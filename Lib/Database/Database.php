<?php
/**
 * Database class file.
 *
 * @package Freyja\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database;
use Freyja\Database\Driver;
use Freyja\Exceptions\InvalidArgumentException as InvArgExcp;
use \RuntimeException;

/**
 * Database class.
 *
 * @package Freyja\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class Database {
  /**
   * Driver used to execute queries.
   *
   * @since 1.0.0
   * @access private
   * @var Driver
   */
  private $driver;

  /**
   * Global instance.
   *
   * @since 1.0.0
   * @access private
   * @static
   * @var Database
   */
  private static $global_instance;

  /**
   * Last executed query.
   *
   * @since 1.0.0
   * @access private
   * @var Query
   */
  private $last;

  /**
   * Class constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Driver $driver Driver used to execute queries.
   */
  public function __construct(Driver $driver) {
    $this->driver = $driver;
  }

  /**
   * Connect to database.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $host Database address.
   * @param string $database Database name.
   * @param string $username Access username.
   * @param string $password Access password.
   * @return self
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the arguments
   * isn't a string.
   */
  public function connect($host, $database, $username, $password) {
    foreach (array('host', 'database', 'username', 'password') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');

    $this->driver->connect($host, $database, $username, $password);

    return $this;
  }

  /**
   * Set global instance.
   *
   * Set this instance as global.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function setGlobal() {
    self::$global_dinstance = $this;
    return $this;
  }

  /**
   * Retrieve global instance.
   *
   * @since 1.0.0
   * @access public
   *
   * @return Database|boolean Global instance. False if global instance isn't
   * set.
   */
  public function getGlobal() {
    if (!isset(self::$global_instance))
      return false;

    return self::$global_instance;
  }

  /**
   * Retrieve Driver name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Driver name (e.g. 'MySql').
   */
  public function getDriver() {
    return $this->driver->getName();
  }

  /**
   * Execute query.
   *
   * Execute the specified query.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Query $query Query to execute.
   */
  public function execute(Query $query) {
    if (!$query->hasResult()) {
      $result = $this->driver->execute($query->build());
      $query->setResult($result);
    }

    $this->last = $query;
  }

  /**
   * Retrieve query results.
   *
   * Retrieve the results of the last executed query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return mixed Query results.
   *
   * @throws \RuntimeException if no query was ever executed in this Database
   * instance.
   */
  public function get() {
    if (!isset($this->last))
      throw new RuntimeException('A query must be executed before retrieving the results.');

    return $this->last->getResult();
  }

  /**
   * Retrieve query result.
   *
   * Retrieve only the first result row of the last executed query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return mixed Query result.
   *
   * @throws \RuntimeException if no query was ever executed in this Database
   * instance.
   */
  public function first() {
    try {
      $result = $this->get();
    } catch (Exception $e) {
      throw $e;
    }

    if (is_array($result))
      $result = $result[0];
    return $result;
  }
}
