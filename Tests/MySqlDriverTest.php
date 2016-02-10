<?php
/**
 * MySqlDriverTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests;

use Freyja\Database\Driver\MySqlDriver;

/**
 * MySqlDriverTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class MySqlDriverTest extends \PHPUnit_Extensions_Database_Testcase {
  /**
   * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  public function getConnection() {
    $pdo = new PDO('sqlite::memory:');
    return $this->createDefaultDBConnection($pdo, ':memory:');
  }

  /**
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  public function getDataSet() {
    return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
      dirname(__FILE__)."/_files/guestbook.yml"
    );
  }
}
