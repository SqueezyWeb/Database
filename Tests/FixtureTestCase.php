<?php
/**
 * FixtureTestCase class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests;

use mysqli;

/**
 * FixtureTestCase class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class FixtureTestCase extends \PHPUnit_Extensions_Database_Testcase {
  /**
   * Fixtures.
   *
   * @since 1.0.0
   * @access public
   * @var array
   */
  public $fixtures = array(
    'customers'
  );

  /**
   * Connection.
   *
   * @since 1.0.0
   * @access private
   */
  private $connection = null;

  /**
   * Execute before every test.
   *
   * @since 1.0.0
   * @access public
   */
  public function setUp() {
    $connection = $this->getConnection();
    $mysqli = $connection->getConnection();

    // Set up tables.
    $fixture_data_set = $this->getDataSet($this->fixtures);
    foreach ($fixture_data_set->getTableNames() as $table) {
      // Drop table.
      $mysqli->query("DROP TABLE IF EXISTS `$table`;");
      // Recreate table.
      $meta = $fixture_data_set->getTableMetaData($table);
      $create = "CREATE TABLE IF NOT EXISTS `$table` ";
      $cols = array();
      foreach ($meta->getColumns() as $col)
        $cols[] = "`$col`";
      $create .= '('.implode(',', $cols).');';
      $mysqli->query($create);
    }

    parent::setUp();
  }

  /**
   * Execute after every test.
   *
   * @since 1.0.0
   * @access public
   */
  public function tearDown() {
    $all_tables = $this->getDataSet($this->fixtures)->getTableNames();
    foreach ($all_tables as $table) {
      // Drop table.
      $connection = $this->getConnection();
      $mysqli = $connection->getConnection();
      $mysqli->query("DROP TABLE IF EXISTS `$table`;");
    }

    parent::tearDown();
  }

  /**
   * Connect to test database.
   *
   * @since 1.0.0
   * @access public
   */
  public function getConnection() {
    if ($this->connection == null) {
      try {
        $mysqli = new mysqli('localhost', 'root', 'root', 'test');
        $this->connection = $this->createDefaultDBConnection($mysqli, 'test');
      } catch(Exception $e) {
        echo $e->getMessage();
      }
    }
    return $this->connection;
  }

  /**
   * Retrieve the data to load.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $fixtures
   * @return PHPUnit_Extensions_Database_DataSet_CompositeDataSet
   */
  public function getDataSet($fixtures = array()) {
    if (empty($fixtures))
      $fixtures = $this->fixtures;

    $composite_ds = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());
    $fixture_path = dirname(__FILE__).'/fixtures';

    foreach ($fixtures as $fixture) {
      $path = $fixture_path."/$fixture.xml";
      $ds = new PHPUnit_Extensions_Database_DataSet_YamlDataSet($path);
      $composite_ds->addDataSet($ds);
    }
    return $composite_ds;
  }

  /**
   * Load a data set.
   *
   * @since 1.0.0
   * @access public
   */
  public function loadDataSet($dataset) {
    // Set the new dataset.
    $this->getDatabaseTester()->setDataSet($dataset);
    // Call setUp.
    $this->getDatabaseTester()->onSetUp();
  }
}
