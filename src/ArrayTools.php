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

use TypeError;

use at\util\ArrayToolsException;

/**
 * utility functions for arrays.
 *
 * @method array ArrayTools::arsort()             @see http://php.net/arsort
 * @method array ArrayTools::asort()              @see http://php.net/asort
 * @method array ArrayTools::change_key_case()    @see http://php.net/array_change_key_case
 * @method array ArrayTools::chunk()              @see http://php.net/array_chunk
 * @method array ArrayTools::column()             @see http://php.net/array_column
 * @method array ArrayTools::combine()            @see http://php.net/array_combine
 * @method array ArrayTools::count_values()       @see http://php.net/array_count_values
 * @method array ArrayTools::diff_assoc()         @see http://php.net/array_diff_assoc
 * @method array ArrayTools::diff_key()           @see http://php.net/array_diff_key
 * @method array ArrayTools::diff_uassoc()        @see http://php.net/array_diff_uassoc
 * @method array ArrayTools::diff_ukey()          @see http://php.net/array_diff_ukey
 * @method array ArrayTools::diff()               @see http://php.net/array_diff
 * @method array ArrayTools::fill_keys()          @see http://php.net/array_fill_keys
 * @method array ArrayTools::fill()               @see http://php.net/array_fill
 * @method array ArrayTools::filter()             @see http://php.net/array_filter
 * @method array ArrayTools::flip()               @see http://php.net/array_flip
 * @method array ArrayTools::intersect_assoc()    @see http://php.net/array_intersect_assoc
 * @method array ArrayTools::intersect_key()      @see http://php.net/array_intersect_key
 * @method array ArrayTools::intersect_uassoc()   @see http://php.net/array_intersect_uassoc
 * @method array ArrayTools::intersect_ukey()     @see http://php.net/array_intersect_ukey
 * @method array ArrayTools::intersect()          @see http://php.net/array_intersect
 * @method array ArrayTools::key_exists()         @see http://php.net/array_key_exists
 * @method array ArrayTools::keys()               @see http://php.net/array_keys
 * @method array ArrayTools::krsort()             @see http://php.net/krsort
 * @method array ArrayTools::ksort()              @see http://php.net/ksort
 * @method array ArrayTools::map()                @see http://php.net/array_map
 * @method array ArrayTools::merge_recursive()    @see http://php.net/array_merge_recursive
 * @method array ArrayTools::merge()              @see http://php.net/array_merge
 * @method array ArrayTools::natcasesort()        @see http://php.net/array_natcasesort
 * @method array ArrayTools::natsort()            @see http://php.net/natsort
 * @method array ArrayTools::pad()                @see http://php.net/array_pad
 * @method mixed ArrayTools::product()            @see http://php.net/array_product
 * @method array ArrayTools::push()               @see http://php.net/array_push
 * @method mixed ArrayTools::rand()               @see http://php.net/array_rand
 * @method mixed ArrayTools::reduce()             @see http://php.net/array_reduce
 * @method array ArrayTools::replace_recursive()  @see http://php.net/array_replace_recursive
 * @method array ArrayTools::replace()            @see http://php.net/array_replace
 * @method array ArrayTools::reverse()            @see http://php.net/array_reverse
 * @method array ArrayTools::rsort()              @see http://php.net/rsort
 * @method mixed ArrayTools::search()             @see http://php.net/array_search
 * @method array ArrayTools::shuffle()            @see http://php.net/shuffle
 * @method array ArrayTools::slice()              @see http://php.net/array_slice
 * @method array ArrayTools::sort()               @see http://php.net/sort
 * @method array ArrayTools::splice()             @see http://php.net/array_splice
 * @method mixed ArrayTools::sum()                @see http://php.net/array_sum
 * @method array ArrayTools::uasort()             @see http://php.net/uasort
 * @method array ArrayTools::udiff_assoc()        @see http://php.net/array_udiff_assoc
 * @method array ArrayTools::udiff_uassoc()       @see http://php.net/array_udiff_uassoc
 * @method array ArrayTools::udiff()              @see http://php.net/array_udiff
 * @method array ArrayTools::uintersect_assoc()   @see http://php.net/array_uintersect_assoc
 * @method array ArrayTools::uintersect_uassoc()  @see http://php.net/array_uintersect_uassoc
 * @method array ArrayTools::uintersect()         @see http://php.net/array_uintersect
 * @method array ArrayTools::uksort()             @see http://php.net/uksort
 * @method array ArrayTools::unique()             @see http://php.net/array_unique
 * @method array ArrayTools::unshift()            @see http://php.net/array_unshift
 * @method array ArrayTools::usort()              @see http://php.net/usort
 * @method array ArrayTools::values()             @see http://php.net/array_values
 */
class ArrayTools {

  /** @type callable[]  list of supported array_* functions. */
  const ARRAY_FUNCTIONS = [
    'array_change_key_case' => 'array_change_key_case',
    'array_chunk' => 'array_chunk',
    'array_column' => 'array_column',
    'array_combine' => 'array_combine',
    'array_count_values' => 'array_count_values',
    'array_diff_assoc' => 'array_diff_assoc',
    'array_diff_key' => 'array_diff_key',
    'array_diff_uassoc' => 'array_diff_uassoc',
    'array_diff_ukey' => 'array_diff_ukey',
    'array_diff' => 'array_diff',
    'array_fill_keys' => 'array_fill_keys',
    'array_fill' => 'array_fill',
    'array_filter' => 'array_filter',
    'array_flip' => 'array_flip',
    'array_intersect_assoc' => 'array_intersect_assoc',
    'array_intersect_key' => 'array_intersect_key',
    'array_intersect_uassoc' => 'array_intersect_uassoc',
    'array_intersect_ukey' => 'array_intersect_ukey',
    'array_intersect' => 'array_intersect',
    'array_key_exists' => 'array_key_exists',
    'array_keys' => 'array_keys',
    'array_map' => 'array_map',
    'array_merge_recursive' => 'array_merge_recursive',
    'array_merge' => 'array_merge',
    'array_pad' => 'array_pad',
    'array_product' => 'array_product',
    'array_rand' => 'array_rand',
    'array_reduce' => 'array_reduce',
    'array_replace_recursive' => 'array_replace_recursive',
    'array_replace' => 'array_replace',
    'array_reverse' => 'array_reverse',
    'array_search' => 'array_search',
    'array_slice' => 'array_slice',
    'array_sum' => 'array_sum',
    'array_udiff_assoc' => 'array_udiff_assoc',
    'array_udiff_uassoc' => 'array_udiff_uassoc',
    'array_udiff' => 'array_udiff',
    'array_uintersect_assoc' => 'array_uintersect_assoc',
    'array_uintersect_uassoc' => 'array_uintersect_uassoc',
    'array_uintersect' => 'array_uintersect',
    'array_unique' => 'array_unique',
    'array_values' => 'array_values'
  ];

  /** @type callable[]  list of supported by-reference array functions. */
  const ARRAY_REF_FUNCTIONS = [
    'array_splice' => 'array_splice',
    'array_push' => 'array_push',
    'array_unshift' => 'array_unshift',
    'arsort' => 'arsort',
    'asort' => 'asort',
    'krsort' => 'krsort',
    'ksort' => 'ksort',
    'natcasesort' => 'natcasesort',
    'natsort' => 'natsort',
    'rsort' => 'rsort',
    'shuffle' => 'shuffle',
    'sort' => 'sort',
    'uasort' => 'uasort',
    'uksort' => 'uksort',
    'usort' => 'usort'
  ];

  /**
   * keys for various method options.
   *
   * @type int OPT_DELIM  a user-specified delimiter
   * @type int OPT_THROW  throw on failure?
   */
  const OPT_DELIM = 0;
  const OPT_THROW = 1;


  /**
   * proxies native php array functions.
   * @see <http://php.net/__callStatic>
   * @see self::call()
   */
  public static function __callStatic($name, $arguments) {
    try {
      return self::call($name, ...$arguments);
    } catch (TypeError $e) {
      // re-throw from here
      throw new TypeError($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * proxies native php array functions.
   *
   * @see self::ARRAY_FUNCTIONS and self::ARRAY_REF_FUNCTIONS for supported functions.
   * note, supported by-ref functions *do not* operate on the subject array by reference.
   *
   * the leading "array_" in the function name may be omitted.
   * the user is responsible for argument types/order.
   *
   * @param string $name          function name to proxy
   * @param array  $subject       the subject array
   * @param mixed  ...$arguments  function arguments
   * @throws ArrayToolsException  if the method is not supported
   * @return mixed                subject array if the native function takes it by reference;
   *                              native return value of the function otherwise
   */
  public static function call(string $name, array $subject, ...$arguments) {
    $array_name = "array_{$name}";

    foreach ([$name, $array_name] as $function) {
      if (in_array($function, self::ARRAY_FUNCTIONS)) {
        return $function($subject, ...$arguments);
      }

      if (in_array($function, self::ARRAY_REF_FUNCTIONS)) {
        $function($subject, ...$arguments);
        return $subject;
      }
    }

    throw new ArrayToolsException(ArrayToolsException::NO_SUCH_METHOD, ['method' => $name]);
  }

  /**
   * sorts array items into categories based on given key name.
   *
   * @param array $subject         the subject array
   * @param array $index           key of column to categorize by
   * @throws ArrayToolssException  if key is not present in all rows
   * @return array                 the categorized array
   */
  public static function categorize(array $subject, $key) {
    $categorized = [];
    foreach ($subject as $row) {
      if (! isset($row[$key])) {
        throw new ArrayToolsException(ArrayToolsException::INVALID_CATEGORY_KEY, ['key' => $key]);
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
   * @param array  $subject  the subject array
   * @param string $path     delimited list of keys to follow
   * @param array  $opts {
   *    @type string $delim  the delimiter to split the path on (defaults to '.')
   *    @type bool   $throw  throw if the path does not exist (defaults to false)?
   *  }
   * @throws ArrayToolsException  if the path does not exist in the subject array
   * @return mixed                the value at the given path if it exists; null otherwise
   */
  public static function dig(array $subject, string $path, array $opts = []) {
    $opts = $opts + [
      self::OPT_DELIM => '.',
      self::OPT_THROW => false
    ];

    foreach (explode($opts[self::OPT_DELIM], $path) as $key) {
      if (! (is_array($subject) && isset($subject[$key]))) {
        if ($opts[self::OPT_THROW]) {
          throw new ArrayToolsException(ArrayToolsException::INVALID_PATH, ['path' => $path]);
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
  public static function extend(array $subject, array ...$arrays): array {
    foreach ($arrays as $array) {
      foreach ($array as $key=>$value) {
        if (is_int($key)) {
          $subject[] = $value;
        } elseif (isset($subject[$key]) && is_array($subject[$key]) && is_array($value)) {
          $subject[$key] = static::extend($subject[$key], $value);
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
  public static function isList(array $subject): bool {
    $i = 0;
    foreach ($subject as $k=>$v) {
      if ($k !== $i) { return false; }
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
   * @param array $subject        the subject array
   * @param int   $number         the number of items to pick (must be a positive integer)
   * @throws ArrayToolsException  when trying to pick more items than are in the array
   * @return scalar|scalar[]      the selected key(s)
   */
  public static function random(array $subject, int $number = 1) {
    $count = count($subject);
    if ($number < 1 || $number > $count) {
      throw new ArrayToolsException(
        ArrayToolsException::INVALID_SAMPLE_SIZE,
        ['count' => $count, 'size' => $number]
      );
    }

    $keys = array_keys($subject);
    if ($number === $count) {
      return $keys;
    }

    $randoms = [];
    for ($i=0; $i<$number; $i++) {
      $random = random_int(0, count($keys));
      $randoms[] = $keys[$random];
      unset($keys[$random]);
      $keys = array_values($keys);
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

  /**
   * lists php array functions that this class supports (proxies).
   *
   * @return callable[]  a list of php array function names
   */
  public static function supportedArrayFunctions() : array {
    return array_merge(self::ARRAY_FUNCTIONS, self::ARRAY_REF_FUNCTIONS);
  }
}
