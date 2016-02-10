<?php
/**
 * Field class file.
 *
 * @package Freyja\Database\Schema
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Schema;

use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\RuntimeException;
use Freyja\Exceptions\LogicException;
use Freyja\Log\LoggerInterface;

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
   * Numeric types.
   *
   * Numeric types, the only ones that can be UNSIGNED and AUTO_INCREMENT.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $numeric_types = array(
    self::INT,
    self::TINYINT,
    self::SMALLINT,
    self::MEDIUMINT,
    self::BIGINT,
    self::FLOAT,
    self::DOUBLE,
    self::DECIMAL
  );

  /**
   * Logger object.
   *
   * @since 1.0.0
   * @access private
   * @var Freyja\Log\LoggerInterface
   */
  private $logger;

  /**
   * Data type constants.
   *
   * @since 1.0.0
   * @access public
   * @var string
   */
  // Numeric types.
  const INT = 'INT';
  const TINYINT = 'TINYINT';
  const SMALLINT = 'SMALLINT';
  const MEDIUMINT = 'MEDIUMINT';
  const BIGINT = 'BIGINT';
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
  const TINYTEXT = 'TINYTEXT';
  const MEDIUMTEXT = 'MEDIUMTEXT';
  const LONGTEXT = 'LONGTEXT';

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
      throw InvalidArgumentException::typeMismatch('name', $name, 'String');

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
    $this->setIntegerType(self::TINYINT, $length, 4);
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
    $this->setIntegerType(self::SMALLINT, $length, 5);
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
    $this->setIntegerType(self::MEDIUMINT, $length, 9);
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
    $this->setIntegerType(self::BIGINT, $length, 20);
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
    $this->type = self::TINYTEXT;
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
    $this->type = self::MEDIUMTEXT;
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
    $this->type = self::LONGTEXT;
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
   * @throws Freyja\Exceptions\LogicException if the field is set to NOT NULL.
   * @throws Freyja\Exceptions\InvalidArgumentException if $value isn't a
   * scalar.
   */
  public function default($value = self::NULL) {
    if ($value == self::NULL && $this->nullable == false)
      throw new LogicException('Cannot set default value to NULL if the field is NOT NULL');

    if (!is_scalar($value))
      throw InvalidArgumentException::typeMismatch('default value', $value, 'Scalar');

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
   * @throws Freyja\Exceptions\LogicException if default value is set to NULL.
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
   * @throws Freyja\Exceptions\LogicException if the field type is not allowed
   * to be UNSIGNED.
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
   * @throws Freyja\Exceptions\LogicException if the field type is not allowed
   * to be AUTO_INCREMENT.
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
   * Return an associative array `key => value`, where `key` is the field name
   * and `value` is an associative array where keys are: 'type', 'default',
   * 'NOT NULL', 'UNSIGNED', 'AUTO_INCREMENT'.
   * Keys 'type' and 'default' will have the exact value that can be inserted in
   * the query, except for `'default' => null`, which means that the default
   * value isn't set for this field.
   * The other keys of the internal array will have a boolean value, and if it
   * is true the key can be inserted in the query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return array Field information.
   *
   * @throws Freyja\Exceptions\RuntimeException if field type isn't set.
   */
  public function getField() {
    $info = array();

    // Attach $length and $decimals to the type if necessary.
    try {
      $type = $this->getTypeString();
    } catch (ExceptionInterface $e) {
      throw $e;
    }

    // Push the type of the field in the array.
    $info['type'] = $type;

    // Push the default value in the array.
    $info['default'] = $this->default;

    // Push NOT NULL in the array.
    $info['NOT NULL'] = !$this->nullable;

    // Push UNSIGNED in the array.
    $field['UNSIGNED'] = $this->unsigned;

    // Push AUTO_INCREMENT in the array.
    $field['AUTO_INCREMENT'] = $this->auto_increment;

    // Push the name and the info of the field in the array.
    $field = array($this->name => $info);

    return $field;
  }

  /**
   * Convert Field object to string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if field type isn't set.
   */
  public function __toString() {
    try {
      $type = $this->getTypeString();
    } catch (ExceptionInterface $e) {
      throw $e;
    }

    return sprintf(
      '%1$s %2$s%3$s%4$s%5$s%6$s',
      $this->name,
      $type,
      !is_null($this->default) ? ' DEFAULT '.$this->default : '',
      !$this->nullable ? ' NOT NULL' : '',
      $this->unsigned ? ' UNSIGNED' : '',
      $this->auto_increment ? ' AUTO_INCREMENT' : ''
    );
  }

  /**
   * Whether the field is auto_increment or not.
   *
   * @since 1.0.0
   * @access public
   *
   * @return boolean
   */
  public function isAutoIncrement() {
    return $this->auto_increment;
  }

  /**
   * Attach length and decimals to field type if necessary.
   *
   * @since 1.0.0
   * @access private
   *
   * @return string
   *
   * @throws Freyja\Exceptions\RuntimeException if field type isn't set.
   */
  private function getTypeString() {
    if (!isset($this->type))
      throw new RuntimeException('Field type isn\'t set');

    $type = $this->type;
    switch ($this->type) {
      case self::INT:
      case self::TINYINT:
      case self::SMALLINT:
      case self::MEDIUMINT:
      case self::BIGINT:
      case self::CHAR:
      case self::VARCHAR:
        $type .= '('.$this->length.')';
        break;
      case self::FLOAT:
      case self::DOUBLE:
      case self::DECIMAL:
        $type .= sprintf(
          '(%1$s,%2$s)',
          $this->length,
          $this->decimals
        );
        break;
    }
    return $type;
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
    $this->length = self::getLength($length, $max_length, 1, $max_length);
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
    $this->decimals = self::getLength($decimals, $max_decimals, 0, $default_decimals);
    $this->length = self::getLength($length, $max_length, max(1, $this->decimals), max($default_length, $this->decimals+1));
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
    $this->length = self::getLength($length, 255, 1, $default_length);
  }

  /**
   * Decide which length (or decimals) to set.
   *
   * @since 1.0.0
   * @access private
   * @static
   *
   * @param int $length Field length (or decimals).
   * @param int $max Max length accepted (or decimals).
   * @param int $min Min length accepted (or decimals).
   * @param int $default Default length (or decimals).
   * @return int Length (or decimals) chosen.
   */
  private static function getLength($length, $max, $min, $default) {
    if (!is_numeric($length) || $length < $min || $length > $max) {
      return (int) $default;
      // TODO: log in file.
    }
    return (int) $length;
  }
}
