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
   *
   * supports sorting functions (does not modify the input array) and all "array_*" functions.
   * compact(), extract(), and pointer functions (e.g., current(), each()) are not supported.
   * the user is responsible for argument types/order.
   */
  public static function __callStatic($name, $arguments) {
    if (in_array($name, self::SORT_FUNCTIONS)) {
      call_user_func_array($name, $arguments);
      return $arguments[0];
    }

    if (! function_exists("array_{$name}")) {
      throw new ArraysException(ArraysException::NO_SUCH_METHOD);
    }
    return call_user_func_array("array_{$name}", $arguments);
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
      'delim' => '.',
      'throw' => false
    ];

    foreach (explode($opts['delim'], $path) as $key) {
      if (! (is_array($subject) && isset($subject[$key]))) {
        if ($opts['throw']) {
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
        ['min' => '1', 'max' => $count]
      );
    }

    $keys = array_keys($subject);
    if ($number === $count) {
      return $keys;
    }

    $randoms = [];
    for ($i=0; $i<$number; $i++) {
      $count = count($keys);
      $randoms[] = random_int(0, $count);
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
