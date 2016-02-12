<?php
/**
 * MySqlDriverTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests;

use Freyja\Database\Tests\FixtureTestCase;
use Freyja\Database\Driver\MySqlDriver;
use ReflectionProperty;

/**
 * MySqlDriverTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
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
    $connection = $this->getConnection()->getConnection();

    // Set accessibility to object property.
    $reflection_connection = new ReflectionProperty('Freyja\Database\Driver\MySqlDriver', 'connection');
    $reflection_connection->setAccessible(true);

    $driver = new MySqlDriver('localhost', 'test', 'gian', 'gian');
    $driver_connection = $reflection_connection->getValue($driver);

    $this->assertEquals(
      $connection,
      $driver_connection,
      'Failed asserting that MySqlDriver correctly connect to the database.'
    );
  }
}
