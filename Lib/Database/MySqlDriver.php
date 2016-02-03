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

use Freyja\Exceptions\InvalidArgumentException as InvArgExcp;
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
   * Driver name.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $name;

  /**
   * Connection object.
   *
   * @since 1.0.0
   * @access private
   * @var \mysqli
   */
  private $connection;

  /**
   * Class constructor.
   *
   * Set the Driver name.
   *
   * @since 1.0.0
   * @access public
   */
  public function __construct() {
    $this->name = 'MySql';
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
   *
   * @throws Freyja\Exceptions\InvalidArgumentException if one of the arguments
   * isn't a string.
   */
  public function connect($host, $database, $username, $password) {
    foreach (array('host', 'database', 'username', 'password') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');

    $this->connection = new mysqli($host, $username, $password, $database);
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
    return $this->name;
  }

  /**
   * Execute a query.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Query Query that will be executed.
   * @return mixed Query result.
   */
  public function execute(Query $query) {
    return $this->connection->query($query);
  }
}
