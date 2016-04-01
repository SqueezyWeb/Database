<?php
/**
 * MySqlDriver class file.
 *
 * @package Freyja\Database\Driver
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database\Driver;

use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\RuntimeException;
use Freyja\Database\Query\Query;
use mysqli;

/**
 * MySqlDriver class.
 *
 * @package Freyja\Database\Driver
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 1.1.0 Added parameter $object to execute().
 * @since 0.1.0
 * @version 1.1.0
 */
class MySqlDriver implements Driver {
  /**
   * Connection object.
   *
   * @since 1.0.0
   * @access private
   * @var \mysqli
   */
  private $connection;

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
   * @throws Freyja\Exceptions\RuntimeException if the connection has an error.
   */
  public function connect($host, $database, $username, $password) {
    foreach (array('host', 'database', 'username', 'password') as $arg)
      if (!is_string($$arg))
        throw InvalidArgumentException::typeMismatch($arg, $$arg, 'String');

    $connection = new mysqli($host, $username, $password, $database);

    // Handle connection errors.
    if ($connection->connect_error)
      throw new RuntimeException(sprintf(
        'Error while connecting to MySql Server (%d): %s',
        $connection->connect_errno,
        $connection->connect_error
      ));

    // Connection successful.
    $this->connection = $connection;

    return $this;
  }

  /**
   * Retrieve Driver name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Driver name.
   */
  public function getName() {
    return join('', array_slice(explode('\\', get_class()), -1));
  }

  /**
   * Execute a query.
   *
   * @since 1.1.0 Added parameter $object
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Query\Query Query that will be executed.
   * @param string|bool $object Optional. If set and string, fetches results
   * as the specified object. If true, fetches results as StdClass objects.
   * If false fetches results as arrays. Default false.
   * @return mixed Query result.
   *
   * @throws Freyja\Exceptions\RuntimeException if query have some errors.
   */
  public function execute(Query $query, $object = false) {
    $query_str = (string) $query;

    if (method_exists($query, 'getDelimiter')) {
      $delimiter = preg_quote($query->getDelimiter(), '/');
      // Escape values between delimiters.
      $query_str = preg_replace_callback("/$delimiter(.*?)$delimiter/", array($this, 'escapeString'), $query_str);
    }

    $result = $this->connection->query($query_str);

    // Handle query errors.
    if (!$result)
      throw new RuntimeException(sprintf(
        'Query failed. Error (%d): %s',
        $this->connection->errno,
        $this->connection->error
      ));

    // Query successful.
    $results = array();
    if (!is_bool($result)) {
      if (!$object) {
        while ($row = $result->fetch_assoc())
          $results[] = $row;
      } else if ($object === true) {
        while ($row = $result->fetch_object())
          $results[] = $row;
      } else {
        while ($row = $result->fetch_object($object))
          $results[] = $row;
      }
    } else {
      $results = $result;
    }
    return $results;
  }

  /**
   * Escape a string.
   *
   * This method is used by `Freyja\Database\Driver\MySqlDriver::execute()`.
   * It isn't meant to be called alone.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Escaped string.
   */
  public function escapeString($string) {
    if (isset($string[1]))
      return $this->connection->real_escape_string($string[1]);
  }
}
