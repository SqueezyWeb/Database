<?php
/**
 * MySqlQueryTest class file.
 *
 * @package Freyja\Database\Tests
 * @copyright 2016 SqueezyWeb
 * @since 0.1.0
 */

namespace Freyja\Database\Tests\Query;

use Freyja\Database\Query\MySqlQuery;
use \ReflectionProperty;

/**
 * MySqlQueryTest class.
 *
 * @package Freyja\Database\Tests
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.2.0
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
   * Test for `MySqlQuery::distinct()`.
   *
   * @since 1.2.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::distinct
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testDistinct() {
    $query = new MySqlQuery;
    $query->table('table')->select(array('field1', 'field2'))->distinct();
    $query_str = $query->build();
    $expected_str = 'SELECT DISTINCT field1, field2 FROM table';

    $this->assertEquals(
      $expected_str,
      $query_str,
      'Failed asserting that MySqlQuery correctly build a `SELECT DISTINCT` query.'
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
    $query_str = $query->table('table')->insert(
      array('field', 'another_field'),
      array(array('ciaone', 56))
    )->build();
    $expected_str = 'INSERT INTO table (field, another_field) VALUES (\'{esc}ciaone{esc}\', 56)';

    $this->assertEquals(
      $expected_str,
      $query_str,
      'Failed asserting that MySqlQuery correctly build an insert query.'
    );
  }

  /**
   * Test for `MySqlQuery::insert()`.
   *
   * @since 1.1.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::insert
   * @requires function Freyja\Database\Query\MySqlQuery::build
   */
  public function testInsertWithMultipleRows() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->insert(
      array('field', 'another_field'),
      array(array('ciaone', 56), array('another_ciaone', 'another_56'), array('booh', 0))
    )->build();
    $expected_str = 'INSERT INTO table (field, another_field) VALUES (\'{esc}ciaone{esc}\', 56), (\'{esc}another_ciaone{esc}\', \'{esc}another_56{esc}\'), (\'{esc}booh{esc}\', 0)';

    $this->assertEquals(
      $expected_str,
      $query_str,
      'Failed asserting that MySqlQuery correctly build an insert query with multiple rows.'
    );
  }

  /**
   * Test for `MySqlQuery::insert()`.
   *
   * @since 1.1.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::insert
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument fields (one of its elements). String expected, array given instead.
   */
  public function testInsertWithInvalidFields() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->insert(
      array(array(), 'another_field'),
      array(array('ciaone', 56), array('another_ciaone', 'another_56'), array('booh', 0))
    )->build();
  }

  /**
   * Test for `MySqlQuery::insert()`.
   *
   * @since 1.1.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::insert
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument values (one of its elements). Array expected, string given instead.
   */
  public function testInsertWithInvalidValues() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->insert(
      array('field', 'another_field'),
      array('ciaone', array('another_ciaone', 'another_56'), array('booh', 0))
    )->build();
  }

  /**
   * Test for `MySqlQuery::insert()`.
   *
   * @since 1.1.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::insert
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Every internal array of arguments second argument must be equal to first argument length
   */
  public function testInsertWithDifferentNumberOfFieldsAndValues() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->insert(
      array('field', 'another_field'),
      array(array('ciaone', 56, 56), array('another_ciaone', 'another_56'), array('booh', 0))
    )->build();
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
   * @expectedExceptionMessage Invalid data passed to `MySqlQuery::where()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed
   */
  public function testWhereWithMoreStringsThanAllowed() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('field'))->where(
      'some_field',
      '=',
      'ciaone',
      'wrong_argument'
    )->orWhere(array(
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
   * @expectedExceptionMessage Operator BETWEEN requires a range specification as an array with two elements, no array was given
   */
  public function testWhereWithInvalidArgumentTypeForBetweenOperator() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('field'))->where('some_field', 'between', 'ciaone')->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Any operator except BETWEEN accept only a single value in method `MySqlQuery::where()`
   */
  public function testWherePassingArrayValueButNoBetweenOperator() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('field'))->where('some_field', '>', array('ciaone', 56))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Invalid data passed to `MySqlQuery::where()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed
   */
  public function testWherePassingOneStringOnly() {
    $query = new MySqlQuery;
    $query_str = $query->table('table')->select(array('field'))->where('some_field')->build();
  }

  /**
   * Test for `MySqlQuery::where`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::table
   * @requires function Freyja\Database\Query\MySqlQuery::select
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function Freyja\Database\Query\MySqlQuery::build
   *
   * @dataProvider whereClausesProvider
   */
  public function testWhereWithExceptionForInvalidData($field, $operator, $value, $exception, $exception_message) {
    $this->setExpectedException($exception, $exception_message);

    $query = new MySqlQuery;
    $query->table('table')->select('*')->where($field, $operator, $value)->build();
  }

  /**
   * Data provider for testing the method `MySqlQuery::where()`.
   *
   * @since 1.0.0
   * @access public
   */
  public function whereClausesProvider() {
    $exception = 'Freyja\Exceptions\InvalidArgumentException';
    $exception_message = 'Some elements of some clauses passed to `MySqlQuery::where()` are invalid';
    return array(
      'array as field' => array(array('field'), '=', 56, $exception, $exception_message),
      'array as operator' => array('field', array('='), 56, $exception, $exception_message),
      'object as field' => array(new MySqlQuery, '=', 56, $exception, $exception_message),
      'object as operator' => array('field', new MySqlQuery, 56, $exception, $exception_message),
      'object as value' => array('field', '=', new MySqlQuery, $exception, $exception_message)
    );
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Any operator except BETWEEN accept only a single value in method `MySqlQuery::where()`
   */
  public function testWherePassingArrayWithArrayValueButNoBetweenOperator() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array(
      array('field', '>', array(0, 56))
    ))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Invalid data passed to `MySqlQuery::where()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed
   */
  public function testWherePassingArrayWithOnlyOneString() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array(
      array('field')
    ))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Invalid data passed to `MySqlQuery::where()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed
   */
  public function testWherePassingArrayWithMoreStringsThanAllowed() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array(
      array('field', '=', 'ciaone', 'exceeding string')
    ))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Operator BETWEEN requires a range specification as an array with two elements, no array was given
   */
  public function testWherePassingBetweenOperatorButNoArrayValue() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array(
      array('field', 'between', 'ciaone')
    ))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Invalid data passed to `MySqlQuery::where()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed
   */
  public function testWherePassingNotWellEncapsulatedClause() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array('field', '=', 'ciaone'))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Some elements of some clauses passed to `MySqlQuery::where()` are invalid
   */
  public function testWherePassingMultipleNotWellEncapsulatedClauses() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array('field', 'ciaone'), array('field', '>', 56))->build();
  }

  /**
   * Test for `MySqlQuery::where`.
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
   * @expectedExceptionMessage Invalid data passed to `MySqlQuery::where()`: every clause must be an array with a minimum of two elements and a maximum of three, or direclty two or three scalars if only one clause is passed
   */
  public function testWherePassingArrayAndStrings() {
    $query = new MySqlQuery;
    $query->table('table')->select('*')->where(array('field', 'ciaone'), 'field', '=', 56)->build();
  }

  /**
   * Test for `MySqlQuery::whereRaw()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::whereRaw
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testWhereRaw() {
    // Set accessibility to object property.
    $reflection_where = new ReflectionProperty('Freyja\Database\Query\MySqlQuery', 'where');
    $reflection_where->setAccessible(true);

    $query = new MySqlQuery;
    $query->whereRaw('WHERE field = 56 AND other_field = NULL');

    $this->assertEquals(
      'WHERE field = 56 AND other_field = NULL',
      $reflection_where->getValue($query),
      'Failed asserting that MySqlQuery::whereRaw() correctly set a where clause.'
    );
  }

  /**
   * Test for `MySqlQuery::whereRaw()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::whereRaw
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testWhereRawWithChainedWhere() {
    // Set accessibility to object property.
    $reflection_where = new ReflectionProperty('Freyja\Database\Query\MySqlQuery', 'where');
    $reflection_where->setAccessible(true);

    $query = new MySqlQuery;
    $query->whereRaw('WHERE field = 56')->where('other_field', null);

    $this->assertEquals(
      'WHERE field = 56 AND other_field = NULL',
      $reflection_where->getValue($query),
      'Failed asserting that MySqlQuery::whereRaw() correctly set a where clause, if chained by MySqlQuery::where().'
    );
  }

  /**
   * Test for `MySqlQuery::whereRaw()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::whereRaw
   * @requires function Freyja\Database\Query\MySqlQuery::where
   * @requires function ReflectionProperty::setAccessible
   * @requires function ReflectionProperty::getValue
   */
  public function testWhereRawChainedAfterWhere() {
    // Set accessibility to object property.
    $reflection_where = new ReflectionProperty('Freyja\Database\Query\MySqlQuery', 'where');
    $reflection_where->setAccessible(true);

    $query = new MySqlQuery;
    $query->where('other_field', null)->whereRaw('WHERE field = 56');

    $this->assertEquals(
      'WHERE field = 56',
      $reflection_where->getValue($query),
      'Failed asserting that MySqlQuery::whereRaw() correctly set a where clause, if chained after MySqlQuery::where().'
    );
  }

  /**
   * Test for `MySqlQuery::whereRaw()`.
   *
   * @since 1.0.0
   * @access public
   *
   * @requires function Freyja\Database\Query\MySqlQuery::whereRaw
   *
   * @expectedException Freyja\Exceptions\InvalidArgumentException
   * @expectedExceptionMessage Wrong type for argument where. String expected, array given instead.
   */
  public function testWhereRawWithInvalidArgument() {
    $query = new MySqlQuery;
    $query->whereRaw(array());
  }
}
