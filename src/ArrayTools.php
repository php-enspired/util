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
 * @method array ArrayTools::arsort()            @see http://php.net/arsort
 * @method array ArrayTools::asort()             @see http://php.net/asort
 * @method array ArrayTools::changeKeyCase()     @see http://php.net/array_change_key_case
 * @method array ArrayTools::chunk()             @see http://php.net/array_chunk
 * @method array ArrayTools::column()            @see http://php.net/array_column
 * @method array ArrayTools::combine()           @see http://php.net/array_combine
 * @method array ArrayTools::countValues()       @see http://php.net/array_count_values
 * @method array ArrayTools::diffAssoc()         @see http://php.net/array_diff_assoc
 * @method array ArrayTools::diffKey()           @see http://php.net/array_diff_key
 * @method array ArrayTools::diffUassoc()        @see http://php.net/array_diff_uassoc
 * @method array ArrayTools::diffUkey()          @see http://php.net/array_diff_ukey
 * @method array ArrayTools::diff()              @see http://php.net/array_diff
 * @method array ArrayTools::fillKeys()          @see http://php.net/array_fill_keys
 * @method array ArrayTools::fill()              @see http://php.net/array_fill
 * @method array ArrayTools::filter()            @see http://php.net/array_filter
 * @method array ArrayTools::flip()              @see http://php.net/array_flip
 * @method array ArrayTools::intersectAssoc()    @see http://php.net/array_intersect_assoc
 * @method array ArrayTools::intersectKey()      @see http://php.net/array_intersect_key
 * @method array ArrayTools::intersectUassoc()   @see http://php.net/array_intersect_uassoc
 * @method array ArrayTools::intersectUkey()     @see http://php.net/array_intersect_ukey
 * @method array ArrayTools::intersect()         @see http://php.net/array_intersect
 * @method array ArrayTools::key_exists()        @see http://php.net/array_key_exists
 * @method array ArrayTools::keys()              @see http://php.net/array_keys
 * @method array ArrayTools::krsort()            @see http://php.net/krsort
 * @method array ArrayTools::ksort()             @see http://php.net/ksort
 * @method array ArrayTools::map()               @see http://php.net/array_map
 * @method array ArrayTools::mergeRecursive()    @see http://php.net/array_merge_recursive
 * @method array ArrayTools::merge()             @see http://php.net/array_merge
 * @method array ArrayTools::natcasesort()       @see http://php.net/array_natcasesort
 * @method array ArrayTools::natsort()           @see http://php.net/natsort
 * @method array ArrayTools::pad()               @see http://php.net/array_pad
 * @method mixed ArrayTools::product()           @see http://php.net/array_product
 * @method array ArrayTools::push()              @see http://php.net/array_push
 * @method mixed ArrayTools::rand()              @see http://php.net/array_rand
 * @method mixed ArrayTools::reduce()            @see http://php.net/array_reduce
 * @method array ArrayTools::replaceRecursive()  @see http://php.net/array_replace_recursive
 * @method array ArrayTools::replace()           @see http://php.net/array_replace
 * @method array ArrayTools::reverse()           @see http://php.net/array_reverse
 * @method array ArrayTools::rsort()             @see http://php.net/rsort
 * @method mixed ArrayTools::search()            @see http://php.net/array_search
 * @method array ArrayTools::shuffle()           @see http://php.net/shuffle
 * @method array ArrayTools::slice()             @see http://php.net/array_slice
 * @method array ArrayTools::sort()              @see http://php.net/sort
 * @method array ArrayTools::splice()            @see http://php.net/array_splice
 * @method mixed ArrayTools::sum()               @see http://php.net/array_sum
 * @method array ArrayTools::uasort()            @see http://php.net/uasort
 * @method array ArrayTools::udiffAssoc()        @see http://php.net/array_udiff_assoc
 * @method array ArrayTools::udiffUassoc()       @see http://php.net/array_udiff_uassoc
 * @method array ArrayTools::udiff()             @see http://php.net/array_udiff
 * @method array ArrayTools::uintersectAssoc()   @see http://php.net/array_uintersect_assoc
 * @method array ArrayTools::uintersectUassoc()  @see http://php.net/array_uintersect_uassoc
 * @method array ArrayTools::uintersect()        @see http://php.net/array_uintersect
 * @method array ArrayTools::uksort()            @see http://php.net/uksort
 * @method array ArrayTools::unique()            @see http://php.net/array_unique
 * @method array ArrayTools::unshift()           @see http://php.net/array_unshift
 * @method array ArrayTools::usort()             @see http://php.net/usort
 * @method array ArrayTools::values()            @see http://php.net/array_values
 */
class ArrayTools {

  /** @type callable[]  list of supported array_* functions. */
  const ARRAY_FUNCTIONS = [
    'changeKeyCase' => 'array_change_key_case',
    'chunk' => 'array_chunk',
    'column' => 'array_column',
    'combine' => 'array_combine',
    'countValues' => 'array_count_values',
    'diffAssoc' => 'array_diff_assoc',
    'diffKey' => 'array_diff_key',
    'diffUassoc' => 'array_diff_uassoc',
    'diffUkey' => 'array_diff_ukey',
    'diff' => 'array_diff',
    'fillKeys' => 'array_fill_keys',
    'fill' => 'array_fill',
    'filter' => 'array_filter',
    'flip' => 'array_flip',
    'intersectAssoc' => 'array_intersect_assoc',
    'intersectKey' => 'array_intersect_key',
    'intersectUassoc' => 'array_intersect_uassoc',
    'intersectUkey' => 'array_intersect_ukey',
    'intersect' => 'array_intersect',
    'keyExists' => 'array_key_exists',
    'keys' => 'array_keys',
    'map' => 'array_map',
    'mergeRecursive' => 'array_merge_recursive',
    'merge' => 'array_merge',
    'pad' => 'array_pad',
    'product' => 'array_product',
    'rand' => 'array_rand',
    'reduce' => 'array_reduce',
    'replaceRecursive' => 'array_replace_recursive',
    'replace' => 'array_replace',
    'reverse' => 'array_reverse',
    'search' => 'array_search',
    'slice' => 'array_slice',
    'sum' => 'array_sum',
    'udiffAssoc' => 'array_udiff_assoc',
    'udiffUassoc' => 'array_udiff_uassoc',
    'udiff' => 'array_udiff',
    'uintersectAssoc' => 'array_uintersect_assoc',
    'uintersectUassoc' => 'array_uintersect_uassoc',
    'uintersect' => 'array_uintersect',
    'unique' => 'array_unique',
    'values' => 'array_values'
  ];

  /** @type callable[]  list of supported by-reference array functions. */
  const ARRAY_REF_FUNCTIONS = [
    'splice' => 'array_splice',
    'push' => 'array_push',
    'unshift' => 'array_unshift',
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
   * both proxy names and native function names are supported.
   * note, supported by-ref functions *do not* operate on the subject array by reference.
   *
   * the user is responsible for argument types/order.
   *
   * @param string $name          function to proxy
   * @param mixed  ...$arguments  function arguments
   * @throws ArrayToolsException  if the method is not supported
   * @return mixed                subject array if the native function takes it by reference;
   *                              native return value of the function otherwise
   */
  public static function call(string $name, ...$arguments) {
    $function = self::ARRAY_FUNCTIONS[$name] ??
      (in_array($name, self::ARRAY_FUNCTIONS) ? $name : null);
    if ($function) {
      return $function(...$arguments);
    }

    $refFunction = self::ARRAY_REF_FUNCTIONS[$name] ??
      (in_array($name, self::ARRAY_REF_FUNCTIONS) ? $name : null);
    if ($refFunction) {
      $subject = array_shift($arguments);
      $refFunction($subject, ...$arguments);
      return $subject;
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
   *    @type string self::OPT_DELIM  the delimiter to split the path on (defaults to '.')
   *    @type bool   self::OPT_THROW  throw if the path does not exist (defaults to false)?
   *  }
   * @throws ArrayToolsException  if the path does not exist in the subject array
   * @return mixed                the value at the given path if it exists; null otherwise
   */
  public static function dig(array $subject, string $path, array $opts = []) {
    $delim = $opts[self::OPT_DELIM] ?? '.';
    $throw = $opts[self::OPT_THROW] ?? false;

    foreach (explode($delim, $path) as $key) {
      if (! (is_array($subject) && isset($subject[$key]))) {
        if ($throw) {
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
      $random = random_int(0, count($keys) - 1);
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
