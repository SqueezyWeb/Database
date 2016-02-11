<?php
/**
 * Query class file.
 *
 * @package Freyja\Database\Query
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database\Query;

/**
 * Query class.
 *
 * @package Freyja\Database\Query
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
abstract class Query implements QueryInterface {
  /**
   * Query result.
   *
   * @since 1.0.0
   * @access private
   * @var mixed
   */
  private $result;

  /**
   * Check query result existence.
   *
   * @since 1.0.0
   * @access public
   *
   * @return boolean Whether the result is set or not.
   */
  public function hasResult() {
    return !is_null($this->result);
  }

  /**
   * Set query result.
   *
   * @since 1.0.0
   * @access public
   *
   * @param mixed $result Query result.
   */
  public function setResult($result) {
    $this->result = $result;
  }

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
  public function getResult() {
    return $this->result;
  }

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
