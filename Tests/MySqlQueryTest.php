<?php
/**
 * MySqlQueryTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests;

use Freyja\Database\Query\MySqlQuery;

/**
 * MySqlQueryTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
class MySqlQueryTest extends \PHPUnit_Framework_Testcase {
  /**
   * Test for `MySqlQuery::select()`.
   *
   * @since 1.0.0
   * @access public
   */
  public function testSelectWithEmptyFields() {
    $query = new MySqlQuery;
    $query->table('table')->select(array());
    $query_str = $query->build();
    $expected_str = 'SELECT * FROM table';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query.'
    );
  }

  /**
   * Test for `MySqlQuery::select()`.
   *
   * @since 1.0.0
   * @access public
   */
  public function testSelect() {
    $query = new MySqlQuery;
    $query->table('table')->select(array('field', 'another_field', 56));
    $query_str = $query->build();
    $expected_str = 'SELECT field, another_field FROM table';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query.'
    );
  }
}
