<?php
/**
 * Query class file.
 *
 * @package Freyja\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database;

/**
 * Query class.
 *
 * @package Freyja\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
abstract class Query implements QueryInterface {
  /**
   * Check query result existence.
   *
   * @since 1.0.0
   * @access public
   * @abstract
   *
   * @return boolean Whether the result is set or not.
   */
  abstract public function hasResult();

  /**
   * Set query result.
   *
   * @since 1.0.0
   * @access public
   * @abstract
   *
   * @param mixed $result Query result.
   */
  abstract public function setResult($result);

  /**
   * Retrieve query result.
   *
   * Retrive result(s) of the query. If result isn't set, null will be returned.
   *
   * @since 1.0.0
   * @access public
   * @abstract
   *
   * @return mixed Query result.
   */
  abstract public function getResult();

  /**
   * Build the query string.
   *
   * @since 1.0.0
   * @access public
   * @abstract
   *
   * @return string
   */
  abstract public function build();

  /**
   * Convert Query to string.
   *
   * @since 1.0.0
   * @access public
   * @final
   *
   * @return string
   */
  final public function __toString() {
    try {
      return $this->build();
    } catch (Exception $e) {
      // TODO: decide how to handle this situation.
    }
  }
}
