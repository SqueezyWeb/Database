<?php
/**
 * Query interface file.
 *
 * @package Freyja\Database
 * @copyright 2016 SqueezyWeb
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 */

namespace Freyja\Database;

/**
 * Query interface.
 *
 * @package Freyja\Database
 * @author Gianluca Merlo <gianluca@squeezyweb.com>
 * @since 0.1.0
 * @version 1.0.0
 */
interface Query {
  /**
   * Set target table.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $name Table name.
   * @return self
   */
  public function table($name);

  /**
   * Set SELECT fields.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array|string $field Field name.
   * @return self
   */
  public function select($fields);

  /**
   * Set UPDATE fields and values.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $values Associative array `key => value`, where `key` is the
   * name of the field and `value` is the new value.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if field names in
   * $values aren't strings.
   * @throws \RuntimeException if values in $values aren't scalars.
   */
  public function update(array $values);

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
  public function count(array $fields = array());

  /**
   * Set INSERT fields and values.
   *
   * @since 1.0.0
   * @access public
   *
   * @param array $values Associative array `key => value`, where `key` is the
   * field and `value` is the new value.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if field names in
   * $values aren't strings.
   * @throws \RuntimeException if values in $values aren't scalars.
   */
  public function insert(array $values);

  /**
   * Set a DELETE query.
   *
   * If clause is set through the method `Query::where()`, all rows of the table
   * will be affected.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $modifier Optional. DELETE Modifier. Allowed keywords: '',
   * 'LOW_PRIORITY', 'QUICK', 'IGNORE' (either uppercase or lowercase).
   * Default: ''.
   * @return self
   *
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $modifier isn't a
   * string.
   * @throws \RuntimeException if $modifier isn't one of the allowed value.
   */
  public function delete($modifier = '');

  /**
   * JOIN tables.
   *
   * JOIN two tables. Second table of the JOIN will be the first argument of
   * this method. The first one will be the one set with the `Query::table()`
   * method.
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
  public function join($table, $one, $operator, $two, $type = 'INNER');

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
  public function leftJoin($table, $one, $operator, $two);

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
  public function rightJoin($table, $one, $operator, $two);

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
  public function fullOuterJoin($table, $one, $operator, $two);

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
   * @throws \RuntimeException if $direction isn't 'ASC' or 'DESC';
   */
  public function orderBy($field, $direction = 'ASC');

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
  public function groupBy($field);

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
   * @throws \RuntimeException if $operator isn't one of the allowed ones, or if
   * $value isn't in the correct form.
   */
  public function having($field, $operator, $value);

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
  public function limit($limit, $offset = null);

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
  public function first();

  /**
   * Set WHERE clauses.
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
  public function where($clauses);

  /**
   * Set WHERE clauses.
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
  public function orWhere($clauses);

  /**
   * Set WHERE clauses.
   *
   * Set clauses like: 'WHERE `field` IN (`value` AND `value` AND `value` ...)'.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param array $values Values to check with.
   * @return self
   *
   * @throws \RuntimeException if $value elements aren't in the correct form.
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function whereIn($field, array $values);

  /**
   * Set WHERE clauses.
   *
   * Set clauses like: 'WHERE `field` NOT IN (`value` AND `value` ...)'.
   *
   * @since 1.0.0
   * @access public
   *
   * @param string $field Field name.
   * @param array $values Values to check with.
   * @return self
   *
   * @throws \RuntimeException if $value elements aren't in the correct form.
   * @throws SqueezyWeb\Exceptions\InvalidArgumentException if $field isn't a
   * string.
   */
  public function whereNotIn($field, array $values);

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
}
