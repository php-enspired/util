<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (only)
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

use at\util\JSON,
    at\util\Vars;

/**
 * utility functions for arrays. */
class Arrays {

  /**
   * organizes array items into categories based on given key name.
   *
   * @param array $subject         the subject array
   * @param array $index           key of column to categorize by
   * @throws OutOfBoundsException  if key is not present in all rows
   * @return array                 the categorized array
   */
  public static function categorize(array $subject, $key) {
    Vars::typeHint($key, 'string', 'int');
    $categorized = [];
    foreach ($array as $row) {
      if (! isset($row[$key])) {
        throw new \OutOfBoundsException("\$key [{$key}] must be present in all rows");
      }
      $categorized[$key] = $row;
    }
    return $categorized;
  }

  /**
   * looks up value at given path (if it exists).
   *
   * @param array  $subject        the subject array
   * @param string $path           delimited list of keys to follow
   * @param array  $opts {
   *    @type string $delim  the delimiter to split the path on (defaults to '.')
   *    @type bool   $throw  throw if the path does not exist (defaults to false)?
   *  }
   * @throws OutOfBoundsException  if the path does not exist in the subject array
   * @return mixed                 the value at the given path if it exists; null otherwise
   */
  public static function dig(array $subject, string $path, array $opts=[]) {
    $opts = $opts + [
      'delim' => '.',
      'throw' => false
    ];

    foreach (explode($opts['delim'], $path) as $key) {
      if (! (is_array($subject) && isset($subject[$key]))) {
        if ($opts['throw']) {
          $m = "\$path [{$path}] does not exist in subject array";
          throw new \OutOfBoundsException($m, E_USER_NOTICE);
        }
        return null;
      }
      $subject = $subject[$key];
    }
    return $subject;
  }

  /**
   * like array_merge_recursive(), but only merges arrays
   * (no casting: arrays will never be merged with scalar values).
   *
   * @param array $subject  the subject array
   * @param array …$arrays  secondary array(s) to extend with
   * @return array          the extended array
   */
  public static function extend_recursive(array $subject, array ...$arrays): array {
    foreach ($arrays as $array) {
      foreach ($array as $key=>$value) {
        if (is_int($key)) {
          $subject[] = $value;
        } elseif (isset($subject[$key]) && is_array($subject[$key]) && is_array($value)) {
          $subject[$key] = static::extend_recursive($subject[$key], $value);
        } else {
          $subject[$key] = $value;
        }
      }
    }
    return $subject;
  }

  /**
   * filters variables based on a callback map.
   *
   * {@see http://php.net/filter_var_array} for details about building filter definitions.
   * in addition, allows "shorthand" filter definitions:
   *  - if $definition is callable, it will be applied to the entire array using FILTER_CALLBACK.
   *  - if a $definition value is NULL, that key will use FILTER_DEFAULT.
   *  - if a $definition value is callable, that key will use FILTER_CALLBACK.
   *  - if a $definition value is a Regex instance, that key will use FILTER_VALIDATE_REGEXP.
   *
   * callable filters use the following signature:
   *  filter_callback(mixed $value) : mixed
   *
   * like filter_var_array, values can be arrays, but filter definitions cannot be nested.
   * to validate an item with nested arrays,
   * pass a callback function to the base key which contains the validation logic.
   *
   * @param array $vars                the variables to filter
   * @param mixed $definition          filter definition
   * @param bool  $add_empty           add missing items (as NULL) to the returned array
   * @throws UnexpectedValueException  if a provided callback throws
   * @throws BadFunctionCallException  if filter definition is invalid
   * @return array                     the filtered variables
   */
  public static function filter(array $vars, $definition, $add_empty=true): array {
    try {
      if ($definition === null) {
        $definition = FILTER_DEFAULT;
      } elseif (is_callable($definition)) {
        $definition = array_fill_keys(array_keys($vars), $definition);
      }
      if (is_array($definition)) {
        foreach ($definition as &$i) {
          if ($i === null) {
            $i = FILTER_DEFAULT;
          } elseif ($i instanceof Regex) {
            $i = [
              "filter"  => FILTER_VALIDATE_REGEXP,
              "options" => ["regexp" => $i->__toString()]
            ];
          } elseif (is_callable($i)) {
            $i = [
              "filter"  => FILTER_CALLBACK,
              "options" => $i
            ];
          }
        }
      }

      $result = filter_var_array($vars, $definition, $add_empty);
    } catch (\Throwable $e) {
      $m = "uncaught exception thrown from filter: {$e->getMessage()}";
      throw new \RuntimeException($m, $e->getCode(), $e);
    }
    if (! is_array($result)) {
      $j = JSON::encode($definition);
      $m = "invalid filter definition: [$j]";
      throw new \BadFunctionCallException($m, E_USER_WARNING);
    }
    return $result;
  }

  /**
   * computes the intersection of arrays.
   *
   * keys are preserved.
   * values are compared strictly.
   *
   * @param array $subject  the subject array
   * @param array …$arrays  array(s) to compare against
   * @return array          an array containing subject values which are present in all arrays
   */
  public static function intersect(array $subject, array ...$arrays) : array {
    foreach ($subject as $k => $v) {
      foreach ($arrays as $array) {
        if (! in_array($v, $array, true)) {
          unset($subject[$k]);
          continue 2;
        }
      }
    }
    return $subject;
  }

  /**
   * determines whether an array is a list (has 0-based, sequential indexes).
   *
   * @param array $subject  the subject array
   * @return bool           true if subject is a list; false otherwise
   */
  public static function is_list(array $subject): bool {
    $i = 0;
    foreach ($subject as $k=>$v) {
      if ($k !== $i) {
        return false;
      }
      $i++;
    }
    return true;
  }

  /**
   * selects one or more items from an array at random and returns their key(s).
   *
   * this method uses a cryptographically secure random number generator.
   * use array_rand() unless you actually have need for cryptographic randomness.
   *
   * @param array $subject       the subject array
   * @param int   $number        the number of items to pick (must be a positive integer)
   * @throws UnderflowException  when trying to pick more items than are in the array
   * @return scalar|scalar[]     the selected key(s)
   */
  public static function random(array $subject, int $number=1) {
    if ($number < 1) {
      throw new \InvalidArgumentException('$number must be positive integer');
    }

    $count = count($subject);
    if ($number > $count) {
      throw new \UnderflowException("\$subject array contains fewer than {$number} items");
    }

    $keys = array_keys($subject);
    if ($number === $count) {
      return $keys;
    }

    $randoms = [];
    for ($i=0; $i<$number; $i++) {
      $random = random_int(0, $count);
      $randoms[] = $keys[$random];
      unset($keys[$random]);
    }
    return ($number === 1) ? $randoms[0] : $randoms;
  }

  /**
   * modifies an array's keys based on a callback function.
   *
   * the provided callback should return an integer or string key,
   * or null to exclude the item from the re-keyed array.
   * any other return value will trigger an exception.
   *
   * @param array    $subject          the subject array
   * @param callable $callback         (string|int $k, mixed $v) : string|int|null
   * @throws BadFunctionCallException  if callback throws or returns an invalid key
   * @return array                     the re-keyed array
   */
  public static function rekey(array $subject, callable $callback) : array {
    $rekeyed = [];
    foreach ($subject as $k=>$v) {
      try {
        $r = $callback($k, $v);
      } catch (\Throwable $e) {
        $m = 'uncaught exception thrown from $callback';
        throw new \BadFunctionCallException($m, E_WARNING, $e);
      }
      if ($r === null) {
        continue;
      }
      if (! (is_string($r) || is_int($r))) {
        $t = Vars::type($r);
        $m = "new key must be an integer or string; \$callback returned [{$t}]";
        throw new \BadFunctionCallException($m, E_WARNING);
      }
      $rekeyed[$r] = $v;
    }
    return $rekeyed;
  }

  /**
   * splices values into an array, preserving associative keys in replacement
   * (in case of key collision, existing items are discarded).
   *
   * N.B., operates on subject array by reference.
   *
   * N.B., the complex behaviour of offset and length in array_splice() are NOT preserved;
   * both arguments must be non-negative integers.
   *
   * @param array &$subject     the subject array
   * @param int   $offset       starting offset
   * @param int   $length       length of slice to remove (NULL = truncate)
   * @param array $replacement  replacement value(s) with optional key(s)
   */
  public function splice(array &$subject, int $offset, int $length=null, array $replacement=[]) {
    if ($offset < 0) {
      $m = "\$offset must be a non-negative integer; [{$offset}] provided";
      throw new \InvalidArgumentException($m, E_USER_WARNING);
    }
    if ($length < 0) {
      $m = "\$length must be a non-negative integer; [{$length}] provided";
      throw new \InvalidArgumentException($m, E_USER_WARNING);
    }
    $front = array_slice($subject, 0, $offset, true);
    $back = ($length === null) ?
      []:
      array_slice($subject, ($offset + $length), null, true);
    foreach ($replacement as $k => $v) {
      if (is_string($k) && isset($front[$k])) {
        unset($front[$k]);
      }
      if (is_string($k) && isset($back[$k])) {
        unset($back[$k]);
      }
    }
    $subject = array_merge($front, $replacement, $back);
  }
}
