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
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::build
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
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::build
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

  /**
   * Test for `MySqlQuery::count()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::count
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testCountWithEmptyFields() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->count()->build();
    $expected_str = 'SELECT COUNT(*) FROM table';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a count query.'
    );
  }

  /**
   * Test for `MySqlQuery::count()`
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::count
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testCount() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->count(array('field', 'another_field', 56))->build();
    $expected_str = 'SELECT COUNT(field, another_field) FROM table';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a count query.'
    );
  }

  /**
   * Test for `MySqlQuery::count()` and `MySqlQuery::select()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::count
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testSelectWithCount() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select('field', 'other_field')->count(array('another_field', 56))->build();
    $expected_str = 'SELECT field, other_field, COUNT(another_field) FROM table';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query with count.'
    );
  }

  /**
   * Test for `MySqlQuery::where()` and `MySqlQuery::orWhere()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function Freyja\Database\Query\MySqlQuery::orWhere
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testWhere() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select('field')->where('some_field', 'ciaone')->orWhere(array(
      array('some_other_field', '>', 56),
      array('some_beautiful_field', 'between', array(null, 65))
    ))->build();
    $expected_str = 'SELECT field FROM table WHERE some_field = \'{esc}ciaone{esc}\' OR some_other_field > 56 OR some_beautiful_field BETWEEN NULL AND 65';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a query with a where clause.'
    );
  }

  /**
   * Test for `MySqlQuery::whereIn()` and `MySqlQuery::whereNotIn()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::whereIn
   * @requires function Freyja\Database\Query\MySqlQuery::whereNotIn
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testWhereIn() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('field'))->whereIn('field', array(1, 2, 3))->whereNotIn('field', array(4, 5, 6))->build();
    $expected_str = 'SELECT field FROM table WHERE field IN(1, 2, 3) AND field NOT IN(4, 5, 6)';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a query with a where clause.'
    );
  }

  /**
   * Test for `MySqlQuery::update()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::update
   * @requires function Freyja\Database\Query\MySqlQuery::orderBy
   * @requires function Freyja\Database\Query\MySqlQuery::limit
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testUpdate() {
    $query = new MySqlQuery;
    $query_str = $query->update(array(
      'field' => 56,
      'other_field' => 'ciaone',
      'another_field' => null
    ))->table('table')->orderBy('field', 'desc')->limit(15)->build();
    $expected_str = 'UPDATE table SET field = 56, other_field = \'{esc}ciaone{esc}\', another_field = NULL ORDER BY field DESC LIMIT 15';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build an update query.'
    );
  }

  /**
   * Test for `MySqlQuery::first()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::update
   * @requires function Freyja\Database\Query\MySqlQuery::first
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testFirst() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->update(array(
      'field' => 56,
      'other_field' => 'ciaone',
      'another_field' => null
    ))->first()->build();
    $expected_str = 'UPDATE table SET field = 56, other_field = \'{esc}ciaone{esc}\', another_field = NULL LIMIT 0, 1';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build an update query with a limit(0,1).'
    );
  }

  /**
   * Test for `MySqlQuery::insert()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::insert
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testInsert() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->insert(array(
      'field' => 'ciaone',
      'another_field' => 56
    ))->build();
    $expected_str = 'INSERT INTO table (field, another_field) VALUES (\'{esc}ciaone{esc}\', 56)';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build an insert query.'
    );
  }

  /**
   * Test for `MySqlQuery::delete()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::delete
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testDelete() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->delete()->where('field', 'like', 5.6)->build();
    $expected_str = 'DELETE FROM table WHERE field LIKE 5.6';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a delete query.'
    );
  }

  /**
   * Test for `MySqlQuery::join()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::join
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testJoin() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('*'))->join(
      'other_table',
      'table.id_field',
      '=',
      'other_table.id_field'
    )->build();
    $expected_str = 'SELECT * FROM table INNER JOIN other_table ON table.id_field = other_table.id_field';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query with a join.'
    );
  }

  /**
   * Test for `MySqlQuery::leftJoin()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::join
   * @requires function Freyja\Database\Query\MySqlQuery::leftJoin
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testLeftJoin() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('*'))->join(
      'other_table',
      'table.id_field',
      '=',
      'other_table.id_field'
    )->leftJoin(
      'another_table',
      'table.id_field',
      '=',
      'another_table.id_field'
    )->build();
    $expected_str = 'SELECT * FROM table INNER JOIN other_table ON table.id_field = other_table.id_field LEFT JOIN another_table ON table.id_field = another_table.id_field';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query with a join and a left join.'
    );
  }

  /**
   * Test for `MySqlQuery::rightJoin()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::rightJoin
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testRightJoin() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('*'))->rightJoin(
      'other_table',
      'table.id_field',
      '=',
      'other_table.id_field'
    )->build();
    $expected_str = 'SELECT * FROM table RIGHT JOIN other_table ON table.id_field = other_table.id_field';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query with a right join.'
    );
  }

  /**
   * Test for `MySqlQuery::fullOuterJoin()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::fullOuterJoin
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testFullOuterJoin() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('*'))->fullOuterJoin(
      'other_table',
      'table.id_field',
      '=',
      'other_table.id_field'
    )->build();
    $expected_str = 'SELECT * FROM table FULL OUTER JOIN other_table ON table.id_field = other_table.id_field';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a select query with a full outer join.'
    );
  }

  /**
   * Test for `MySqlQuery::groupBy()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::groupBy
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testGroupBy() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('*'))->groupBy('field')->build();
    $expected_str = 'SELECT * FROM table GROUP BY field';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a query with group by condition.'
    );
  }

  /**
   * Test for `MySqlQuery::having()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::groupBy
   * @requires function Freyja\Database\Query\MySqlQuery::having
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testHaving() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('*'))->groupBy('field')->having('other_field', 'like', 'ciaone')->build();
    $expected_str = 'SELECT * FROM table GROUP BY field HAVING other_field LIKE \'{esc}ciaone{esc}\'';

    $this->assertEquals(
      $query_str,
      $expected_str,
      'Failed asserting that MySqlQuery correctly build a query with group by and having condition.'
    );
  }

  /**
   * Test for `MySqlQuery::where()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Too much arguments passed to `MySqlQuery::where()`
   */
  public function testWhereWithMoreStringsThanAllowed() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select('field')->where('some_field', '=', 'ciaone', 'wrong_argument')->orWhere(array(
      array('some_other_field', '>', 56),
      array('some_beautiful_field', 'between', array(null, 65))
    ))->build();
  }

  /**
   * Test for `MySqlQuery::where()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Some arguments passed to `MySqlQuery::where()` aren't in the correct form
   */
  public function testWhereWithInvalidArgumentType() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select('field')->where('some_field', array(0, 56), 'ciaone')->orWhere(array(
      array('some_other_field', '>', 56),
      array('some_beautiful_field', 'between', array(null, 65))
    ))->build();
  }

  /**
   * Test for `MySqlQuery::where()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Some arguments passed to `MySqlQuery::where()` aren't in the correct form
   */
  public function testWhereWithInvalidArgumentTypeForBetweenOperator() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select('field')->where('some_field', 'between', 'ciaone')->orWhere(array(
      array('some_other_field', '>', 56),
      array('some_beautiful_field', 'between', array(null, 65))
    ))->build();
  }
}
