<?php
/**
 * Schema class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Database\Driver\Driver;
use Freyja\Database\Schema\Table;
use Freyja\Database\Database;
use Symfony\Component\Yaml\Yaml;
use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\ExceptionInterface;

/**
 * Schema class.
 *
 * @package Freyja\Database\Schema
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class Schema {
  /**
   * Database schema.
   *
   * @todo: put an example.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $schema = array();

  /**
   * Database.
   *
   * @since 1.0.0
   * @access private
   * @var Freyja\Database\Database
   */
  private $database;

  /**
   * Configuration file path.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $filename;

  /**
   * Logger object.
   *
   * @since 1.0.0
   * @access private
   * @var Freyja\Log\LoggerInterface
   */
  private $logger;

  /**
   * Class constructor.
   *
   * The Database argument MUST be connected before passing it to the
   * constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Database $database
   * @param Freyja\Log\LoggerInterface $logger Optional. Logger object.
   * Default null.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $logger isn't a
   * Freyja\Log\LoggerInterface or null.
   */
  public function __construct(Database $database, $logger = null) {
    if (!is_a($logger, 'Freyja\log\LoggerInterface') && !is_null($logger))
      throw InvalidArgumentException::typeMismatch('logger', $logger, 'Freyja\Log\LoggerInterface or null');

    $this->database = $database;
    $this->logger = $logger;

    $this->filename = getcwd().'/db/schema.yml';
    if (file_exists($this->filename)) {
      // Put file content into an array.
      $schema = Yaml::parse(file_get_contents($this->filename));
      // Check if $database exists in the array and put its schema into the
      // object property.
      $this->schema = isset($schema[$database->getName()]) ? $schema[$database->getName()] : array();
      if (!isset($this->schema['tables']))
        $this->schema['tables'] = array();
      // At this point $schema has this structure:
      // `array('tables'=>array())`
      // and the internal array may or may not contain some tables.
      // $schema is the schema of $database, therefore it must be dumped by Yaml
      // in this way: `array($database->getName() => $schema)`.
    }
  }

  /**
   * Create table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Schema\Table $table
   *
   * @throws Freyja\Exceptions\ExceptioinInterface the same exception raised by
   * Freyja\Database\Database::execute() if the query fails.
   */
  public function create(Table $table) {
    if ($this->hasTable($table)) {
      // Table already exists.
      // TODO: log a notice that table already exists. Log in a file too.
    } else {
      // Table doesn't exists, create it.
      try {
        $this->database->execute($table);
      } catch (ExceptionInterface $e) {
        throw $e;
      }
      // Query ok, update schema property.
      $info = $table->getTable();
      $this->schema['tables'] = array_merge($this->schema['tables'], $info);

      // Write new schema in yaml file.
      $this->updateSchema();
      // TODO: log in file that everything is done correctly (?).
    }
  }

  /**
   * Remove table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string|Table $table Table name, or Table object.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $table isn't a string
   * or a Freyja\Database\Schema\Table object.
   * @throws Freyja\Exceptions\ExceptionInterface the same exception raised by
   * Freyja\Database\Database::execute() if the query fails.
   */
  public function remove($table) {
    if (!is_string($table) && !is_a($table, 'Freyja\Database\Schema\Table'))
      throw InvalidArgumentException::typeMismatch('table', $table, 'String or Freyja\Database\Schema\Table');

    if (is_string($table))
      $table = new Table($table);

    if (!$this->hasTable($table)) {
      // TODO: send notice, and log to file, that table doesn't exists.
    } else {
      // Table exists, drop it.
      try {
        $this->database->execute($table->drop());
      } catch (ExceptionInterface $e) {
        throw $e;
      }
      // Query ok, update schema property.
      unset($this->schema['fields'][$table->getName()]);
      // Write new schema in yaml file.
      $this->updateSchema();
      // TODO: log in file that everything is done correctly (?).
    }
  }

  /**
   * Alter table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Schema\Table $table Table to be altered.
   *
   * @throws Freyja\Exceptions\RuntimeException if raised by
   * `Freyja\Database\Schema\Table::getAlteration()`.
   * @throws Freyja\Exceptions\ExceptionInterface the same exception raised by
   * Freyja\Database\Database::execute() if the query fails.
   */
  public function alter(Table $table) {
    if (!$this->hasTable($table)) {
      // TODO: send notice, and log to file, that table doesn't exists.
    } else {
      // Table exists, alter it.
      try {
        $this->database->execute($table);
      } catch (ExceptionInterface $e) {
        throw $e;
      }
      // Query ok, update schema property.
      try {
        $alteration = $table->getAlteration();
      } catch (ExceptionInterface $e) {
        throw $e;
      }
      foreach ($alteration as $type => $fields) {
        switch ($type) {
          case 'ADD':
            $this->schema['tables'][$table->getName()]['fields'] = array_merge(
              $this->schema['tables'][$table->getName()]['fields'],
              $fields
            );
            break;
          case 'DROP COLUMN':
            foreach (array_keys($fields) as $name)
              unset($this->schema['tables'][$table->getName()]['fields'][$name]);
            break;
        }
      }
      // Write new schema in yaml file.
      $this->updateSchema();
      // TODO: log in file that everything is done correctly (?).
    }
  }

  /**
   * Check table existence.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string|Table $table Freyja\Database\Schema\Table object or string.
   * @return boolean Whether the table exists in the schema or not.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $table isn't a string
   * or a Freyja\Database\Schema\Table object.
   */
  public function hasTable($table) {
    if (!is_string($table) && !is_a($table, 'Freyja\Database\Schema\Table'))
      throw InvalidArgumentException::typeMismatch('table', $table, 'String or Freyja\Database\Schema\Table');

    if (!is_string($table))
      $table = $table->getName();
    return isset($this->schema['tables'][$table]);
  }

  /**
   * Update schema.
   *
   * Update object property and yaml configuration file as well.
   *
   * @since 1.0.0
   * @access private
   *
   * @param
   */
  private function updateSchema() {
    // Retrieve current schema.
    $schema = array();
    if (file_exists($this->filename))
      $schema = Yaml::parse(file_get_contents($this->filename));

    // Replace database schema with the updated one.
    // Create it if not already there.
    $schema[$this->database->getName()] = $this->schema;

    // Rewrite yaml file.
    $yaml_string = Yaml::dump($schema);
    file_put_contents($this->filename, $yaml_string);
  }
}
