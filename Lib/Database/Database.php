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
use Freyja\Database\Query;
use Freyja\Exceptions\RuntimeException;
use Freyja\Exceptions\ExceptionInterface;

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
   * @var Freyja\Database\Driver
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
   * @var Freyja\Database\Query
   */
  private $last;

  /**
   * Class constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Driver $driver Driver used to execute queries.
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
    try {
      $this->driver->connect($host, $database, $username, $password);
    } catch (ExceptionInterface $e) {
      throw $e;
    }

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
   * @return Database Global instance. Null if global instance isn't
   * set.
   */
  public function getGlobal() {
    return self::$global_instance;
  }

  /**
   * Retrieve Driver name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Driver name (e.g. 'MySqlDriver').
   */
  public function getDriver() {
    return get_class($this->driver);
  }

  /**
   * Execute query.
   *
   * Execute the specified query.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Query $query Query to execute.
   * @return self
   *
   * @throws Freyja\Exceptions\RuntimeException if it's raised by
   * Freyja\Database\Driver::execute().
   */
  public function execute(Query $query) {
    if (!$query->hasResult()) {
      try {
        $result = $this->driver->execute($query);
      } catch (Exception $e) {
        throw $e;
      }
      $query->setResult($result);
    }

    $this->last = $query;
    return $this;
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
   * @throws Freyja\Exceptions\RuntimeException if no query was ever executed in
   * this Database instance.
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
   * @throws Freyja\Exceptions\RuntimeException if no query was ever executed in
   * this Database instance.
   */
  public function first() {
    try {
      $result = $this->get();
    } catch (ExceptionInterface $e) {
      throw $e;
    }

    if (is_array($result))
      $result = array_shift($result);
    return $result;
  }
}
