<?php
/**
 * MySqlDriverTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests\Driver;

use Freyja\Database\Tests\FixtureTestCase;
use Freyja\Database\Tests\Customer;
use Freyja\Database\Driver\MySqlDriver;
use Freyja\Database\Query\MySqlQuery;
use ReflectionProperty;

/**
 * MySqlDriverTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @author Mattia Migliorini <mattia@squeezyweb.com>
 * @since 0.1.0
 * @version 1.1.0
 */
class MySqlDriverTest extends FixtureTestCase {
  /**
   * Fixtures.
   *
   * @since 1.0.0
   * @access public
   * @var array
   */
  public $fixtures = array('customers');

  /**
   * Test for `MySqlDriver::connect()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Driver\MySqlDriver::connect
   */
  public function testConnect() {
    // Set accessibility to object property.
    $reflection_connection = new ReflectionProperty('Freyja\Database\Driver\MySqlDriver', 'connection');
    $reflection_connection->setAccessible(true);

    $driver = new MySqlDriver;
    $driver->connect('localhost', 'test', 'travis', '');
    $driver_connection = $reflection_connection->getValue($driver);

    $this->assertNull(
      $driver_connection->connect_error,
      'Failed asserting that MySqlDriver correctly connect to the database.'
    );
  }

  /**
   * Test for `MySqlDriver::getName()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Driver\MySqlDriver::getName
   */
  public function testGetName() {
    $driver = new MySqlDriver;
    $driver_name = $driver->getName();

    $this->assertEquals(
      $driver_name,
      'MySqlDriver',
      'Failed asserting that MySqlDriver::getName() correctly retrieve the driver name.'
    );
  }

  /**
   * Test for `MySqlDriver::execute()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Driver\MySqlDriver::execute
   */
  public function testExecute() {
    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);

    $query = new MySqlQuery;
    $query->table('customers')->select(array('name', 'surname'))->where('customer_id', 1);

    $driver = new MySqlDriver;
    $result = $driver->connect('localhost', 'test', 'travis', '')->execute($query);

    $this->assertEquals(
      $result[0]['name'],
      'Tizio',
      'Failed asserting that MySqlDriver::execute correctly execute the specified query.'
    );

    $this->assertEquals(
      $result[0]['surname'],
      'Caio',
      'Failed asserting that MySqlDriver::execute correctly execute the specified query.'
    );
  }

  /**
   * Test execute() passing $object.
   *
   * @since 1.1.0
   * @access public
   *
   * @requires function Freyja\Database\Driver\MySqlDriver::execute
   */
  public function testExecuteObject() {
    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);

    $query = new MySqlQuery;
    $query->table('customers')->select(array('name', 'surname'))->where('customer_id', 1);

    $driver = new MySqlDriver;
    $result = $driver->connect('localhost', 'test', 'travis', '')->execute($query, true);

    $expected = new \StdClass;
    $expected->name = 'Tizio';
    $expected->surname = 'Caio';

    $this->assertEquals(
      $result[0],
      $expected,
      'execute() returns StdClass objects if $object parameter is set to true'
    );
  }

  /**
   * Test execute() passing a specific class name as $object.
   *
   * @since 1.1.0
   * @access public
   *
   * @requires function Freyja\Database\Driver\MySqlDriver::execute
   */
  public function testExecuteObjectCustomer() {
    include(dirname(__DIR__).'/fixtures/Customer.php');

    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);

    $query = new MySqlQuery;
    $query->table('customers')->select(array('name', 'surname'))->where('customer_id', 1);

    $driver = new MySqlDriver;
    $result = $driver->connect('localhost', 'test', 'travis', '')->execute($query, 'Freyja\Database\Tests\Customer');

    $expected = new Customer(null, 'Tizio', 'Caio');

    $this->assertEquals(
      $expected,
      $result[0],
      'execute() returns Customer object if $object parameter is set to Customer'
    );
  }

  /**
   * Test for `MySqlDriver::execute()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Driver\MySqlDriver::execute
   */
  public function testExecuteWithEscapedStrings() {
    // Load data.
    $connection = $this->getConnection();
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);

    $query = new MySqlQuery;
    $query->table('customers')->select('email')->where('name', 'Tizio');

    $driver = new MySqlDriver;
    $result = $driver->connect('localhost', 'test', 'travis', '')->execute($query);

    $this->assertEquals(
      $result[0]['email'],
      'tizio.caio@email.address',
      'Failed asserting that MySqlDriver::execute correctly execute the specified query.'
    );
  }
}
