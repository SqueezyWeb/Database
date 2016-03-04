<?php
/**
 * TableTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests\Schema;

use Freyja\Database\Schema\Table;
use Freyja\Database\Schema\Field;
use \ReflectionProperty;

/**
 * TableTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class TableTest extends \PHPUnit_Framework_Testcase {
  /**
   * Test for `Table::__construct()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testConstruct() {
    // Set accessibility to object properties.
    $reflection_name = new ReflectionProperty('Freyja\Database\Schema\Table', 'name');
    $reflection_fields = new ReflectionProperty('Freyja\Database\Schema\Table', 'fields');
    $reflection_charset = new ReflectionProperty('Freyja\Database\Schema\Table', 'charset');
    $reflection_collation = new ReflectionProperty('Freyja\Database\Schema\Table', 'collation');
    $reflection_engine = new ReflectionProperty('Freyja\Database\Schema\Table', 'engine');
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Table', 'type');
    $reflection_name->setAccessible(true);
    $reflection_fields->setAccessible(true);
    $reflection_charset->setAccessible(true);
    $reflection_collation->setAccessible(true);
    $reflection_engine->setAccessible(true);
    $reflection_type->setAccessible(true);

    $table = new Table('table', array(new Field('f1'), new Field('f2')));

    $this->assertEquals(
      'table',
      $reflection_name->getValue($table),
      'Failed asserting that Table::__construct() correctly set the table name.'
    );
    $fields = $reflection_fields->getValue($table);
    $this->assertTrue(
      is_array($fields),
      'Failed asserting that Table::__construct() correctly set the field array.'
    );
    $this->assertEquals(
      'f1',
      $fields['f1']->getName(),
      'Failed asserting that Table::__construct() correctly set the table charset.'
    );
    $this->assertEquals(
      'f2',
      $fields['f2']->getName(),
      'Failed asserting that Table::__construct() correctly set the table charset.'
    );
    $this->assertEquals(
      'utf8',
      $reflection_charset->getValue($table),
      'Failed asserting that Table::__construct() correctly set the table charset.'
    );
    $this->assertEquals(
      'utf8_unicode_ci',
      $reflection_collation->getValue($table),
      'Failed asserting that Table::__construct() correctly set the table collation.'
    );
    $this->assertEquals(
      'InnoDB',
      $reflection_engine->getValue($table),
      'Failed asserting that Table::__construct() correctly set the table engine.'
    );
    $this->assertEquals(
      'create',
      $reflection_type->getValue($table),
      'Failed asserting that Table::__construct() correctly set the table engine.'
    );
  }

  /**
   * Test for `Table::__construct()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument name. String expected, array given instead.
   */
  public function testConstructWithException() {
    $table = new Table(array());
  }

  /**
   * Test for `Table::__construct()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument field. Freyja\Database\Schema\Field expected, string given instead.
   */
  public function testConstructWithInvalidFields() {
    $table = new Table('table', array('field'));
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   */
  public function testPrimaryKey() {
    // Set accessibility to object properties.
    $reflection_keys = new ReflectionProperty('Freyja\Database\Schema\Table', 'primary_keys');
    $reflection_name = new ReflectionProperty('Freyja\Database\Schema\Table', 'primary_name');
    $reflection_keys->setAccessible(true);
    $reflection_name->setAccessible(true);

    $table = new Table('table', array(new Field('f1'), new Field('f2')));
    $table->primaryKey('f1');

    $this->assertEquals(
      count($reflection_keys->getValue($table)),
      1,
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertTrue(
      in_array('f1', $reflection_keys->getValue($table)),
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertEquals(
      $reflection_name->getValue($table),
      'f1',
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   */
  public function testPrimaryKeyWithMultipleFields() {
    // Set accessibility to object properties.
    $reflection_keys = new ReflectionProperty('Freyja\Database\Schema\Table', 'primary_keys');
    $reflection_name = new ReflectionProperty('Freyja\Database\Schema\Table', 'primary_name');
    $reflection_keys->setAccessible(true);
    $reflection_name->setAccessible(true);

    $table = new Table('table', array(new Field('f1'), new Field('f2')));
    $table->primaryKey(array('f1', 'f2'), 'f');

    $this->assertEquals(
      count($reflection_keys->getValue($table)),
      2,
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertTrue(
      in_array('f1', $reflection_keys->getValue($table)),
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertTrue(
      in_array('f2', $reflection_keys->getValue($table)),
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertEquals(
      $reflection_name->getValue($table),
      'f',
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   */
  public function testPrimaryKeyChangingName() {
    // Set accessibility to object properties.
    $reflection_keys = new ReflectionProperty('Freyja\Database\Schema\Table', 'primary_keys');
    $reflection_name = new ReflectionProperty('Freyja\Database\Schema\Table', 'primary_name');
    $reflection_keys->setAccessible(true);
    $reflection_name->setAccessible(true);

    $table = new Table('table', array(new Field('f1'), new Field('f2')));
    $table->primaryKey('f1', 'f');

    $this->assertEquals(
      count($reflection_keys->getValue($table)),
      1,
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertTrue(
      in_array('f1', $reflection_keys->getValue($table)),
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
    $this->assertEquals(
      $reflection_name->getValue($table),
      'f',
      'Failed asserting that Table::primaryKey() correctly set the foreign key.'
    );
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument field. Array or String expected, integer given instead.
   */
  public function testPrimaryKeyWithInvalidFirstArgument() {
    $table = new Table('table');
    $table->primaryKey(56);
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * expectedExceptionMessage Wrong type for argument name. String or null expected, array given instead.
   */
  public function testPrimaryKeyWithInvalidSecondArgument() {
    $table = new Table('table');
    $table->primaryKey('f', array());
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage Setting more than one field as primary key requires a name to be set for that key
   */
  public function testPrimaryKeyWithNoName() {
    $table = new Table('table');
    $table->primaryKey(array('f1', 'f2'));
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument field name. String expected, array given instead.
   */
  public function testPrimaryKeyWithInvalidFieldName() {
    $table = new Table('table');
    $table->primaryKey(array(array()), 'f');
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Field name(s) passed to `Table::primaryKey()` must match the fields of the table
   */
  public function testPrimaryKeyWithNoMatchingField() {
    $table = new Table('table', array(new Field('f1')));
    $table->primaryKey('f');
  }

  /**
   * Test for `Table::primaryKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Field name(s) passed to `Table::primaryKey()` must match the fields of the table
   */
  public function testPrimaryKeyWithNoMatchingFields() {
    $table = new Table('table', array(new Field('f1')));
    $table->primaryKey(array('f'), 'f');
  }

  /**
   * Test for `Table::foreignKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::foreignKey
   */
  public function testForeignKey() {
    // Set accessibility to object property.
    $reflection_foreign_key = new ReflectionProperty('Freyja\Database\Schema\Table', 'foreign_keys');
    $reflection_foreign_key->setAccessible(true);

    $table = new Table('table', array(new Field('f')));
    $table->foreignKey('f', 'table2', 'f');
    $foreign_key = $reflection_foreign_key->getValue($table);

    $this->assertEquals(
      'table2',
      $foreign_key['f']['references'],
      'Failed asserting that Table::foreignKey() correctly set a foreign key, referencing the correct table.'
    );
    $this->assertEquals(
      'f',
      $foreign_key['f']['on'],
      'Failed asserting that Table::foreignKey() correctly set a foreign key, referencing the correct field.'
    );
  }

  /**
   * Test for `Table::foreignKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::foreignKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument field. String expected, array given instead.
   */
  public function testForeignKeyWithInvalidArgument() {
    $table = new Table('table');
    $table->foreignKey(array(), 't', 'f');
  }

  /**
   * Test for `Table::foreignKey()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires funciton Freyja\Database\Schema\Table::foreignKey
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Cannot set a foreign key on a non existing field
   */
  public function testForeignKeyOnNonExistingField() {
    $table = new Table('table', array(new Field('f1')));
    $table->foreignKey('f', 't', 'f');
  }

  /**
   * Test for `Table::getName()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::getName
   */
  public function testGetName() {
    $table = new Table('table');
    $this->assertEquals(
      'table',
      $table->getName(),
      'Failed asserting that Table::getName() correctly retrieves the table name.'
    );
  }

  /**
   * Test for `Table::getTable()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::integer
   * @requires function Freyja\Database\Schema\Field::float
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requries function Freyja\Database\Schema\Table::getTable
   */
  public function testGetTable() {
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $table = new Table('table', array($f1->integer(), $f2->float()));
    $expected = array('table' => array(
      'fields' => array('f1'=>array(
        'type' => 'INT(11)',
        'default' => null,
        'NOT NULL' => false,
        'UNSIGNED' => false,
        'AUTO_INCREMENT' => false
      ), 'f2'=>array(
        'type' => 'FLOAT(10,2)',
        'default' => null,
        'NOT NULL' => false,
        'UNSIGNED' => false,
        'AUTO_INCREMENT' => false
      )),
      'primary' => array(),
      'foreign' => array(),
      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'engine' => 'InnoDB'
    ));

    $this->assertEquals(
      $expected,
      $table->getTable(),
      'Failed asserting that Table::getTable() returns the correct information of the table.'
    );
  }

  /**
   * Test for `Table::getTable()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Field::integer
   * @requires function Freyja\Database\Schema\Field::float
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::primaryKey
   * @requires function Freyja\Database\Schema\Table::foreignKey
   * @requries function Freyja\Database\Schema\Table::getTable
   */
  public function testGetTableWithPrimaryAndForeignKeys() {
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $table = new Table('table', array($f1->integer()->autoIncrement(), $f2->float()));
    $table->primaryKey('f1', 'f')->foreignKey('f2', 'some_table', 'some_field');
    $expected = array('table' => array(
      'fields' => array('f1'=>array(
        'type' => 'INT(11)',
        'default' => null,
        'NOT NULL' => false,
        'UNSIGNED' => false,
        'AUTO_INCREMENT' => true
      ), 'f2'=>array(
        'type' => 'FLOAT(10,2)',
        'default' => null,
        'NOT NULL' => false,
        'UNSIGNED' => false,
        'AUTO_INCREMENT' => false
      )),
      'primary' => array('f' => array('f1')),
      'foreign' => array('f2' => array(
        'references' => 'some_table',
        'on' => 'some_field'
      )),
      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'engine' => 'InnoDB'
    ));

    $this->assertEquals(
      $expected,
      $table->getTable(),
      'Failed asserting that Table::getTable() returns the correct information of the table.'
    );
  }

  /**
   * Test for `Table::drop()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::drop
   */
  public function testDrop() {
    // Set accessibility to object property.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Table', 'type');
    $reflection_type->setAccessible(true);

    $table = new Table('table');
    $table->drop();

    $this->assertEquals(
      'drop',
      $reflection_type->getValue($table),
      'Failed asserting that Table::drop() correctly set the query type.'
    );
  }

  /**
   * Test for `Table::addFields()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Table::addFields
   */
  public function testAddFields() {
    // Set accessibility to object property.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Table', 'type');
    $reflection_alter = new ReflectionProperty('Freyja\Database\Schema\Table', 'alter_fields');
    $reflection_type->setAccessible(true);
    $reflection_alter->setAccessible(true);

    $table = new Table('table');
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $table->addFields(array($f1, $f2));
    $expected = array('ADD' => array($f1, $f2), 'DROP COLUMN' => array());

    $this->assertEquals(
      'alter',
      $reflection_type->getValue($table),
      'Failed asserting that Table::addFields() correctly set the query type.'
    );
    $this->assertEquals(
      $expected,
      $reflection_alter->getValue($table),
      'Failed asserting that Table::addFields() correctly set the alter fields.'
    );
  }

  /**
   * Test for `Table::alter()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::addFields
   * @requires function Freyja\Database\Schema\Table::alter
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage It's required at least one column to alter the table
   */
  public function testAlterWithException() {
    $table = new Table('table');
    $table->addFields(array());
  }

  /**
   * Test for `Table::alter()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::addFields
   * @requires function Freyja\Database\Schema\Table::alter
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument field. Freyja\Database\Schema\Field expected, string given instead.
   */
  public function testAlterWithInvalidArgument() {
    $table = new Table('table');
    $table->addFields(array('f1'));
  }

  /**
   * Test for `Table::removeFields()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Field::__construct
   * @requires function Freyja\Database\Schema\Table::addFields
   */
  public function testRemoveFields() {
    // Set accessibility to object property.
    $reflection_type = new ReflectionProperty('Freyja\Database\Schema\Table', 'type');
    $reflection_alter = new ReflectionProperty('Freyja\Database\Schema\Table', 'alter_fields');
    $reflection_type->setAccessible(true);
    $reflection_alter->setAccessible(true);

    $table = new Table('table');
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $table->removeFields(array($f1, $f2));
    $expected = array('ADD' => array(), 'DROP COLUMN' => array($f1, $f2));

    $this->assertEquals(
      'alter',
      $reflection_type->getValue($table),
      'Failed asserting that Table::addFields() correctly set the query type.'
    );
    $this->assertEquals(
      $expected,
      $reflection_alter->getValue($table),
      'Failed asserting that Table::addFields() correctly set the alter fields.'
    );
  }

  /**
   * Test for `Table::getAlteration()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::addFields
   * @requires function Freyja\Database\Schema\Table::removeFields
   * @requires function Freyja\Database\Schema\Table::getAlteration
   */
  public function testGetAlteration() {
    $table = new Table('table');
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $f3 = new Field('f3');
    $table->addFields(array($f1->integer(), $f3->integer()))->removeFields(array($f2->integer()));
    $expected = array('ADD' => array(
      'f1' => array('type'=>'INT(11)','default'=>null,'NOT NULL'=>false,'UNSIGNED'=>false,'AUTO_INCREMENT'=>false),
      'f3' => array('type'=>'INT(11)','default'=>null,'NOT NULL'=>false,'UNSIGNED'=>false,'AUTO_INCREMENT'=>false)
    ), 'DROP COLUMN' => array(
      'f2' => array('type'=>'INT(11)','default'=>null,'NOT NULL'=>false,'UNSIGNED'=>false,'AUTO_INCREMENT'=>false)
    ));

    $this->assertEquals(
      $expected,
      $table->getAlteration(),
      'Failed asserting that Table::getAlteration correctly retrieves alteration information.'
    );
  }

  /**
   * Test for `Table::buildCreate()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::build
   * @requires function Freyja\Database\Schema\Table::buildCreate
   */
  public function testBuildCreate() {
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $table = new Table('table', array($f1->integer(), $f2->integer()));
    $expected = 'CREATE TABLE IF NOT EXISTS table (f1 INT(11), f2 INT(11)) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;';

    $this->assertEquals(
      $expected,
      $table->build(),
      'Failed asserting that Table methods correctly build a CREATE query.'
    );
  }

  /**
   * Test for `Table::buildCreate()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::build
   * @requires function Freyja\Database\Schema\Table::buildCreate
   *
   * @expectedException Freyja\Exceptions\LogicException
   * @expectedExceptionMessage A table must have at least 1 column
   */
  public function testBuildCreateWithoutFields() {
    $table = new Table('table');
    $table->build();
  }

  /**
   * Test for `Table::buildCreate()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::build
   * @requires function Freyja\Database\Schema\Table::buildCreate
   */
  public function testBuildCreateWithKeys() {
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $table = new Table('table', array($f1->integer()->autoIncrement(), $f2->integer()));
    $table->primaryKey(array('f1','f2'), 'f')->foreignKey('f2', 'some_table', 'some_field');
    $expected = 'CREATE TABLE IF NOT EXISTS table (f1 INT(11) AUTO_INCREMENT, f2 INT(11), CONSTRAINT f PRIMARY KEY (f1,f2), FOREIGN KEY (f2) REFERENCES some_table(some_field)) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;';

    $this->assertEquals(
      $expected,
      $table->build(),
      'Failed asserting that Table methods correctly build a CREATE query.'
    );
  }

  /**
   * Test for `Table::buildDrop()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::drop
   * @requires function Freyja\Database\Schema\Table::build
   * @requires function Freyja\Database\Schema\Table::buildDrop
   */
  public function testBuildDrop() {
    $table = new Table('table', array(new Field('field')));
    $table->drop();
    $expected = 'DROP TABLE IF EXISTS table;';
    $this->assertEquals(
      $expected,
      $table->build(),
      'Failed asserting that Table methods correctly build a DROP table.'
    );
  }

  /**
   * Test for `Table::buildAlter()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function Freyja\Database\Schema\Table::build
   * @requires function Freyja\Database\Schema\Table::buildAlter
   */
  public function testBuildAlter() {
    $table = new Table('table');
    $f1 = new Field('f1');
    $f2 = new Field('f2');
    $f3 = new Field('f3');
    $table->addFields(array($f1->integer(), $f2->integer()))->removeFields(array($f3->integer()));
    $expected = 'ALTER TABLE table ADD f1 INT(11), ADD f2 INT(11), DROP COLUMN f3;';
    $this->assertEquals(
      $expected,
      $table->build(),
      'Failed asserting that Table methods correctly build a DROP table.'
    );
  }
}
