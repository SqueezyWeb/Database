<?php
/**
 * Table class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Database\Query;
use Freyja\Database\QueryInterface;
use Freyja\Database\Schema\Field;
use Freyja\Exceptions\InvalidArgumentException as InvArgExcp;
use \LogicException;

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
   * @param array $fields Fields of the table.
   * @param string $charset Optional. Table character set. Default 'utf8'.
   * @param string $collation Optional. Table collation.
   * Default 'utf8_unicode_ci'.
   * @param string $engine Optional. Table engine. Default 'InnoDB'.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $name or $charset or
   * $collation or $engine aren't strings, or if $fields elements aren't
   * Freyja\Database\Schema\Field objects.
   * @throws \LogicException if $fields is empty.
   */
  public function __construct($name, array $fields, $charset = 'utf8', $collation = 'utf8_unicode_ci', $engine = 'InnoDB') {
    foreach (array('name', 'charset', 'collation', 'engine') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');
    if (empty($fields))
      throw new LogicException('A table must have at least 1 column');
    foreach ($fields as $field) {
      if (!is_a($field, 'Freyja\Database\Schema\Field'))
        throw InvArgExcp::typeMismatch('field', $field, 'Freyja\Database\Schema\Field');
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
   * @throws \LogicException if $field is an array and $name is null.
   */
  public function primaryKey($field, $name = null) {
    if (!is_string($field) && !is_array($field))
      throw InvArgExcp::typeMismatch('field name(s)', $field, 'Array or String');
    if (!is_string($name) && !is_null($name))
      throw InvArgExcp::typeMismatch('key name', $name, 'String or null');

    if (is_array($field)) {
      if (is_null($name))
        throw new LogicException('Setting more than one field as primary key requires a name to be set for that key');
      foreach ($field as $f) {
        if(!is_string($f))
          throw InvArgExcp::typeMismatch('field name', $f, 'String');
        if (!array_key_exists($f, $this->fields))
          throw new InvArgExcp('Field name(s) passed to `Table::primaryKey()` must match the fields of the table');
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
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');
    if (!array_key_exists($field, $this->fields))
      throw new InvalidArgumentException('Cannot set a foreign key on a non existing field');

    // TODO: Check in the schema if the table $table and the column $referenced_field exist.

    $this->foreign_keys[] = array(
      'field' => $field,
      'references' => $table,
      'on', $referenced_field
    );
  }

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   *
   * @throws \RuntimeException if it is raised by the Field method called.
   * @see Field::getField()
   * @throws \LogicException if AUTO_INCREMENT is set on more than one field, or
   * in a non primary key field.
   */
  public function build() {
    $query = 'CREATE TABLE IF NOT EXISTS '.$this->name.' (';

    $count = 0;
    $autoinc_fields = 0;
    foreach ($this->fields as $field) {
      if ($count != 0)
        $query .= ', ';

      try {
        $info = $field->getField();
      } catch (Exception $e) {
        throw $e;
      }

      // Append field name and type.
      $query .= $info['name'].' '.$info['type'];

      // Append length and decimals, if set.
      if (isset($info['length']))
        $query .= sprintf(
          '(%1$s%2$s)',
          $length,
          isset($info['decimals']) ? ','.$info['decimals'] : ''
        );

      // Append default value, if set.
      if (isset($info['default']))
        $query .= ' DEFAULT '.$info['default'];

      // Append NOT NULL, if set.
      if (isset($info['not_null']))
        $query .= ' '.$info['not_null'];

      // Append UNSIGNED, if set.
      if (isset($info['unsigned']))
        $query .= ' '.$info['unsigned'];

      // Append AUTO_INCREMENT, if set.
      if (isset($info['auto_increment'])) {
        if ($autoinc_fields > 0)
          throw new LogicException('AUTO_INCREMENT cannot be set on more than one field');
        if (!in_array($info['name'], $this->primary_keys))
          throw new LogicException('AUTO_INCREMENT cannot be set on a field that isn\'t primary key');

        $query .= ' '.$info['auto_increment'];
      }
    }

    // Append the PRIMARY KEY constraint, if set.
    if (!empty($this->primary_keys)) {
      $key_fields = '';
      $count = 0;
      foreach ($this->primary_keys as $key) {
        if ($count != 0)
          $key_fields .= ',';
        $key_fields .= $key;
      }
      $query .= sprintf(
        ', CONSTRAINT %1$s PRIMARY KEY (%2$s)',
        $this->primary_name,
        $key_fields
      );
    }

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
  }
}
