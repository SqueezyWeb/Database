<?php
/**
 * Query class file.
 *
 * @package SqueezyWeb\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 1.0.0
 */

namespace SqueezyWeb\Database;

use SqueezyWeb\Exceptions\InvalidArgumentException as InvArgExcp;
use \RuntimeException;

/**
 * Query class.
 *
 * @package SqueezyWeb\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 1.0.0
 * @version 1.0.0
 */
class Query {
  /**
   * Query type.
   *
   * Accepted values: 'select', 'create', 'drop', 'update', 'insert',
   * 'table_exists'.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $type;

  /**
   * Target table.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $table;

  /**
   * SELECT fields.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $select = array();

  /**
   * COUNT.
   *
   * True correspond to `COUNT(*)`. Or array containing the fields to be
   * counted. Default false.
   *
   * @since 1.0.0
   * @access private
   * @var boolean|array
   */
  private $count = false;

  /**
   * ORDERBY.
   *
   * Associative array `key => value`, where `key` is the field in which to sort
   * the results and `value` is the direction.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $order_by = array();

  /**
   * GROUPBY.
   *
   * @since 1.0.0
   * @access private
   * @var string
   */
  private $group_by;

  /**
   * HAVING clause.
   *
   * Array of strings: field name, operator, value.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $having = array();

  /**
   * UPDATE fields and values.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $update = array();

  /**
   * INSERT fields and values.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $insert = array();

  /**
   * Table joins.
   *
   * Array of arrays, each of which contains the table to join with, the field
   * name of the first table, the operator, the field name of the second table,
   * and the type of the JOIN.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $joins = array();

  /**
   * Where clauses.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $where = array();

  /**
   * Where clauses (or).
   *
   * Where clauses concatenated by `OR`.
   *
   * @since 1.0.0
   * @access private
   * @var array
   */
  private $or_where = array();

  /**
   * LIMIT.
   *
   * @since 1.0.0
   * @access private
   * @var int
   */
  private $limit;

  /**
   * OFFSET.
   *
   * @since 1.0.0
   * @access private
   * @var int
   */
  private $offset;

  /**
   * Set target table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Table name.
   * @return self
   */
  public function table($name) {
    // Verify that $name is a string.
    if (!is_string($name))
      throw InvArgExcp::typeMismatch('table name', $name, 'String');

    $this->table = $name;
    return $this;
  }

  /**
   * Set SELECT fields.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string $field Field name.
   * @return self
   */
  public function select($fields) {
    $fields = is_array($fields) ? $fields : func_get_args();
    $this->select = array_filter($fields, 'is_string');
    $this->type = 'select';

    return $this;
  }

  /**
   * Set UPDATE fields and values.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $values Associative array `key => value`, where `key` is the
   * name of the field and `value` is the new value.
   * @return self
   */
  public function update(array $values) {
    $this->update = $values;
    $this->type = 'update';

    return $this;
  }

  /**
   * Set COUNT.
   *
   * Set COUNT to all field or to some specific fields (passing an array
   * containing strings).
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $count Optional. List of fields to which apply the COUNT
   * selector. Default: empty array.
   * @return self
   */
  public function count(array $fields = array()) {
    if (empty($fields))
      $this->count = true;
    else
      $this->count = array_filter($fields, 'is_string');

    return $this;
  }

  /**
   * Set INSERT fields and values.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $values Associative array `key => value`, where `key` is the
   * field and `value` is the new value.
   * @return self
   */
  public function insert(array $values) {
    $this->insert = $values;
    $this->type = 'insert';

    return $this;
  }

  /**
   * JOIN tables.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @param string $type Optional. Join type (INNER, LEFT, RIGHT, FULL OUTER).
   * Default: 'inner'.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator or if $type
   * isn't a valid join type.
   */
  public function join($table, $one, $operator, $two, $type = 'inner') {
    // Checks on arguments.
    foreach (array('table', 'one', 'operator', 'two', 'type') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');
    if (!isOperatorValid($operator))
      throw new RuntimeException('Operator passed to `Query::join()` must be a valid operator');
    if (!in_array($type, array('inner', 'left', 'right', 'full outer')))
      throw new RuntimeException('Join type passed to `Query::join()` must be a valid join type');

    $this->joins[] = array($table, $one, $operator, $two, $type);
    return $this;
  }

  /**
   * Perform a LEFT JOIN.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator.
   */
  public function leftJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'left');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Perform a RIGHT JOIN.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator.
   */
  public function rightJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'right');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Perform a FULL OUTER JOIN.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $table Table name.
   * @param string $one Field of the first table.
   * @param string $operator Operator.
   * @param string $two Field of the second table.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments isn't a string.
   * @throws \RuntimeException if $operator isn't a valid operator.
   */
  public function fullOuterJoin($table, $one, $operator, $two) {
    try {
      return $this->join($table, $one, $operator, $two, 'full outer');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Check if table exists.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Optional. Table name. Default: the one set with the
   * `Query::table()` method.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $name isn't a
   * string or null.
   */
  public function hasTable($name = null) {
    if (!is_string($name) && !is_null($name))
      throw InvArgExcp::typeMismatch('table name', $name, 'String or null');

    if (is_string($name))
      $this->table = $name;
    $this->type = 'table_exists';

    return $this;
  }

  /**
   * Set order to results.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param string $direction Optional. Set the direction of the sort. Permitted
   * values: 'asc', 'desc'. Default: 'asc'.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field and
   * $direction aren't strings.
   * @throws \RuntimeException if $direction isn't 'asc' or 'desc';
   */
  public function orderBy($field, $direction = 'asc') {
    if (!is_string($field))
      throw InvArgExcp::typeMismatch('field name', $field, 'String');
    if (!is_string($direction))
      throw InvArgExcp::typeMismatch('direction', $direction, 'String');
    if ($direction != 'asc' && $direction != 'desc')
      throw new RuntimeException('$direction passed to `Query::orderBy()` must be \'asc\' or \'desc\'');

    $this->order_by[$field] = $direction;
    return $this;
  }

  /**
   * Perform a GROUPBY.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function groupBy($field) {
    if (!is_string($field))
      throw InvArgExcp::typeMismatch('field name', $field, 'String');

    $this->group_by = $field;
    return $this;
  }

  /**
   * Set HAVING clause.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param string $operator Operator.
   * @param mixed $value Value.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field and
   * $operator aren't strings.
   */
  public function having($field, $operator, $value) {
    foreach(array('field', 'operator') as $arg)
      if (!is_string($$arg))
        throw InvArgExcp::typeMismatch($arg, $$arg, 'String');

    $this->having = array($field, $operator, $value);
    return $this;
  }

  /**
   * Set a LIMIT.
   *
   * @since 1.0.0
   * @access public
   *
   * @param int $limit Number of maximum rows returned (LIMIT).
   * @param int $offset Optional. Specify the OFFSET. Default: null.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if one of the
   * arguments aren't numeric.
   */
  public function limit($limit, $offset = null) {
    if (!is_numeric($limit))
      throw InvArgExcp::typeMismatch('limit', $limit, 'Numeric');
    if (!is_numeric($offset) && !is_null($offset))
      throw InvArgExcp::typeMismatch('offset', $offset, 'Numeric or null');

    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }

  /**
   * Set the query to return only the first row.
   *
   * It is equivalent to call `Query::limit(1, 0)`. In other words, this method
   * will perform a `LIMIT 0, 1` in the query.
   *
   * @since 1.0.0
   * @access public
   *
   * @return self
   */
  public function first() {
    try {
      return $this->limit(1, 0);
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Set where clauses.
   *
   * Set where clauses, linking them with the `AND` operator.
   * Arguments can be passed in two forms. The simple one is useful for setting
   * one condition, and consists of passing an argument for the field, one for
   * the operator (can be omitted if '=') and one for the value.
   * The other one consists of passing an array containing an array for every
   * clause, each of which must contain two or three elements, in the same form
   * as the first one.
   * Every other way will raise an exception.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string Where clauses.
   * @return self
   *
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  public function where($clauses) {
    try {
      return $this->processWhere(func_get_args(), 'where');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Set where clauses.
   *
   * Set where clauses, linking them with the `OR` operator.
   * The method behave like `Query::where()`.
   * @see Query::where()
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string Where clauses.
   * @return self
   *
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  public function orWhere($clauses) {
    try {
      return $this->processWhere(func_get_args(), 'orWhere');
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   *
   */
  public function whereIn()

  /**
   *
   */
  public function whereNotIn()

  /**
   * Process where clauses.
   *
   * @since 1.0.0
   * @access private
   *
   * @param array $args Arguments of the public method.
   * @param string $called_by Name of the method that called this one.
   * @return self
   *
   * @throws \RuntimeException if the arguments aren't in the correct form.
   */
  private function processWhere(array $args, $called_by = 'where') {
    if (is_array($args[0])) {
      // Method was called with an array of clauses.
      foreach ($args[0] as $clause) {
        // Check if the array has other arrays or not, and if it has , then
        // check if every internal array has only scalar inside.
        if (!is_array($clause) || count($clause) < 2 || count($clause) > 3)
          throw new RuntimeException('Arguments passed to `Query::'.$called_by.'()` aren\'t in the correct form');
        foreach ($clause as $scalar) {
          if (!is_scalar($scalar))
            throw new RuntimeException('Arguments passed to `Query::'.$called_by.'()` aren\'t in the correct form');
        }
      }

      // The array is in the correct form.
      $this->buildWhereClause($args[0], $called_by == 'where' ? 'AND' : 'OR');
    } elseif (is_scalar($args[0])) {
      // Method was called with a list of arguments.
      // Check if the arguments are all scalar, and if they are two or three.
      if (count($args) < 2 || count($args) > 3)
        throw new RuntimeException('Arguments passed to `Query::'.$called_by.'()` aren\'t in the correct form');
      foreach ($args as $clause) {
        if (!is_scalar($clause))
          throw new RuntimeException('Arguments passed to `Query::'.$called_by.'()` aren\'t in the correct form');
      }

      // Arguments are in the correct form.
      $this->buildWhereClause(array($args), $called_by == 'where' ? 'AND' : 'OR');
    } else {
      // Method was called in an incorrect way.
      throw new RuntimeException('Arguments passed to `Query::'.$called_by.'()` aren\'t in the correct form');
    }

    return $this;
  }

  /**
   * Build WHERE clause.
   *
   * Transform a list of arguments in a string of WHERE conditions.
   *
   * @since 1.0.0
   * @access private
   *
   * @param array $clauses `WHERE` clauses.
   * @param string $operator `AND` or `OR`.
   */
  private function buildWhereClause($clauses, $operator) {
    // TODO: check the operator e build the string 'WHERE blabla AND blabla OR blabla'.
    // TODO: change the property $where and $or_where.
  }

  /**
   * Verify the operator.
   *
   * Checks that the operator passed is a valid one.
   *
   * @since 1.0.0
   * @access private
   *
   * @param string $operator Operator that needs to be verified.
   * @return boolean Whether the operator is valid or not.
   */
  private function isOperatorValid($operator) {
    $valid_operators = array('=', '>', '>=', '<', '<=', '!=', 'between', 'like');

    return in_array($operator, $valid_operators);
  }
}
