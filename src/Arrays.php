<?php
/**
 * @package    at.util
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

use at\util\ArraysException;

/**
 * utility functions for arrays. */
class Arrays {

  /** @type callable[]  list of supported array_* functions. */
  const ARRAY_FUNCTIONS = [
    'array_change_key_case',
    'array_chunk',
    'array_column',
    'array_combine',
    'array_count_values',
    'array_diff_assoc',
    'array_diff_key',
    'array_diff_uassoc',
    'array_diff_ukey',
    'array_diff',
    'array_fill_keys',
    'array_fill',
    'array_filter',
    'array_flip',
    'array_intersect_assoc',
    'array_intersect_key',
    'array_intersect_uassoc',
    'array_intersect_ukey',
    'array_intersect',
    'array_key_exists',
    'array_keys',
    'array_map',
    'array_merge_recursive',
    'array_merge',
    'array_pad',
    'array_product',
    'array_rand',
    'array_reduce',
    'array_replace_recursive',
    'array_replace',
    'array_reverse',
    'array_search',
    'array_slice',
    'array_splice',
    'array_sum',
    'array_udiff_assoc',
    'array_udiff_uassoc',
    'array_udiff',
    'array_uintersect_assoc',
    'array_uintersect_uassoc',
    'array_uintersect',
    'array_unique',
    'array_values'
  ];

  /**
   * keys for various method options.
   *
   * @type int OPT_DELIM  a user-specified delimiter
   * @type int OPT_THROW  throw on failure?
   */
  const OPT_DELIM = 0;
  const OPT_THROW = 1;

  /** @type callable[]  list of supported array sorting functions. */
  const SORT_FUNCTIONS = [
    'arsort',
    'asort',
    'krsort',
    'ksort',
    'natcasesort',
    'natsort',
    'rsort',
    'shuffle',
    'sort',
    'uasort',
    'uksort',
    'usort'
  ];

  /**
   * proxies native php array functions.
   * @see self::ARRAY_FUNCTIONS and self::SORT_FUNCTIONS for supported functions.
   * @see <http://php.net/__callStatic>
   *
   * the user is responsible for argument types/order.
   */
  public static function __callStatic($name, $arguments) {
    if (in_array($name, self::SORT_FUNCTIONS)) {
      // stupid by-reference arguments
      $array = array_shift($arguments);
      $name($array, ...$arguments);
      return $array;
    }

    $array_name = "array_{$name}";
    if (in_array($array_name, self::ARRAY_FUNCTIONS)) {
      return $array_name(...$arguments);
    }

    throw new ArraysException(ArraysException::NO_SUCH_METHOD, ['method' => $name]);
  }

  /**
   * sorts array items into categories based on given key name.
   *
   * @param array $subject   the subject array
   * @param array $index     key of column to categorize by
   * @throws ArraysExeption  if key is not present in all rows
   * @return array           the categorized array
   */
  public static function categorize(array $subject, $key) {
    $categorized = [];
    foreach ($subject as $row) {
      if (! isset($row[$key])) {
        throw new ArraysException(ArraysException::INVALID_CATEGORY_KEY, ['key' => $key]);
      }
      $categorized[$row[$key]][] = $row;
    }
    return $categorized;
  }

  /**
   * checks whether a given value is in the subject array (values are compared strictly).
   *
   * @param array $subject  the subject array
   * @param mixed $value    the value to check for
   * @return bool           true if value exists in subject; false otherwise
   */
  public static function contains(array $subject, $value) : bool {
    return in_array($value, $subject, true);
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
      self::OPT_DELIM => '.',
      self::OPT_THROW => false
    ];

    foreach (explode($opts[self::OPT_DELIM], $path) as $key) {
      if (! (is_array($subject) && isset($subject[$key]))) {
        if ($opts[self::OPT_THROW]) {
          throw new ArraysException(ArraysException::INVALID_PATH, ['path' => $path]);
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
   * indexes an array using the values of the given key
   * (on index collision, later values will replace earlier values).
   *
   * @param array      $subject  the subject array
   * @param string|int $key      the key to index by
   * @return array               the indexed array
   */
  public static function index(array $subject, $key) : array {
    return array_column($subject, null, $key);
  }

  /**
   * determines whether an array is a list (has 0-based, sequentially incrementing indexes).
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
   * lists php array functions that this class supports (proxies).
   *
   * @return callable[]  a list of php array function names
   */
  public static function list_array_methods() : array {
    return array_merge(self::ARRAY_FUNCTIONS, self::SORT_FUNCTIONS);
  }

  /**
   * selects one or more keys from an array at random.
   *
   * this method uses random_int().
   * use array_rand() unless you actually have need for cryptographic randomness.
   *
   * @param array $subject    the subject array
   * @param int   $number     the number of items to pick (must be a positive integer)
   * @throws ArraysException  when trying to pick more items than are in the array
   * @return scalar|scalar[]  the selected key(s)
   */
  public static function random(array $subject, int $number=1) {
    $count = count($subject);
    if ($number < 1 || $number > $count) {
      throw new ArraysException(
        ArraysException::INVALID_SAMPLE_SIZE,
        ['count' => $count, 'size' => $number]
      );
    }

    $keys = array_keys($subject);
    if ($number === $count) {
      return $keys;
    }

    $randoms = [];
    for ($i=0; $i<$number; $i++) {
      $count = count($keys);
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
   *
   * @param array    $subject          the subject array
   * @param callable $callback         (string|int $k, mixed $v) : string|int|null
   * @throws BadFunctionCallException  if callback throws or returns an invalid key
   * @return array                     the re-keyed array
   */
  public static function rekey(array $subject, callable $callback) : array {
    $rekeyed = [];
    foreach ($subject as $k=>$v) {
      $r = $callback($k, $v);
      if ($r === null) {
        continue;
      }
      $rekeyed[$r] = $v;
    }
    return $rekeyed;
  }
}
