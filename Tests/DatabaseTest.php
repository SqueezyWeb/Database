<?php
/**
 * DatabaseTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests;

use Freyja\Database\Tests\FixtureTestCase;
use Freyja\Database\Driver\MySqlDriver;
use Freyja\Database\Query\MySqlQuery;
use Freyja\Database\Database;
use Freyja\Exceptions\RuntimeException;
use \ReflectionProperty;

/**
 * DatabaseTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class DatabaseTest extends FixtureTestCase {
  /**
   * Test for `Database::connect()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::connect
   */
  public function testConnect() {
    // Set accessibility to object property.
    $reflection_connection = new ReflectionProperty('Freyja\Database\Driver\MySqlDriver', 'connection');
    $reflection_connection->setAccessible(true);

    $driver = new MySqlDriver;
    $db = new Database($driver);
    $db->connect('localhost', 'test', 'gian', 'gian');
    $connection = $reflection_connection->getValue($driver);

    $this->assertNull(
      $connection->connect_error,
      'Failed asserting that Database correctly connect to the database.'
    );
  }

  /**
   * Test for `Database::getDriver()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::getDriver
   */
  public function testGetDriver() {
    $driver = new MySqlDriver;
    $database = new Database($driver);
    $driver_name = $database->getDriver();

    $this->assertEquals(
      $driver_name,
      'MySqlDriver',
      'Failed asserting that Database::getName() correctly retrieve the driver name.'
    );
  }

  /**
   * Test for `Database::execute()` and `Database::get()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::execute
   * @requires function Freyja\Database\Database::get
   */
  public function testExecuteAndGet() {
    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);

    $query = new MySqlQuery;
    $query->table('customers')->select(array());
    $driver = new MySqlDriver;
    $db = new Database($driver);
    $result = $db->connect('localhost', 'test', 'gian', 'gian')->execute($query)->get();
    $expected_result = array(array(
      'customer_id' => '1',
      'name' => 'Tizio',
      'surname' => 'Caio',
      'email' => 'tizio.caio@email.address'
    ), array(
      'customer_id' => '2',
      'name' => 'Altro Tizio',
      'surname' => 'Altro Caio',
      'email' => 'altro.tiziocaio@email.address'
    ));

    $this->assertEquals(
      $result,
      $expected_result,
      'Failed asserting that Database correctly execute and retrieve the result of a query.'
    );
  }

  /**
   * Test for `Database::execute()` and `Database::first()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::execute
   * @requires function Freyja\Database\Database::first
   */
  public function testExecuteAndFirst() {
    // Load data.
    $ds = $this->getDataSet(array('customers'));
    $this->loadDataSet($ds);

    $query = new MySqlQuery;
    $query->table('customers')->select(array());
    $driver = new MySqlDriver;
    $db = new Database($driver);
    $result = $db->connect('localhost', 'test', 'gian', 'gian')->execute($query)->first();
    $expected_result = array(
      'customer_id' => '1',
      'name' => 'Tizio',
      'surname' => 'Caio',
      'email' => 'tizio.caio@email.address'
    );

    $this->assertEquals(
      $result,
      $expected_result,
      'Failed asserting that Database correctly execute and retrieve the result of a query.'
    );
  }

  /**
   * Test for `Database::get()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Database::__construct
   * @requires function Freyja\Database\Database::get
   *
   * @expectedException Freyja\Exceptions\RuntimeException
   * @expectedExceptionMessage A query must be executed before retrieving the results.
   */
  public function testGetWithoutExecutingAnyQuery() {
    $driver = new MySqlDriver;
    $db = new Database($driver);
    $db->connect('localhost', 'test', 'gian', 'gian')->get();
  }
}
