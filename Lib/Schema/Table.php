<?php
/**
 * Table class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Database\Query\Query;
use Freyja\Database\Query\QueryInterface;
use Freyja\Database\Schema\Field;
use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\LogicException;
use Freyja\Exceptions\ExceptionInterface;

/**
 * Table class.
 *
 * @package Freyja\Database\Schema
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class Table extends Query implements QueryInterface {
  /**
   * Table name.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $name;

  /**
   * Query type.
   *
   * Query type that will be executed on the table.
   * It can be: 'create', 'drop', 'alter'. Default 'create'.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $type = 'create';

  /**
   * To be altered fields.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $alter_fields = array(
    'ADD' => array(),
    'DROP COLUMN' => array()
  );

  /**
   * Table fields.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $fields = array();

  /**
   * Primary keys.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $primary_keys = array();

  /**
   * Primary key name.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $primary_name;

  /**
   * Foreign keys.
   *
   * Array of associative arrays `key => value`, where `key` will be 'field',
   * 'references' and 'on'. 'field' is the foreign key field, 'references' is
   * the name of the table references by the key, and 'on' is the matching field
   * of the table referenced.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $foreign_keys = array();

  /**
   * Table character set.
   *
   * @since 1.0.0
   * @access private
   * @var string;
   */
  private $charset;

  /**
   * Table collation.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $collation;

  /**
   * Table engine.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $engine;

  /**
   * Class constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Table name.
   * @param array $fields Optional. Fields of the table. If the type of the
   * query is 'create', it MUST NOT be an empty array. Default empty array.
   * @param string $charset Optional. Table character set. Default 'utf8'.
   * @param string $collation Optional. Table collation.
   * Default 'utf8_unicode_ci'.
   * @param string $engine Optional. Table engine. Default 'InnoDB'.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $name or $charset or
   * $collation or $engine aren't strings, or if $fields elements aren't
   * Freyja\Database\Schema\Field objects.
   */
  public function __construct($name, array $fields = array(), $charset = 'utf8', $collation = 'utf8_unicode_ci', $engine = 'InnoDB') {
    foreach (array('name', 'charset', 'collation', 'engine') as $arg)
      if (!is_string($$arg))
        throw InvalidArgumentException::typeMismatch($arg, $$arg, 'String');
    foreach ($fields as $field) {
      if (!is_a($field, 'Freyja\Database\Schema\Field'))
        throw InvalidArgumentException::typeMismatch('field', $field, 'Freyja\Database\Schema\Field');
      $this->fields[$field->getName()] = $field;
    }

    $this->name = $name;
    $this->charset = $charset;
    $this->collation = $collation;
    $this->engine = $engine;
  }

  /**
   * Set PRIMARY KEY.
   *
   * Set a primary key for one or more fields.
   * Pass a string for one field, or an array for more. If you pass an array,
   * you MUST pass also the key name.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string|array $field Field name(s).
   * @param string $name Optional. Key name. Default null.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $field, or its
   * elements if it is an array, aren't strings, or if $name isn't a string or
   * null, or if field name(s) doesn't match any field name saved in the
   * object's property.
   * @throws Freyja\Exceptions\LogicException if $field is an array and $name is
   * null.
   */
  public function primaryKey($field, $name = null) {
    if (!is_string($field) && !is_array($field))
      throw InvalidArgumentException::typeMismatch('field name(s)', $field, 'Array or String');
    if (!is_string($name) && !is_null($name))
      throw InvalidArgumentException::typeMismatch('key name', $name, 'String or null');

    if (is_array($field)) {
      if (is_null($name))
        throw new LogicException('Setting more than one field as primary key requires a name to be set for that key');
      foreach ($field as $f) {
        if(!is_string($f))
          throw InvalidArgumentException::typeMismatch('field name', $f, 'String');
        if (!array_key_exists($f, $this->fields))
          throw new InvalidArgumentException('Field name(s) passed to `Table::primaryKey()` must match the fields of the table');
      }
    } else {
      $field = array($field);
    }

    $this->primary_keys = $field;
    $this->primary_name = (is_null($name) && isset($field[0])) ? $field[0] : $name;
    return $this;
  }

  /**
   * Set FOREIGN KEY.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param string $table Table referenced.
   * @param string $referenced_field Field referenced.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if the arguments aren't
   * strings, or if $field doesn't match any field of the table.
   */
  public function foreignKey($field, $table, $referenced_field) {
    foreach (array('field', 'table', 'referenced_field') as $arg)
      if (!is_string($$arg))
        throw InvalidArgumentException::typeMismatch($arg, $$arg, 'String');
    if (!array_key_exists($field, $this->fields))
      throw new InvalidArgumentException('Cannot set a foreign key on a non existing field');

    $this->foreign_keys[$field][] = array(
      'references' => $table,
      'on', $referenced_field
    );
  }

  /**
   * Retrieve table name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Table name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Retrieve table information.
   *
   * Associative array `key => value`, where `key` is the table name, and
   * `value` is an associative array, in which the keys are: 'fields', of which
   * the value is an array containing all the fields as they are returned from
   * `Freyja\Database\Schema\Field::getField()`; 'primary', of which the value
   * is an array containing the primary_key name as `key`, and the array of
   * fields that form the key; 'foreign', of which the value is an array, where
   * the keys are 'field', 'references' and 'on', and the values are the fields
   * and table names that identify the foreign_key; 'charset', 'collation' and
   * 'engine', of which the values identify respectively the character set, the
   * collation and the engine of the table.
   *
   * @since 1.0.0
   * @access public
   *
   * @return array Table information.
   *
   * @throws Freyja\Exceptions\RuntimeException The one possibly raised by
   * Freyja\Database\Schema\Field::getField().
   */
  public function getTable() {
    // Set primary key information.
    $primary = array();
    if (isset($this->primary_name))
      $primary[$this->primary_name] = $this->primary_keys;

    // Set fields information.
    $fields = array();
    foreach ($this->fields as $field) {
      try {
        $fields = array_merge($fields, $field->getField());
      } catch (ExceptionInterface $e) {
        throw $e;
      }
    }

    // Arrange information.
    $info = array(
      'fields' => $fields,
      'primary' => $primary,
      'foreign' => $this->foreign_keys,
      'charset' => $this->charset,
      'collation' => $this->collation,
      'engine' => $this->engine
    );

    // Return complete information array, with the table name.
    return array($this->name => $info);
  }

  /**
   * Retrieve alteration information.
   *
   * @since 1.0.0
   * @access public
   *
   * @return array Associative array `key => value`, where `key` is the type of
   * the alteration (e.g. 'ADD') and `value` is an array of arrays, exactly as
   * they are outputed from `Freyja\Database\Schema\Field::getField()`.
   *
   * @throws Freyja\Exceptions\RuntimeException if raised by the method
   * `Freyja\Database\Schema\Field::getField()`.
   */
  public function getAlteration() {
    $alteration = array();
    foreach ($this->alter_fields as $type => $fields) {
      $single_type_alteration = array();
      foreach ($fields as $field) {
        try {
          $single_type_alteration = array_merge($single_type_alteration, $field->getField());
        } catch (ExceptionInterface $e) {
          throw $e;
        }
      }
      $alteration[$type] = $single_type_alteration;
    }
    return $alteration;
  }

  /**
   * Set query type to 'drop'.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function drop() {
    $this->type = 'drop';
    return $this;
  }

  /**
   * Add field(s) to table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $fields Fields that will be added.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if it is raised by the
   * method `Table::alter()`.
   * @throws Freyja\Exceptions\LogicException if it is raised by the method
   * `Table::alter()`.
   */
  public function addFields(array $fields) {
    try {
      return $this->alter($fields, 'ADD');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Remove field(s) from table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $fields Fields that will be removed.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if it is raised by the
   * method `Table::alter()`.
   * @throws Freyja\Exceptions\LogicException if it is raised by the method
   * `Table::alter()`.
   */
  public function removeFields(array $fields) {
    try {
      return $this->alter($fields, 'DROP COLUMN');
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if it is raised by the methods
   * called.
   * @throws Freyja\Exceptions\LogicException if it is raised by the methods
   * called.
   * @see Freyja\Database\Schema\Table::buildCreate()
   */
  public function build() {
    try {
      return $this->{'build'.ucfirst(strtolower($this->type))}();
    } catch (ExceptionInterface $e) {
      throw $e;
    }
  }

  /**
   * Build single field.
   *
   * This method is used by `Freyja\Database\Schema\Table::buildCreate()` and
   * uses a property initialized in that method, therefore you SHOULD NOT use
   * this method alone. Using this method alone will be the same of casting the
   * Field to a string.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Schema\Field $field
   * @return string
   *
   * @throws Freyja\Exceptions\LogicException if AUTO_INCREMENT is set on more
   * than one field, or in a non primary key field.
   */
  public function buildField(Field $field) {
    if (isset($this->autoinc_field)) {
      if ($field->isAutoIncrement()) {
        if ($this->autoinc_field == true) {
          unset($this->autoinc_field);
          throw new LogicException('AUTO_INCREMENT cannot be set on more than one field');
        }
        if (!in_array($field->getName(), $this->primary_keys)) {
          unset($this->autoinc_field);
          throw new LogicException('AUTO_INCREMENT cannot be set on a field that isn\'t primary key');
        }
        $this->autoinc_field = true;
      }
    }

    return (string) $field;
  }

  /**
   * Alter table.
   *
   * @since 1.0.0
   * @access private
   *
   * @param array $fields Fields to be altered.
   * @param string $type Alter type. Value can be 'add' or 'drop'.
   *
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if some element in
   * $fields isn't a Freyja\Database\Schema\Field object.
   * @throws Freyja\Exceptions\LogicException if $fields is an empty array.
   */
  private function alter(array $fields, $type) {
    if (empty($fields))
      throw new LogicException('It\'s required at least one column to alter the table');
    foreach ($fields as $field)
      if (!is_a($field, 'Freyja\Database\Schema\Field'))
        throw InvalidArgumentException::typeMismatch('field', $field, 'Freyja\Database\Schema\Field');

    $this->type = 'alter';
    $this->alter_fields[$type] = array_merge($this->alter_fields, $fields);
    return $this;
  }

  /**
   * Build 'create' query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if it is raised by the Field
   * method called.
   * @see Freyja\Database\Schema\Field::getField()
   * @throws Freyja\Exceptions\LogicException if AUTO_INCREMENT is set on more
   * than one field, or in a non primary key field, or if $fields property is an
   * empty array.
   */
  private function buildCreate() {
    $query = 'CREATE TABLE IF NOT EXISTS '.$this->name.' (';

    $autoinc_fields = 0;
    if (empty($fields))
      throw new LogicException('A table must have at least 1 column');
    try {
      $this->autoinc_field = false;
      $query .= join(', ', array_map(array($this, 'buildField'), $this->fields));
      unset($this->autoinc_field);
    } catch (ExceptionInterface $e) {
      throw $e;
    }

    // Append the PRIMARY KEY constraint, if set.
    if (!empty($this->primary_keys))
      $query .= sprintf(
        ', CONSTRAINT %1$s PRIMARY KEY (%2$s)',
        $this->primary_name,
        join(',', $this->primary_keys)
      );

    // Append the FOREIGN KEY constraints, if set.
    foreach ($this->foreign_keys as $key) {
      $query .= sprintf(
        ', FOREIGN KEY (%1$s) REFERENCES %2$s(%3$s)',
        $key['field'],
        $key['references'],
        $key['on']
      );
    }

    // Append charset, collation and engine.
    $query .= sprintf(
      ') CHARACTER SET %1$s COLLATION %2$s ENGINE %3$s;',
      $this->charset,
      $this->collation,
      $this->engine
    );

    return $query;
  }

  /**
   * Build 'drop' query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   */
  private function buildDrop() {
    $query = sprintf(
      'DROP TABLE IF EXISTS %s;',
      $this->name
    );

    return $query;
  }

  /**
   * Build 'alter' query string.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if it is raised by the Field
   * method called.
   * @see Freyja\Database\Schema\Field::getField()
   */
  private function buildAlter() {
    $query = sprintf('ALTER TABLE %s ', $this->name);

    try {
      foreach ($this->alter_fields as $type => $fields) {
        $query .= join(', ', array_map(function($type, $field) {
          $part = '';
          $field_info = $field->getField();
          $field_name = $field->getName();
          $part .= sprintf(
            '%1$s %2$s',
            $type,
            $field_name
          );

          // Prepare string based on the type of the alteration.
          // The switch is useless at the moment, but it's here in anticipation of
          // future additions.
          switch ($type) {
            case 'ADD':
              $part .= ' '.$field_info[$field_name]['type'];
              if (!is_null($field_info[$field_name]['default']))
                $part .= ' DEFAULT '.$field_info[$field_name]['default'];
              if ($field_info[$field_name]['NOT NULL'])
                $part .= ' NOT NULL';
              break;
          }
          return $part;
        }, array_fill(0, count($fields), $type), array_values($fields)));
      }
    } catch (ExceptionInterface $e) {
      throw $e;
    }

    $query .= ';';
    return $query;
  }
}
