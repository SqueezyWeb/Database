<?php
/**
 * Schema class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Database\Driver;
use Freyja\Database\Table;
use Freyja\Database\Database;
use Symfony\Component\Yaml\Yaml;
use Freyja\Exceptions\InvalidArgumentException as InvArgExcp;

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
   * Class constructor.
   *
   * The Database argument MUST be connected before passing it to the
   * constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Database $database
   */
  public function __construct(Database $database) {
    $this->database = $database;

    $this->filename = getcwd().'/db/Schema.yml';
    if (file_exists($this->filename)) {
      $schema = Yaml::parse(file_get_contents($this->filename));
      $db_name = $schema[$database->getName()];
      if (isset($db_name))
        $this->schema = $db_name;
    }
  }

  /**
   * Create table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Schema\Table $table
   */
  public function create(Table $table) {
    $db_name = $this->database->getName();
    if (!empty($this->schema) && isset($this->schema[$db_name]['tables'][$table->getName()])) {
      // Table already exists.
      // TODO: log a notice that table already exists. Log in a file too.
    } else {
      // Table doesn't exists, create it.
      $this->database->execute($table);
      if ($table->getResult()) {
        // Query ok, update schema property.
        $info = $table->getTable();
        if (empty($this->schema))
          $this->schema[$db_name] = array('tables' => $info);
        else
          $this->schema[$db_name]['tables'] = array_merge(
            $this->schema[$db_name]['tables'],
            $info
          );

        // Write new schema in yaml file.
        $this->updateSchema();
        // TODO: log in file that everything is done correctly (?).
      } else {
        // Query result false.
        // TODO: send notice and log in file that query had an error.
      }
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
   */
  public function remove($table) {
    if (!is_string($table) && !is_a($table, 'Freyja\Database\Schema\Table'))
      throw InvArgExcp::typeMismatch('table', $table, 'String or Freyja\Database\Schema\Table');

    if (is_string($table))
      $table = new Table($table);

    $db_name = $this->database->getName();
    if (empty($this->schema) || !isset($this->schema[$db_name]['tables'][$table->getName()])) {
      // TODO: send notice, and log to file, that table doesn't exists.
    } else {
      $this->database->execute($table->drop());
      if ($table->getResult()) {
        // Query ok, update schema property.
        unset($this->schema[$db_name]['fields'][$table->getName()]);
        // Write new schema in yaml file.
        $this->updateSchema();
        // TODO: log in file that everything is done correctly (?).
      } else {
        // Query result false.
        // TODO: send notice and log in file that query had an error.
      }
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
   * @throws \RuntimeException if raised by
   * `Freyja\Database\Schema\Table::getAlteration()`.
   */
  public function alter(Table $table) {
    $db_name = $this->database->getName();
    if (empty($this->schema) || !isset($this->schema[$db_name]['tables'][$table->getName()])) {
      // TODO: send notice, and log to file, that table doesn't exists.
    } else {
      $this->database->execute($table);
      if ($table->getResult()) {
        // Query ok, update schema property.
        try {
          $alteration = $table->getAlteration();
        } catch (Exception $e) {
          throw $e;
        }
        foreach ($alteration as $type => $fields) {
          switch ($type) {
            case 'ADD':
              $this->schema[$db_name]['tables'][$table->getName()]['fields'] = array_merge(
                $this->schema[$db_name]['tables'][$table->getName()]['fields'],
                $fields
              );
              break;
            case 'DROP COLUMN':
              foreach ($fields as $name => $info)
                unset($this->schema[$db_name]['tables'][$table->getName()]['fields'][$name]);
              break;
          }
        }
        // Write new schema in yaml file.
        $this->updateSchema();
        // TODO: log in file that everything is done correctly (?).
      } else {
        // Query result false.
        // TODO: send notice and log in file that query had an error.
      }
    }
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
    $db_name = $this->database->getName();
    $schema[$db_name] = $this->schema[$db_name];

    // Rewrite yaml file.
    $yaml_string = Yaml::dump($schema);
    file_put_contents($this->filename, $yaml_string);
  }
}
