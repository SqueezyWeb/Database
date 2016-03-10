<?php
/**
 * SchemaTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests\Schema;

use Freyja\Database\Tests\FixtureTestCase;
use Freyja\Database\Schema\Schema;
use Freyja\Database\Schema\Field;
use Freyja\Database\Schema\Table;
use Freyja\Database\Database;
use Freyja\Database\Driver\MySqlDriver;
use Freyja\Database\Query\MySqlQuery;
use Symfony\Component\Yaml\Yaml;
use \ReflectionProperty;

/**
 * SchemaTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class SchemaTest extends FixtureTestCase {
  /**
   * Execute once before all tests.
   *
   * @since 1.0.0
   * @access public
   * @static
   */
  public static function setUpBeforeClass() {
    $filename = getcwd().'/db/schema.yml';
    $fixture = dirname(__DIR__).'/fixtures/schema.yml';
    $schema = Yaml::parse(file_get_contents($fixture));
    $yaml_string = Yaml::dump($schema);
    if (!file_exists(getcwd().'/db'))
      mkdir(getcwd().'/db');
    file_put_contents($filename, $yaml_string);
  }

  /**
   * Execute once after all tests.
   *
   * @since 1.0.0
   * @access public
   * @static
   */
  public static function tearDownAfterClass() {
    unlink(getcwd().'/db/schema.yml');
    rmdir(getcwd().'/db');
  }

  /**
   * Test for `Schema::__construct()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testConstruct() {
    // Set accessibility to object property.
    $reflection_schema = new ReflectionProperty('Freyja\Database\Schema\Schema', 'schema');
    $reflection_schema->setAccessible(true);

    $database = new Database(new MySqlDriver);
    $schema = new Schema($database->connect('localhost', 'test', 'travis', ''));
    $retrieved_schema = Yaml::parse(file_get_contents(getcwd().'/db/schema.yml'));

    $this->assertEquals(
      $retrieved_schema['test'],
      $reflection_schema->getValue($schema),
      'Failed asserting that Schema correctly fetch the yaml file.'
    );
  }

  /**
   * Test for `Schema::create()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   * @requires function Freyja\Database\Schema\Schema::create
   */
  public function testCreate() {
    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);
    // Set accessbiility to object property.
    $reflection_schema = new ReflectionProperty('Freyja\Database\Schema\Schema', 'schema');
    $reflection_schema->setAccessible(true);

    $prod_id = new Field('product_id');
    $prod_id->varchar(200);
    $name = new Field('name');
    $name->varchar(200);
    $quantity = new Field('quantity');
    $quantity->varchar(200);
    $prods = new Table('products', array($prod_id, $name, $quantity));
    $db = new Database(new MySqlDriver);
    $schema = new Schema($db->connect('localhost', 'test', 'travis', ''));
    $schema->create($prods);
    $query = new MySqlQuery;
    $query->table('products')->insert(array('product_id', 'name', 'quantity'), array(array(null, null, null)));
    $db->execute($query);

    $query_table = $this->getConnection()->createQueryTable('products', 'SELECT * FROM products');
    $expected_table = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__DIR__).'/fixtures/products.yml');

    $this->assertTablesEqual($query_table, $expected_table->getTable('products'));

    $expected_schema = array(
      'fields' => array(
        'product_id' => array('type'=>'VARCHAR(200)','default'=>null,'NOT NULL'=>false,'UNSIGNED'=>false,'AUTO_INCREMENT'=>false),
        'name' => array('type'=>'VARCHAR(200)','default'=>null,'NOT NULL'=>false,'UNSIGNED'=>false,'AUTO_INCREMENT'=>false),
        'quantity' => array('type'=>'VARCHAR(200)','default'=>null,'NOT NULL'=>false,'UNSIGNED'=>false,'AUTO_INCREMENT'=>false)
      ),
      'primary' => array(),
      'foreign' => array(),
      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'engine' => 'InnoDB'
    );
    $retr_schema = $reflection_schema->getValue($schema);
    $this->assertEquals(
      $expected_schema,
      $retr_schema['tables']['products'],
      'Failed asserting that Schema::create() correctly update the database schema.'
    );

    $this->getConnection()->getConnection()->exec('DROP TABLE IF EXISTS products');
  }

  /**
   * Test for `Schema::remove()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Schema\Schema::remove
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   *
   * @dataProvider removeProvider
   */
  public function testRemove($table) {
    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);
    // Set accessibility to object property.
    $reflection_schema = new ReflectionProperty('Freyja\Database\Schema\Schema', 'schema');
    $reflection_schema->setAccessible(true);

    $db = new Database(new MySqlDriver);
    $schema = new Schema($db->connect('localhost', 'test', 'travis', ''));
    $schema->remove($table);
    $retr_schema = $reflection_schema->getValue($schema);
    $this->assertFalse(
      isset($retr_schema['tables']['customers']),
      'Failed asserting that Schema::remove() correctly update the database schema.'
    );

    $message = '';
    try {
      $result = $this->getConnection()->getConnection()->query('SELECT * FROM customers');
    } catch (\PDOException $e) {
      $message = $e->getMessage();
    }
    $this->assertEquals(
      'SQLSTATE[42S02]: Base table or view not found: 1146 Table \'test.customers\' doesn\'t exist',
      $message,
      'Failed asserting that Schema::remove() correctly drop a table from the database.'
    );

    // Reload original schema.
    self::setUpBeforeClass();
  }

  /**
   * Data Provider for `testRemove()`.
   *
   * @since 1.0.0
   * @access public
   */
  public function removeProvider() {
    return array(
      'object table' => array(new Table('customers')),
      'string table' => array('customers')
    );
  }

  /**
   * Test for `Schema::remove()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Schema\Schema::remove
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument table. String or Freyja\Database\Schema\Table expected, array given instead.
   */
  public function testRemoveWithInvalidData() {
    $db = new Database(new MySqlDriver);
    $schema = new Schema($db->connect('localhost', 'test', 'travis', ''));
    $schema->remove(array());
  }

  /**
   * Test for `Schema::alter()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   * @requires function Freyja\Database\Schema\Table::__construct
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testAlter() {
    // Set accessibility to object property.
    $reflection_schema = new ReflectionProperty('Freyja\Database\Schema\Schema', 'schema');
    $reflection_schema->setAccessible(true);

    $field = new Field('new_field');
    $fields = array($field->varchar(200));
    $table = new Table('customers');
    $db = new Database(new MySqlDriver);
    $schema = new Schema($db->connect('localhost', 'test', 'travis', ''));
    $schema->alter($table->addFields($fields));
    $retr_schema = $reflection_schema->getValue($schema);
    $this->assertTrue(
      isset($retr_schema['tables']['customers']['fields']['new_field']),
      'Failed asserting that Schema::alter() correctly add the new field to the database schema.'
    );
    $expected_schema_field = array(
      'type' => 'VARCHAR(200)',
      'default' => null,
      'NOT NULL' => false,
      'UNSIGNED' => false,
      'AUTO_INCREMENT' => false
    );
    $this->assertEquals(
      $expected_schema_field,
      $retr_schema['tables']['customers']['fields']['new_field'],
      'Failed asserting that Schema::alter() correctly add the new field information to the database schema.'
    );

    $message = '';
    try {
      $result = $this->getConnection()->getConnection()->query('SELECT new_field FROM customers');
    } catch (\PDOException $e) {
      $message = $e->getMessage();
    }

    $this->assertFalse(
      $message == 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'new_field\' in \'field list\'',
      'Failed asserting that Schema::alter() correctly alter a table.'
    );
  }

  /**
   * Test for `Schema::hasTable()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   * @requires function Freyja\Database\Schema\Schema::hasTable
   *
   * @dataProvider hasTableProvider
   */
  public function testHasTable($table, $not_existing_table) {
    $db = new Database(new MySqlDriver);
    $schema = new Schema($db->connect('localhost', 'test', 'travis', ''));
    $this->assertTrue(
      $schema->hasTable($table),
      'Failed asserting that Schema::hasTable() correctly state whether the specified table exists or not.'
    );
    $this->assertFalse(
      $schema->hasTable($not_existing_table),
      'Failed asserting that Schema::hasTable() correctly state whether the specified table exists or not.'
    );
  }

  /**
   * Data Provider for `testHasTable()`.
   *
   * @since 1.0.0
   * @access public
   */
  public function hasTableProvider() {
    return array(
      'object table' => array(new Table('customers'), new Table('not_existing_table')),
      'string table' => array('customers', 'not_existing_table')
    );
  }

  /**
   * Test for `Schema::hasTable()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Schema\Schema::__construct
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   * @requires function Freyja\Database\Schema\Schema::hasTable
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument table. String or Freyja\Database\Schema\Table expected, array given instead.
   */
  public function testHasTableWithInvalidArgument() {
    $db = new Database(new MySqlDriver);
    $schema = new Schema($db->connect('localhost', 'test', 'travis', ''));
    $schema->hasTable(array());
  }
}
