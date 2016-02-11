<?php
/**
 * Driver interface file.
 *
 * @package Freyja\Database\Driver
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database\Driver;

/**
 * Driver interface.
 *
 * @package Freyja\Database\Driver
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
interface Driver {
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
  public function connect($host, $database, $username, $password);

  /**
   * Retrieve Driver name.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string Driver name.
   */
  public function getName();

  /**
   * Execute a query.
   *
   * @since 1.0.0
   * @access public
   *
   * @param Freyja\Database\Query\Query Query that will be executed.
   * @return mixed Query result.
   *
   * @throws Freyja\Exceptions\RuntimeException if query have some errors.
   */
  public function execute(Query $query);
}
