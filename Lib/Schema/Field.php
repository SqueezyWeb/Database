<?php
/**
 * Field class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Exceptions\InvalidArgumentException as InvArgExcp;
use \RuntimeException;
use \LogicException;

/**
 * Field class.
 *
 * @package Freyja\Database\Schema
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class Field {
  /**
   * Field name.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $name;

  /**
   * Data type.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $type;

  /**
   * Data length.
   *
   * @since 1.0.0
   * @access private
   * @var int
   */
  private $length;

  /**
   * Number of decimals.
   *
   * @since 1.0.0
   * @access private
   * @var int
   */
  private $decimals;

  /**
   * Default value.
   *
   * @since 1.0.0
   * @access private
   * @var mixed
   */
  private $default;

  /**
   * Nullable field.
   *
   * Whether the field is nullable or not. Default true;
   *
   * @since 1.0.0
   * @access private
   * @var boolean
   */
  private $nullable = true;

  /**
   * Unsigned data.
   *
   * Whether the field is UNSIGNED or not. Only for numeric data types.
   * Default false.
   *
   * @since 1.0.0
   * @access private
   * @var boolean
   */
  private $unsigned = false;

  /**
   * Auto increment.
   *
   * Whether the field is AUTO_INCREMENT or not. Default false.
   *
   * @since 1.0.0
   * @access private
   * @var boolean
   */
  private $auto_increment = false;

  /**
   * Types that can be UNSIGNED and AUTO_INCREMENT.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $allowed = array(
    self::INT,
    self::TINY_INT,
    self::SMALL_INT,
    self::MEDIUM_INT,
    self::BIG_INT,
    self::FLOAT,
    self::DOUBLE,
    self::DECIMAL
  );

  /**
   * Data type constants.
   *
   * @since 1.0.0
   * @access public
   * @var string
   */
  // Numeric types.
  const INT = 'INT';
  const TINY_INT = 'TINYINT';
  const SMALL_INT = 'SMALLINT';
  const MEDIUM_INT = 'MEDIUMINT';
  const BIG_INT = 'BIGINT';
  const FLOAT = 'FLOAT';
  const DOUBLE = 'DOUBLE';
  const DECIMAL = 'DECIMAL';
  // Date and time types.
  const DATE = 'DATE';
  const DATETIME = 'DATETIME';
  const TIMESTAMP = 'TIMESTAMP';
  const TIME = 'TIME';
  // String types.
  const CHAR = 'CHAR';
  const VARCHAR = 'VARCHAR';
  const TEXT = 'TEXT';
  const TINY_TEXT = 'TINYTEXT';
  const MEDIUM_TEXT = 'MEDIUMTEXT';
  const LONG_TEXT = 'LONGTEXT';

  /**
   * Null value constant.
   *
   * @since 1.0.0
   * @access public
   * @var string
   */
  const NULL = 'NULL';

  /**
   * Class constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Field name.
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if $name isn't a string.
   */
  public function __construct($name) {
    if (!is_string($name))
      throw InvArgExcp::typeMismatch('field name', $name, 'String');

    $this->name = $name;
  }

  /**
   * Set data type to INT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 11.
   * @return self
   */
  public function integer($length = 11) {
    $this->setIntegerType(self::INT, $length, 11);
    return $this;
  }

  /**
   * Set data type to TINYINT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Filed length. Default 4.
   * @return self
   */
  public function tinyInteger($length = 4) {
    $this->setIntegerType(self::TINY_INT, $length, 4);
    return $this;
  }

  /**
   * Set data type to SMALLINT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 5.
   * @return self
   */
  public function smallInteger($length = 5) {
    $this->setIntegerType(self::SMALL_INT, $length, 5);
    return $this;
  }

  /**
   * Set data type to MEDIUMINT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 9.
   * @return self
   */
  public function mediumInteger($length = 9) {
    $this->setIntegerType(self::MEDIUM_INT, $length, 9);
    return $this;
  }

  /**
   * Set data type to BIGINT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 20.
   * @return self
   */
  public function bigInteger($length = 20) {
    $this->setIntegerType(self::BIG_INT, $length, 20);
    return $this;
  }

  /**
   * Set data type to FLOAT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 10.
   * @param int $decimals Optional. Number of decimals. Default 2.
   * @return self
   */
  public function float($length = 10, $decimals = 2) {
    $this->setRealType(self::FLOAT, $length, 10, 255, $decimals, 2, 30);
    return $this;
  }

  /**
   * Set data type to DOUBLE.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 16.
   * @param int $decimals. Optional. Number of decimals. Default 4.
   * @return self
   */
  public function double($length = 16, $decimals = 4) {
    $this->setRealType(self::DOUBLE, $length, 16, 255, $decimals, 4, 30);
    return $this;
  }

  /**
   * Set data type to DECIMAL.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 10.
   * @param int $decimals Optional. Number of decimals. Default 0.
   * @return self
   */
  public function decimal($length = 10, $decimals = 0) {
    $this->setRealType(self::DECIMAL, $length, 10, 65, $decimals, 0, 30);
    return $this;
  }

  /**
   * Set data type to DATE.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function date() {
    $this->type = self::DATE;
    return $this;
  }

  /**
   * Set data type to DATETIME.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function dateTime() {
    $this->type = self::DATETIME;
    return $this;
  }

  /**
   * Set data type to TIMESTAMP.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function timestamp() {
    $this->type = self::TIMESTAMP;
    return $this;
  }

  /**
   * Set data type to TIME.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function time() {
    $this->type = self::TIME;
    return $this;
  }

  /**
   * Set data type to CHAR.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 1.
   * @return self
   */
  public function char($length = 1) {
    $this->setStringType(self::CHAR, $length, 1);
    return $this;
  }

  /**
   * Set data type to VARCHAR.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $length Optional. Field length. Default 255.
   * @return self
   */
  public function varchar($length = 255) {
    $this->setStringType(self::VARCHAR, $length, 255);
    return $this;
  }

  /**
   * Set data type to TEXT.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function text() {
    $this->type = self::TEXT;
    return $this;
  }

  /**
   * Set data type to TINYTEXT.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function tinyText() {
    $this->type = self::TINY_TEXT;
    return $this;
  }

  /**
   * Set data type to MEDIUMTEXT.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function mediumText() {
    $this->type = self::MEDIUM_TEXT;
    return $this;
  }

  /**
   * Set data type to LONGTEXT.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function longText() {
    $this->type = self::LONG_TEXT;
    return $this;
  }

  /**
   * Set default value.
   *
   * Set default value for this field. If you want to set NULL as default value
   * pass `Field::NULL` to this method.
   *
   * @since 1.0.0
   * @access public
   *
   * @param mixed $value Optional. Default value for the field.
   * Default `self::NULL`.
   * @return self
   *
   * @throws \LogicException if the field is set to NOT NULL.
   * @throws Freyja\Exceptions\InvalidArgumentException if $value isn't a
   * scalar.
   */
  public function default($value = self::NULL) {
    if ($value == self::NULL && $this->nullable == false)
      throw new LogicException('Cannot set default value to NULL if the field is NOT NULL');

    if (!is_scalar($value))
      throw InvArgExcp::typeMismatch('default value', $value, 'Scalar');

    if (is_string($value) && $value != self::NULL)
      $this->default = "'".$value."'";
    elseif (is_bool($value))
      $this->default = ($value == true) ? 'TRUE' : 'FALSE';
    else
      $this->default = $value;

    return $this;
  }

  /**
   * Set the field NOT NULL.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   *
   * @throws \LogicException if default value is set to NULL.
   */
  public function notNull() {
    if ($this->default == self::NULL)
      throw new LogicException('Cannot declare the field as NOT NULL if default value is set to NULL');

    $this->nullable = false;
    return $this;
  }

  /**
   * Set the field UNSIGNED.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   *
   * @throws \LogicException if the field type is not allowed to be UNSIGNED.
   */
  public function unsigned() {
    if (!isset($this->type) || !in_array($this->type, $this->allowed))
      throw new LogicException('The field type cannot be declared UNSIGNED');

    $this->unsigned = true;
    return $this;
  }

  /**
   * Set the field AUTO_INCREMENT.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   *
   * @throws \LogicException if the field type is not allowed to be
   * AUTO_INCREMENT.
   */
  public function autoIncrement() {
    if (!isset($this->type) || !in_array($this->type, $this->allowed))
      throw new LogicException('The field type cannot be declared AUTO_INCREMENT');

    $this->auto_increment = true;
    return $this;
  }

  /**
   * Retrieve field name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Field name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Retrieve field information.
   *
   * Return an associative array where `key`s will be: 'name', 'type', 'length'
   * (only if set), 'decimals' (only if set), 'default' (only if set),
   * 'not_null', 'unsigned', 'auto_increment'.
   * All `value`s will be the exact value that can be inserted in the query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return array Field information.
   *
   * @throws \RuntimeException if field type isn't set.
   */
  public function getField() {
    $field = array();

    if (!isset($this->type))
      throw new RuntimeException('Field type isn\'t set');

    // Push the name and the type of the field in the array.
    $field['name'] = $this->name;
    $field['type'] = $this->type;

    // Push $length and $decimals in the array if necessary.
    switch ($this->type) {
      case self::INT:
      case self::TINY_INT:
      case self::SMALL_INT:
      case self::MEDIUM_INT:
      case self::BIG_INT:
      case self::CHAR:
      case self::VARCHAR:
        $field['length'] = $this->length;
        break;
      case self::FLOAT:
      case self::DOUBLE:
      case self::DECIMAL:
        $field['length'] = $this->length;
        $field['decimals'] = $this->decimals;
        break;
    }

    // Push the default value (if set) in the array.
    if (isset($this->default))
      $field['default'] = $this->default;

    // Push NOT NULL (if the field isn't nullable) in the array.
    if (!$this->nullable)
      $field['not_null'] = 'NOT NULL';

    // Push UNSIGNED in the array if the field is set to UNSIGNED.
    if ($this->unsigned)
      $field['unsigned'] = 'UNSIGNED';

    // Push AUTO_INCREMENT in the array if the field is set to AUTO_INCREMENT.
    if ($this->auto_increment)
      $field['auto_increment'] = 'AUTO_INCREMENT';

    return $field;
  }

  /**
   * Set data type to a numeric one.
   *
   * @since 1.0.0
   * @access private
   *
   * @param string $type One of the numeric constant integer values.
   * @param int $length Field length.
   * @param int $max_length Max length accepted.
   */
  private function setIntegerType($type, $length, $max_length) {
    $this->type = $type;
    if (!is_numeric($length) || $length > $max_length || $length < 1) {
      $length = $max_length;
      // TODO: log in file.
    }

    $this->length = (int) $length;
  }

  /**
   * Set data type to a real number.
   *
   * @since 1.0.0
   * @access private
   *
   * @param string $type One of the numeric constant real values.
   * @param int $length Field length.
   * @param int $default_length The default length for $type.
   * @param int $max_length Max length accepted.
   * @param int $decimals Number of decimals.
   * @param int $default_decimals The default number of decimals for $type.
   * @param int $max_decimals Max number of decimals accepted.
   */
  private function setRealType($type, $length, $default_length, $max_length, $decimals, $default_decimals, $max_decimals) {
    $this->type = $type;
    if (!is_numeric($length) || $length < 1 || $length > $max_length) {
      $length = $default_length;
      // TODO: log in file.
    }

    $this->length = (int) $length;

    if (!is_numeric($decimals) || $decimals < 0 || $decimals > $max_decimals || $decimals > $this->length) {
      $decimals = $default_decimals;
      // TODO: log in file.
    }

    $this->decimals = (int) $decimals;
  }

  /**
   * Set data type to a string.
   *
   * @since 1.0.0
   * @access private
   *
   * @param string $type One of the string constant values.
   * @param int $length Field length.
   * @param int $default_length The default length for $type.
   */
  private function setStringType($type, $length, $default_length) {
    $this->type = $type;
    if (!is_numeric($length) || $length < 1 || $length > 255)
      $length = $default_length;

    $this->length = (int) $length;
  }
}
