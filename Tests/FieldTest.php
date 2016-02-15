<?php
/**
 * FieldTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests;

use Freyja\Database\Schema\Field;
use \ReflectionProperty;

/**
 * FieldTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class FieldTest extends \PHPUnit_Framework_Testcase {
  /**
   * Test for `Field::__construct()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testConstruct() {
    // Set accessibility to object property.
    $reflection_name = new ReflectionProperty('Freyja\Database\Schema\Field', 'name');
    $reflection_name->setAccessible(true);

    $field = new Field('name');
    $name = $reflection_name->getValue($field);

    $this->assertEquals(
      'name',
      $name,
      'Failed asserting that Field::__construct() correctly set the field name.'
    );
  }

  /**
   * Test for `Field::__construct()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument name. String expected, array given instead.
   */
  public function testConstructException() {
    $field = new Field(array());
  }

  /**
   * Test for methods: `Field::integer()`, `Field::tinyInteger()`,
   * `Field::smallInteger()`, `Field::mediumInteger()`, `Field::bigInteger()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::integer
   * @requires function Freyja\Database\Schema\Field::tinyInteger
   * @requires function Freyja\Database\Schema\Field::smallInteger
   * @requires function Freyja\Database\Schema\Field::mediumInteger
   * @requires function Freyja\Database\Schema\Field::bigInteger
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testIntegerMethods() {
    // Set accessibility to object properties.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Field', 'type');
    $reflection_length = new ReflectionProperty('Freyja\Database\Schema\Field', 'length');
    $reflection_type->setAccessible(true);
    $reflection_length->setAccessible(true);

    $field = new Field('name');
    $field->integer();
    $integer_retrieved_type = $reflection_type->getValue($field);
    $integer_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'INT',
      $integer_retrieved_type,
      'Failed asserting that Field::integer() correctly set the field type.'
    );
    $this->assertEquals(
      11,
      $integer_retrieved_length,
      'Failed asserting that Field::integer() correctly set the field length.'
    );

    $field = new Field('name');
    $field->tinyInteger(4);
    $tiny_retrieved_type = $reflection_type->getValue($field);
    $tiny_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'TINYINT',
      $tiny_retrieved_type,
      'Failed asserting that Field::tinyInteger() correctly set the field type.'
    );
    $this->assertEquals(
      4,
      $tiny_retrieved_length,
      'Failed asserting that Field::tinyInteger() correctly set the field length.'
    );

    $field = new Field('name');
    $field->smallInteger('4');
    $small_retrieved_type = $reflection_type->getValue($field);
    $small_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'SMALLINT',
      $small_retrieved_type,
      'Failed asserting that Field::smallInteger() correctly set the field type.'
    );
    $this->assertEquals(
      4,
      $small_retrieved_length,
      'Failed asserting that Field::smallInteger() correctly set the field length.'
    );

    $field = new Field('name');
    $field->mediumInteger(11);
    $medium_retrieved_type = $reflection_type->getValue($field);
    $medium_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'MEDIUMINT',
      $medium_retrieved_type,
      'Failed asserting that Field::mediumInteger() correctly set the field type.'
    );
    $this->assertEquals(
      9,
      $medium_retrieved_length,
      'Failed asserting that Field::mediumInteger() correctly set the field length.'
    );

    $field = new Field('name');
    $field->bigInteger(18);
    $big_retrieved_type = $reflection_type->getValue($field);
    $big_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'BIGINT',
      $big_retrieved_type,
      'Failed asserting that Field::bigInteger() correctly set the field type.'
    );
    $this->assertEquals(
      18,
      $big_retrieved_length,
      'Failed asserting that Field::bigInteger() correctly set the field length.'
    );
  }

  /**
   * Test for methods: `Field::float()`, `Field::double()`, `Field::decimal()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::float
   * @requires function Freyja\Database\Schema\Field::double
   * @requires function Freyja\Database\Schema\Field::decimal
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testRealMethods() {
    // Set accessibility to object properties.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Field', 'type');
    $reflection_length = new ReflectionProperty('Freyja\Database\Schema\Field', 'length');
    $reflection_decimals = new ReflectionProperty('Freyja\Database\Schema\Field', 'decimals');
    $reflection_type->setAccessible(true);
    $reflection_length->setAccessible(true);
    $reflection_decimals->setAccessible(true);

    $field = new Field('name');
    $field->float();
    $float_retrieved_type = $reflection_type->getValue($field);
    $float_retrieved_length = $reflection_length->getValue($field);
    $float_retrieved_decimals = $reflection_decimals->getValue($field);
    $this->assertEquals(
      'FLOAT',
      $float_retrieved_type,
      'Failed asserting that Field::float() correctly set the field type.'
    );
    $this->assertEquals(
      10,
      $float_retrieved_length,
      'Failed asserting that Field::float() correctly set the field length.'
    );
    $this->assertEquals(
      2,
      $float_retrieved_decimals,
      'Failed asserting that Field::float() correctly set the field decimals.'
    );

    $field = new Field('name');
    $field->double('10', 7);
    $double_retrieved_type = $reflection_type->getValue($field);
    $double_retrieved_length = $reflection_length->getValue($field);
    $double_retrieved_decimals = $reflection_decimals->getValue($field);
    $this->assertEquals(
      'DOUBLE',
      $double_retrieved_type,
      'Failed asserting that Field::double() correctly set the field type.'
    );
    $this->assertEquals(
      10,
      $double_retrieved_length,
      'Failed asserting that Field::double() correctly set the field length.'
    );
    $this->assertEquals(
      7,
      $double_retrieved_decimals,
      'Failed asserting that Field::double() correctly set the field decimals.'
    );

    $field = new Field('name');
    $field->decimal(60, 35);
    $decimal_retrieved_type = $reflection_type->getValue($field);
    $decimal_retrieved_length = $reflection_length->getValue($field);
    $decimal_retrieved_decimals = $reflection_decimals->getValue($field);
    $this->assertEquals(
      'DECIMAL',
      $decimal_retrieved_type,
      'Failed asserting that Field::decimal() correctly set the field type.'
    );
    $this->assertEquals(
      60,
      $decimal_retrieved_length,
      'Failed asserting that Field::decimal() correctly set the field length.'
    );
    $this->assertEquals(
      0,
      $decimal_retrieved_decimals,
      'Failed asserting that Field::decimal() correctly set the field decimals.'
    );
  }

  /**
   * Test for methods: `Field::date()`, `Field::dateTime()`,
   * `Field::timestamp()`, `Field::time()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::date
   * @requires function Freyja\Database\Schema\Field::dateTime
   * @requires function Freyja\Database\Schema\Field::timestamp
   * @requires function Freyja\Database\Schema\Field::time
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testTimeMethods() {
    // Set accessibility to object property.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Field', 'type');
    $reflection_type->setAccessible(true);

    $field = new Field('name');
    $field->date();
    $date_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'DATE',
      $date_retrieved_type,
      'Failed asserting that Field::date() correctly set the field type.'
    );

    $field = new Field('name');
    $field->dateTime();
    $datetime_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'DATETIME',
      $datetime_retrieved_type,
      'Failed asserting that Field::dateTime() correctly set the field type.'
    );

    $field = new Field('name');
    $field->timestamp();
    $timestamp_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'TIMESTAMP',
      $timestamp_retrieved_type,
      'Failed asserting that Field::timestamp() correctly set the field type.'
    );

    $field = new Field('name');
    $field->time();
    $time_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'TIME',
      $time_retrieved_type,
      'Failed asserting that Field::time() correctly set the field type.'
    );
  }

  /**
   * Test for methods: `Fields::char()`, `Field::varchar()`, `Field::text()`,
   * `Field::tinyText()`, `Field::mediumText()`, `Field::longText()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::char
   * @requires function Freyja\Database\Schema\Field::varchar
   * @requires function Freyja\Database\Schema\Field::text
   * @requires function Freyja\Database\Schema\Field::tinyText
   * @requires function Freyja\Database\Schema\Field::mediumText
   * @requires function Freyja\Database\Schema\Field::longText
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testStringMethods() {
    // Set accessibility to object properities.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Field', 'type');
    $reflection_length = new ReflectionProperty('Freyja\Database\Schema\Field', 'length');
    $reflection_type->setAccessible(true);
    $reflection_length->setAccessible(true);

    $field = new Field('name');
    $field->char();
    $char_retrieved_type = $reflection_type->getValue($field);
    $char_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'CHAR',
      $char_retrieved_type,
      'Failed asserting that Field::char() correctly set field type.'
    );
    $this->assertEquals(
      1,
      $char_retrieved_length,
      'Failed asserting that Field::char() correctly set field length.'
    );

    $field = new Field('name');
    $field->varchar(254);
    $varchar_retrieved_type = $reflection_type->getValue($field);
    $varchar_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      'VARCHAR',
      $varchar_retrieved_type,
      'Failed asserting that Field::varchar() correctly set field type.'
    );
    $this->assertEquals(
      254,
      $varchar_retrieved_length,
      'Failed asserting that Field::varchar() correctly set field length.'
    );

    $field = new Field('name');
    $field->text();
    $text_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'TEXT',
      $text_retrieved_type,
      'Failed asserting that Field::text() correctly set field type.'
    );

    $field = new Field('name');
    $field->tinyText();
    $tinytext_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'TINYTEXT',
      $tinytext_retrieved_type,
      'Failed asserting that Field::tinyText() correctly set field type.'
    );

    $field = new Field('name');
    $field->mediumText();
    $mediumtext_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'MEDIUMTEXT',
      $mediumtext_retrieved_type,
      'Failed asserting that Field::mediumText() correctly set field type.'
    );

    $field = new Field('name');
    $field->longText();
    $longtext_retrieved_type = $reflection_type->getValue($field);
    $this->assertEquals(
      'LONGTEXT',
      $longtext_retrieved_type,
      'Failed asserting that Field::longText() correctly set field type.'
    );
  }

  /**
   * Test for `Field::getLength()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::integer
   * @requires function Freyja\Database\Schema\Field::setIntegerType
   * @requires function Freyja\Database\Schema\Field::getLength
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testGetLengthPassingInvalidArguments() {
    // Set accessibility to object property.
    $reflection_length = new ReflectionProperty('Freyja\Database\Schema\Field', 'length');
    $reflection_length->setAccessible(true);

    $field = new Field('name');
    $field->integer(array());
    $array_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      11,
      $array_retrieved_length,
      'Failed asserting that Field::getLength() correctly set the default field type if an invalid argument (array) is passed.'
    );

    $field = new Field('name');
    $field->integer('ciaone');
    $non_numeric_string_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      11,
      $non_numeric_string_retrieved_length,
      'Failed asserting that Field::getLength() correctly set the default field type if an invalid argument (non numeric string) is passed.'
    );

    $field = new Field('name');
    $field->integer(new \stdClass);
    $object_retrieved_length = $reflection_length->getValue($field);
    $this->assertEquals(
      11,
      $object_retrieved_length,
      'Failed asserting that Field::getLength() correctly set the default field type if an invalid argument (object) is passed.'
    );
  }

  /**
   * Test for `Field::setRealType()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::double
   * @requires function Freyja\Database\Schema\Field::setRealType
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testSetRealType() {
    // Set accessibility to object properties.
    $reflection_length = new ReflectionProperty('Freyja\Database\Schema\Field', 'length');
    $reflection_decimals = new ReflectionProperty('Freyja\Database\Schema\Field', 'decimals');
    $reflection_length->setAccessible(true);
    $reflection_decimals->setAccessible(true);

    $field = new Field('name');
    $field->double(300, 28);
    $retrieved_length_1 = $reflection_length->getValue($field);
    $retrieved_decimals_1 = $reflection_decimals->getValue($field);
    $this->assertEquals(
      29,
      $retrieved_length_1,
      'Failed asserting that Field::setRealType() correctly set length to 1 unit more than decimals, if decimals is valid, and length isn\'t.'
    );
    $this->assertEquals(
      28,
      $retrieved_decimals_1,
      'Failed asserting that Field::setRealType() correctly set decimals if it\'s valid, when length is invalid.'
    );

    $field = new Field('name');
    $field->double(300, 8);
    $retrieved_length_2 = $reflection_length->getValue($field);
    $retrieved_decimals_2 = $reflection_decimals->getValue($field);
    $this->assertEquals(
      16,
      $retrieved_length_2,
      'Failed asserting that Field::setRealType() correctly set length to default if decimals is valid and minor than default length, and length isn\'t valid.'
    );
    $this->assertEquals(
      8,
      $retrieved_decimals_2,
      'Failed asserting that Field::setRealType() correctly set decimals if it\'s valid and minor than default length, when length is invalid.'
    );

    $field = new Field('name');
    $field->double(17, 35);
    $retrieved_length_3 = $reflection_length->getValue($field);
    $retrieved_decimals_3 = $reflection_decimals->getValue($field);
    $this->assertEquals(
      17,
      $retrieved_length_3,
      'Failed asserting that Field::setRealType() correctly set length if it\'s greater than default decimals, if decimals is invalid.'
    );
    $this->assertEquals(
      4,
      $retrieved_decimals_3,
      'Failed asserting that Field::setRealType() correctly set decimals to default, if it\'s invalid when length is valid and greater than default decimals.'
    );

    $field = new Field('name');
    $field->double(2, 35);
    $retrieved_length_4 = $reflection_length->getValue($field);
    $retrieved_decimals_4 = $reflection_decimals->getValue($field);
    $this->assertEquals(
      16,
      $retrieved_length_4,
      'Failed asserting that Field::setRealType() correctly set length to default, if decimals is invalid, and length is lower than default decimals.'
    );
    $this->assertEquals(
      4,
      $retrieved_decimals_4,
      'Failed asserting that Field::setRealType() correctly set decimals to default if invalid, when length is valid, but lower than decimals default.'
    );

    $field = new Field('name');
    $field->double(12, 12);
    $retrieved_length_5 = $reflection_length->getValue($field);
    $retrieved_decimals_5 = $reflection_decimals->getValue($field);
    $this->assertEquals(
      16,
      $retrieved_length_5,
      'Failed asserting that Field::setRealType() correctly set length to default, if it\'s equal to decimals, and decimals is valid and lower than default length.'
    );
    $this->assertEquals(
      12,
      $retrieved_decimals_5,
      'Failed asserting that Field::setRealType() correctly set decimals when equal to length, and lower than default length.'
    );

    $field = new Field('name');
    $field->double(20, 20);
    $retrieved_length_6 = $reflection_length->getValue($field);
    $retrieved_decimals_6 = $reflection_decimals->getValue($field);
    $this->assertEquals(
      21,
      $retrieved_length_6,
      'Failed asserting that Field::setRealType() correctly set length to 1 unit more than decimals, if equal to decimals, but decimals is greater than default length.'
    );
    $this->assertEquals(
      20,
      $retrieved_decimals_6,
      'Failed asserting that Field::setRealType() correctly set decimals when equal to length, and greater than default length.'
    );
  }

  /**
   * Test for `Field::setDefault()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::setDefault
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testSetDefault() {
    // Set accessibility to object property.
    $reflection_default = new ReflectionProperty('Freyja\Database\Schema\Field', 'default');
    $reflection_default->setAccessible(true);

    $field = new Field('name');
    $field->setDefault(56);
    $default = $reflection_default->getValue($field);
    $this->assertEquals(
      56,
      $default,
      'Failed asserting that Field::setDefault() correctly set a default integer value.'
    );

    $field = new Field('name');
    $field->setDefault('ciaone');
    $default = $reflection_default->getValue($field);
    $this->assertEquals(
      '\'ciaone\'',
      $default,
      'Failed asserting that Field::setDefault() correctly set a default string value.'
    );

    $field = new Field('name');
    $field->setDefault(Field::NULL);
    $default = $reflection_default->getValue($field);
    $this->assertEquals(
      'NULL',
      $default,
      'Failed asserting that Field::setDefault() correctly set a default NULL value.'
    );
  }

  /**
   * Test for `Field::setDefault()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::setDefault
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument value. Scalar expected, array given instead.
   */
  public function testSetDefaultWithInvalidValue() {
    $field = new Field('name');
    $field->setDefault(array());
  }

  /**
   * Test for `Field::setDefault()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::notNull
   * @requires function Freyja\Database\Schema\Field::setDefault
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage Cannot set default value to NULL if the field is NOT NULL
   */
  public function testSetDefaultNullValueWithNotNullField() {
    $field = new Field('name');
    $field->notNull()->setDefault();
  }

  /**
   * Test for `Field::notNull()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::notNull
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testNotNull() {
    // Set accessibility to object property.
    $reflection_nullable = new ReflectionProperty('Freyja\Database\Schema\Field', 'nullable');
    $reflection_nullable->setAccessible(true);

    $field = new Field('name');
    $field->notNull();
    $nullable = $reflection_nullable->getValue($field);
    $this->assertFalse($nullable, 'Failed asserting that Field::notNull() correctly set the field to NOT NULL.');
  }

  /**
   * Test for `Field::notNull()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::setDefault
   * @requires function Freyja\Database\Schema\Field::notNull
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage Cannot declare the field as NOT NULL if default value is set to NULL
   */
  public function testNotNullWithNullDefaultValue() {
    $field = new Field('name');
    $field->setDefault()->notNull();
  }

  /**
   * Test for `Field::unsigned()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::integer
   * @requires function Freyja\Database\Schema\Field::unsigned
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testUnsigned() {
    // Set accessibility to object property.
    $reflection_unsigned = new ReflectionProperty('Freyja\Database\Schema\Field', 'unsigned');
    $reflection_unsigned->setAccessible(true);

    $field = new Field('name');
    $field->integer()->unsigned();
    $unsigned = $reflection_unsigned->getValue($field);
    $this->assertTrue($unsigned, 'Failed asserting that Field::unsigned() correctly set the field to UNSIGNED.');
  }

  /**
   * Test for `Field::unsigned()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::char
   * @requires function Freyja\Database\Schema\Field::unsigned
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage The field type cannot be declared UNSIGNED
   */
  public function testUnsignedWithNotValidFieldType() {
    $field = new Field('name');
    $field->char()->unsigned();
  }

  /**
   * Test for `Field::autoIncrement()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::integer
   * @requires function Freyja\Database\Schema\Field::autoIncrement
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testAutoIncrement() {
    // Set accessibility to object property.
    $reflection_auto_increment = new ReflectionProperty('Freyja\Database\Schema\Field', 'auto_increment');
    $reflection_auto_increment->setAccessible(true);

    $field = new Field('name');
    $field->integer()->autoIncrement();
    $auto_increment = $reflection_auto_increment->getValue($field);
    $this->assertTrue($auto_increment, 'Failed asserting that Field::autoIncrement() correctly set the field to AUTO_INCREMENT.');
  }

  /**
   * Test for `Field::autoIncrement()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::char
   * @requires function Freyja\Database\Schema\Field::autoIncrement
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage The field type cannot be declared AUTO_INCREMENT
   */
  public function testAutoIncrementWithNotValidFieldType() {
    $field = new Field('name');
    $field->char()->autoIncrement();
  }

  /**
   * Test for `Field::getName()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::getName
   */
  public function testGetName() {
    $field = new Field('field');
    $name = $field->getName();
    $this->assertEquals(
      $name,
      'field',
      'Failed asserting that Field::getName() correctly retrieve the field name.'
    );
  }

  // TODO: start from getField().
}
