<?php
/**
 * MySqlDriver class file.
 *
 * @package Freyja\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database;

use Freyja\Exceptions\InvalidArgumentException;
use Freyja\Exceptions\RuntimeException;
use Freyja\Database\Query;
use mysqli;

/**
 * MySqlDriver class.
 *
 * @package Freyja\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
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
        'Error while connecting to MySql Server ($d): %s',
        $connection->connect_errno,
        $connection->connect_error
      ));

    // Connection successful.
    $this->connection = $connection;
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
    return get_class();
  }

  /**
   * Execute a query.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Query Query that will be executed.
   * @return mixed Query result.
   *
   * @throws Freyja\Exceptions\RuntimeException if query have some errors.
   */
  public function execute(Query $query) {
    $result = $this->connection->query($query);

    // Handle query errors.
    if (!$result)
      throw new RuntimeException(sprintf(
        'Query failed. Error (%d): %s',
        $this->connection->errno,
        $this->connection->error
      ));

    // Query successful.
    return $result;
  }
}
