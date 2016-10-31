<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no later versions)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  The right to apply the terms of later versions of the GPL is RESERVED.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare(strict_types = 1);
namespace at\util;

use Ds\Map,
    Ds\Set;

/**
 * wraps Ds\Map with type-checking for values (and optionally for keys).
 *
 * type constraints are provided as strings, and will be interpreted as (in order):
 *  - a php datatype (except 'object'; use a classname)
 *  - a pseudotype (e.g., callable, iterable)
 *  - a fully qualified classname (invokes any autoloaders)
 *
 * if a given type cannot be interpreted, the constructor will fail.
 *
 * the TypedMap exposes the same api as a Ds\Map,
 * except that methods which set or modify keys or values also perform a type check,
 * and throw an UnexpectedValueException if the type constraints are not met.
 * for reference (checked methods are marked with a ✓):
 *
 * @method array      Map::__debugInfo(void)
 * @method void       Map::clear(void)
 * @method Map        Map::copy(void)
 * @method int        Map::count(void)
 * @method Map        Map::diff(Map $map)
 * @method Map        Map::filter(callable $filter)
 * @method Pair       Map::first(void)
 * @method mixed      Map::get(mixed $key, mixed $default)
 * @method mixed      Map::getIterator(void)
 * @method Set        Map::keys(void)
 * @method bool       Map::hasKey(mixed ...$keys)
 * @method bool       Map::hasValues(mixed ...$values)
 * @method Map        Map::intersect(Map $map)
 * @method Pair       Map::last(void)
 * @method Map      ✓ Map::map(callable $callback)
 * @method Map      ✓ Map::merge(iterable $values)
 * @method bool       Map::offsetExists(int $offset)
 * @method &mixed     Map::offsetGet(int $offset)
 * @method void     ✓ Map::offsetSet(int $offset, mixed $value)
 * @method void       Map::offsetUnset(int $offset)
 * @method Sequence   Map::pairs(void)
 * @method void     ✓ Map::put(mixed $key, mixed $value)
 * @method void     ✓ Map::putAll(iterable $values)
 * @method mixed      Map::reduce(callable $callback, mixed $initial)
 * @method mixed      Map::remove(int $index)
 * @method void       Map::removeAll(iterable $keys)
 * @method Map        Map::reverse(void)
 * @method Pair       Map::skip(int $position)
 * @method Map        Map::slice(int $offset, int $length)
 * @method Map        Map::sort(callable $comparator)
 * @method array      Map::toArray(void)
 * @method Sequence   Map::values(void)
 * @method Map      ✓ Map::xor(Map $map)
 */
class TypedMap {

  /**
   * @type Ds\Map  the underlying map. */
  protected $_map;

  /**
   * @type string $_keyType    the typed map's key type.
   * @type string $_valueType  the typed map's value type.
   */
  protected $_keyType = null;
  protected $_valueType;

  /**
   * @type string[]  list of type constraints recognized by TypedMap (excepting FQCNs). */
  protected $_normalizedTypes = [
    'callable',
    'boolean',
    'integer',
    'iterable',
    'double',
    'string',
    'array',
    'resource',
    'null'
  ];

  /**
   * @type string[] $_guarded    list of methods which do not modify values / need no checks.
   * @type string[] $_unguarded  list of methods which can mutate/assign values / need checks.
   */
  protected $_guarded = [
    'map',
    'merge',
    'offsetSet',
    'put',
    'putAll',
    'xor'
  ];
  protected $_unguarded = [
    '__debugInfo',
    'clear',
    'copy',
    'count',
    'diff',
    'filter',
    'first',
    'get',
    'getIterator',
    'keys',
    'hasKey',
    'hasValues',
    'intersect',
    'last',
    'offsetExists',
    'offsetGet',
    'offsetUnset',
    'pairs',
    'reduce',
    'remove',
    'removeAll',
    'reverse',
    'skip',
    'slice',
    'sort',
    'toArray',
    'values',
  ];

  /**
   * proxies calls to the contained map, enforcing instance type constraints.
   * @see <http://php.net/__call>
   */
  public function __call($method, $arguments) {
    if (in_array($method, $this->_guarded)) {
      $clone = clone $this->_map;
      $return = $clone->$method(...$arguments);

      $this->_assertTypes($clone->values(), $this->_valueType);
      if ($this->_keyType) {
        $this->_assertTypes($clone->keys(), $this->_keyType);
      }

      $this->_map = $clone;
      return $return;
    }

    if (in_array($method, $this->_unguarded)) {
      return $this->$method(...$arguments);
    }

    $c = static::class;
    $m = "Call to undefined method {$c}::{$method}()";
    throw new \Error($m, E_ERROR);
  }

  /**
   * @param string $valueType  type constraint for values
   * @param string $keyType    optional type constraint for keys
   */
  public function __construct(string $valueType, string $keyType=null) {
    $this->_valueType = $this->_assertTypeConstraint($valueType);
    if ($keyType !== null) {
      $this->_keyType = $this->_assertTypeConstraint($keyType);
    }
    $this->_map = new Map;
  }

  /**
   * ensures that all values in the given set meet the type constraint.
   *
   * @param Set    $set                the set of values to check
   * @param string $type               the required type
   * @throws UnexpectedValueException  if a type check fails
   */
  protected function _assertTypes(Set $set, string $type) {
    foreach ($set as $item) {
      $valid = ($type === 'callable' && is_callable($item))
        || ($type === 'iterable' && ($item instanceof \Traversable || is_array($item)))
        || (strtolower(gettype($item)) === $type)
        || ($item instanceof $type);
      if (! $valid) {
        $t = Vars::type($item);
        $m = "type must be {$type}; [{$t}] provided";
        throw new \UnexpectedValueException($m, E_WARNING);
      }
    }
  }

  /**
   * validates and normalizes a given type constraint.
   *
   * @param string $type  the type constraint to validate
   * @return string|null  the normalized type on success; null otherwise
   */
  protected function _assertTypeConstraint(string $type) {
    $type = strtolower(
      strtr($type, ['bool' => 'boolean', 'int' => 'integer', 'float' => 'double'])
    );
    if (class_exists($type, true) || in_array($type, $this->_normalizedTypes)) {
      return $type;
    }
    $m = "a valid type constraint is required; '{$type}' provided";
    throw new \InvalidArgumentException($m, E_WARNING);
  }
}
