<?php
/**
 * QueryInterface interface file.
 *
 * @package Freyja\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database;

/**
 * QueryInterface interface.
 *
 * @package Freyja\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
interface QueryInterface {
  /**
   * Check query result existence.
   *
   * @since 1.0.0
   * @access public
   *
   * @return boolean Whether the result is set or not.
   */
  public function hasResult();

  /**
   * Set query result.
   *
   * @since 1.0.0
   * @access public
   *
   * @param mixed $result Query result.
   */
  public function setResult($result);

  /**
   * Retrieve query result.
   *
   * Retrive result(s) of the query. If result isn't set, null will be returned.
   *
   * @since 1.0.0
   * @access public
   *
   * @return mixed Query result.
   */
  public function getResult();

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   */
  public function build();

  /**
   * Convert Query to string.
   *
   * @since 1.0.0
   * @access public
   *
   * @return string
   */
  public function __toString();
}
