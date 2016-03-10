<?php
/**
 * Migration Repository Test.
 *
 * @package Freyja\Database\Tests\Migrations
 * @copyright 2016 SqueezyWeb
 * @since 0.3.0
 */

namespace Freyja\Database\Tests\Migrations;

use Freyja\Database\Tests\FixtureTestCase;
use Freyja\Database\Migrations\Repository;
use Freyja\Database\Migrations\Migration;
use Freyja\Database\Database;
use Freyja\Database\Schema\Schema;
use Freyja\Database\Schema\Table;
use Freyja\Database\Driver\MySqlDriver;
use Symfony\Component\Yaml\Yaml;
use ReflectionObject;

/**
 * Migration Repository Test.
 *
 * @package Freyja\Database\Tests\Migrations
 * @author Mattia Migliorini <mattia@squeezyweb.com>
 * @since 0.3.0
 * @version 1.0.0
 */
class RepositoryTest extends FixtureTestCase {
	/**
	 * Fixtures.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $fixtures = array('migrations');

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
	 * Test constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function testConstruct() {
		$db = new Database(new MySqlDriver);
		$repository = new Repository($db, 'migrations');

		$r = new ReflectionObject($repository);
		$database = $r->getProperty('database');
		$table = $r->getProperty('table');
		$query = $r->getProperty('query');
		$database->setAccessible(true);
		$table->setAccessible(true);
		$query->setAccessible(true);

		$this->assertSame($db, $database->getValue($repository), '__construct() registers the passed Database instance as class property');
		$this->assertEquals('migrations', $table->getValue($repository), '__construct() registers the migration repository table name');
		$this->assertEquals('Freyja\Database\Query\MySqlQuery', $query->getValue($repository), '__construct() correctly sets query object name up');
	}

	/**
	 * Test constructor with invalid table parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed $table
	 *
	 * @expectedException Freyja\Exceptions\InvalidArgumentException
	 * @dataProvider provideInvalidTableTypes
	 */
	public function testConstructorInvalidTable($table) {
		new Repository(new Database(new MySqlDriver), $table);
	}

	/**
	 * Provide invalid table parameter types for testConstructorInvalidTable().
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function provideInvalidTableTypes() {
		return array(
			'int' => array(56),
			'float' => array(56.5),
			'bool' => array(false),
			'array' => array(array()),
			'object' => array(new \StdClass)
		);
	}

	/**
	 * Test getRan() method.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function testGetRan() {
    $this->loadData();
    $repository = $this->getRepository();

    $ran = $repository->getRan();

    $expected = array(1457091317, 1457091829);

    $this->assertEquals($expected, $ran, 'getRan() returns an array of ran migration batches');
	}

  /**
   * Test getLast() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testGetLast() {
    $this->loadData();
    $repository = $this->getRepository();

    $actual = $repository->getLast();
    $expected = array(
      'batch' => 1457091829,
      'migration' => '1457091829_migrationCreateTest'
    );
    $this->assertEquals($expected, $actual, 'getLast() returns the last ran migration');
  }

  /**
   * Test log() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testLog() {
    $repository = $this->getRepository();

    $repository->log('1457091317_migrationTest', 1457091317);

    $actual = $repository->getLast();
    $expected = array(
      'batch' => 1457091317,
      'migration' => '1457091317_migrationTest'
    );
    $this->assertEquals($expected, $actual, 'log() logs migration data');
  }

  /**
   * Test log() method with invalid parameters.
   *
   * @since 1.0.0
   * @access public
   *
   * @param mixed $migration
   * @param mixed $batch
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @dataProvider provideInvalidLogParameters
   */
  public function testLogInvalid($migration, $batch) {
    $repository = $this->getRepository();

    $repository->log($migration, $batch);
  }

  /**
   * Provide invalid parameters for testLogInvalid().
   *
   * @since 1.0.0
   * @access public
   */
  public function provideInvalidLogParameters() {
    $types = array(
      56,
      56.3,
      false,
      array(),
      new \StdClass,
      '1457091317_migrationTest'
    );

    $provide = array();
    foreach ($types as $type)
      $provide[] = array($type, $type);

    return $provide;
  }

  /**
   * Test delete() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testDelete() {
    $this->loadData();
    $repository = $this->getRepository();

    // TODO: populate test once Migration class is done.
    $this->markTestIncomplete('Test will be completed once Migration class is done');
  }

  /**
   * Test getLastBatchNumber() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testGetLastBatchNumber() {
    $this->loadData();
    $repository = $this->getRepository();

    $actual = $repository->getLastBatchNumber();
    $this->assertEquals(1457091829, $actual, 'getLastBatchNumber() returns the batch of the last migration');
  }

  /**
   * Test createRepository() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testCreateRepository() {
    $database = $this->getDatabase();
    $schema = new Schema($database);
    $schema->remove(new Table('migrations'));

    $repository = $this->getRepository();

    $repository->createRepository();

    // We need to create a new Schema instance here, because Schema::hasTable()
    // only looks at Schema::$schema and not at the schema file as of v1.0.0.
    $schema = new Schema($database);

    $this->assertTrue($schema->hasTable(new Table('migrations')), 'createRepository() creates the migrations table');
  }

  /**
   * Test repositoryExists() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testRepositoryExists() {
    $this->loadData();
    $repository = $this->getRepository();

    $this->assertTrue($repository->repositoryExists(), 'repositoryExists() returns true if the migrations table exists');

    $schema = new Schema($this->getDatabase());
    $schema->remove(new Table('migrations'));
    $this->assertFalse($repository->repositoryExists(), 'repositoryExists() returns false if the migrations table does not exist');
  }

  /**
   * Test getDatabase() method.
   *
   * @since 1.0.0
   * @access public
   */
  public function testGetDatabase() {
    $database = $this->getDatabase();
    $repository = new Repository($database, 'migrations');

    $actual = $repository->getDatabase();
    $this->assertSame($database, $actual, 'getDatabase() returns the Database instance associated with the repository');
  }

  /**
   * Load fixtures data.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function loadData($data = null) {
    // Load data.
    if (!is_null($data))
      $this->loadDataSet($this->getDataSet($data));
    else
      $this->loadDataSet($this->getDataSet($this->fixtures));
  }

  /**
   * Retrieve database connection.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function getDatabase() {
    $database = new Database(new MySqlDriver);
    $database->connect('localhost', 'test', 'travis', '');
    return $database;
  }

  /**
   * Retrieve repository instance.
   *
   * @since 1.0.0
   * @access protected
   *
   * @see RepositoryTest::getDatabase()
   */
  protected function getRepository() {
    return new Repository($this->getDatabase(), 'migrations');
  }
}
