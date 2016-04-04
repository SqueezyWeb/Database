<?php
/**
 * Customer fixture class.
 *
 * @package Freyja\Database\Tests\Fixtures
 * @copyright 2016 SqueezyWeb
 * @since 0.3.0
 */

namespace Freyja\Database\Tests;

/**
 * Customer fixture class.
 *
 * @package Freyja\Database\Tests\Fixtures
 * @author Mattia Migliorini <mattia@squeezyweb.com>
 * @since 0.3.0
 * @version 1.0.0
 */
class Customer {
  /**
   * Constructor.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $id
   * @param string $name
   * @param string $surname
   * @param string $email
   */
  public function __construct($id = null, $name = null, $surname = null, $email = null) {
    if (!is_null($id))
      $this->customer_id = $id;
    if (!is_null($name))
      $this->name = $name;
    if (!is_null($surname))
      $this->surname = $surname;
    if (!is_null($email))
      $this->email = $email;
  }
}
