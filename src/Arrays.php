<?php
/**
 * @package    at.util
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2018
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

use at\util\ArraysException;

/**
 * Utility functions for arrays.
 *
 * @method array Arrays::arsort()            @see http://php.net/arsort
 * @method array Arrays::asort()             @see http://php.net/asort
 * @method array Arrays::changeKeyCase()     @see http://php.net/array_change_key_case
 * @method array Arrays::chunk()             @see http://php.net/array_chunk
 * @method array Arrays::column()            @see http://php.net/array_column
 * @method array Arrays::combine()           @see http://php.net/array_combine
 * @method array Arrays::countValues()       @see http://php.net/array_count_values
 * @method array Arrays::diffAssoc()         @see http://php.net/array_diff_assoc
 * @method array Arrays::diffKey()           @see http://php.net/array_diff_key
 * @method array Arrays::diffUassoc()        @see http://php.net/array_diff_uassoc
 * @method array Arrays::diffUkey()          @see http://php.net/array_diff_ukey
 * @method array Arrays::diff()              @see http://php.net/array_diff
 * @method array Arrays::fillKeys()          @see http://php.net/array_fill_keys
 * @method array Arrays::fill()              @see http://php.net/array_fill
 * @method array Arrays::filter()            @see http://php.net/array_filter
 * @method array Arrays::flip()              @see http://php.net/array_flip
 * @method array Arrays::intersectAssoc()    @see http://php.net/array_intersect_assoc
 * @method array Arrays::intersectKey()      @see http://php.net/array_intersect_key
 * @method array Arrays::intersectUassoc()   @see http://php.net/array_intersect_uassoc
 * @method array Arrays::intersectUkey()     @see http://php.net/array_intersect_ukey
 * @method array Arrays::intersect()         @see http://php.net/array_intersect
 * @method array Arrays::keyExists()         @see http://php.net/array_key_exists
 * @method array Arrays::keys()              @see http://php.net/array_keys
 * @method array Arrays::krsort()            @see http://php.net/krsort
 * @method array Arrays::ksort()             @see http://php.net/ksort
 * @method array Arrays::map()               @see http://php.net/array_map
 * @method array Arrays::mergeRecursive()    @see http://php.net/array_merge_recursive
 * @method array Arrays::merge()             @see http://php.net/array_merge
 * @method array Arrays::natcasesort()       @see http://php.net/array_natcasesort
 * @method array Arrays::natsort()           @see http://php.net/natsort
 * @method array Arrays::pad()               @see http://php.net/array_pad
 * @method mixed Arrays::product()           @see http://php.net/array_product
 * @method array Arrays::push()              @see http://php.net/array_push
 * @method mixed Arrays::rand()              @see http://php.net/array_rand
 * @method mixed Arrays::reduce()            @see http://php.net/array_reduce
 * @method array Arrays::replaceRecursive()  @see http://php.net/array_replace_recursive
 * @method array Arrays::replace()           @see http://php.net/array_replace
 * @method array Arrays::reverse()           @see http://php.net/array_reverse
 * @method array Arrays::rsort()             @see http://php.net/rsort
 * @method mixed Arrays::search()            @see http://php.net/array_search
 * @method array Arrays::shuffle()           @see http://php.net/shuffle
 * @method array Arrays::slice()             @see http://php.net/array_slice
 * @method array Arrays::sort()              @see http://php.net/sort
 * @method array Arrays::splice()            @see http://php.net/array_splice
 * @method mixed Arrays::sum()               @see http://php.net/array_sum
 * @method array Arrays::uasort()            @see http://php.net/uasort
 * @method array Arrays::udiffAssoc()        @see http://php.net/array_udiff_assoc
 * @method array Arrays::udiffUassoc()       @see http://php.net/array_udiff_uassoc
 * @method array Arrays::udiff()             @see http://php.net/array_udiff
 * @method array Arrays::uintersectAssoc()   @see http://php.net/array_uintersect_assoc
 * @method array Arrays::uintersectUassoc()  @see http://php.net/array_uintersect_uassoc
 * @method array Arrays::uintersect()        @see http://php.net/array_uintersect
 * @method array Arrays::uksort()            @see http://php.net/uksort
 * @method array Arrays::unique()            @see http://php.net/array_unique
 * @method array Arrays::unshift()           @see http://php.net/array_unshift
 * @method array Arrays::usort()             @see http://php.net/usort
 * @method array Arrays::values()            @see http://php.net/array_values
 */
class Arrays {

  /**
   * Keys for dig() $options tuple.
   *
   * @type int DIG_DELIM  a user-specified delimiter
   * @type int DIG_THROW  throw on failure?
   */
  public const DIG_DELIM = 0;
  public const DIG_THROW = 1;

  /** @type callable[]  list of supported array_* functions. */
  protected const _ARRAY_FUNCTIONS = [
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
  protected const _ARRAY_REF_FUNCTIONS = [
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
   * Proxies native php array functions.
   * @see <http://php.net/__callStatic>
   *
   * Both proxy names and native function names are supported.
   * Note, supported by-ref functions *do not* operate on the subject array by reference.
   * @see self::supportedArrayFunctions() for supported functions list.
   *
   * The user is responsible for argument types/order.
   *
   * @param string $name          function to proxy
   * @param mixed  ...$arguments  function arguments
   * @throws ArraysException      if the method is not supported
   * @return mixed                subject array if the native function takes it by reference;
   *                              native return value of the function otherwise
   */
  public static function __callStatic($name, $arguments) {
    try {
      $function = self::_ARRAY_FUNCTIONS[$name] ??
        (in_array($name, self::_ARRAY_FUNCTIONS) ? $name : null);
      if ($function) {
        return $function(...$arguments);
      }

      $refFunction = self::_ARRAY_REF_FUNCTIONS[$name] ??
        (in_array($name, self::_ARRAY_REF_FUNCTIONS) ? $name : null);
      if ($refFunction) {
        $subject = array_shift($arguments);
        $refFunction($subject, ...$arguments);
        return $subject;
      }

      throw new ArraysException(ArraysException::NO_SUCH_METHOD, ['method' => $name]);
    } catch (TypeError $e) {
      // re-throw from here
      throw new TypeError($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Sorts array items into categories based on given key name.
   *
   * @param array $subject     the subject array
   * @param array $index       key of column to categorize by
   * @throws ArrayssException  if key is not present in all rows
   * @return array             the categorized array
   */
  public static function categorize(array $subject, $key) : array {
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
   * Checks whether a given value is in the subject array (values are compared strictly).
   *
   * @param array $subject  the subject array
   * @param mixed $value    the value to check for
   * @return bool           true if value exists in subject; false otherwise
   */
  public static function contains(array $subject, $value) : bool {
    return in_array($value, $subject, true);
  }

  /**
   * Looks up value at given path (if it exists).
   *
   * @param array  $subject  the subject array
   * @param string $path     delimited list of keys to follow
   * @param array  $opts     execution options
   *  - string self::DIG_DELIM  the delimiter to split the path on (defaults to '.')
   *  - bool   self::DIG_THROW  throw if the path does not exist (defaults to false)?
   * @throws ArraysException  if the path does not exist in the subject array
   * @return mixed            the value at the given path if it exists; null otherwise
   */
  public static function dig(array $subject, string $path, array $opts = []) {
    $delim = $opts[self::DIG_DELIM] ?? '.';
    Value::hint('$opts[Arrays::DIG_DELIM]', $delim, Value::STRING);

    $throw = $opts[self::DIG_THROW] ?? false;
    Value::hint('$opts[Arrays::DIG_THROW]', $throw, Value::BOOL);

    foreach (explode($delim, $path) as $key) {
      if (! (is_array($subject) && isset($subject[$key]))) {
        if ($throw) {
          throw new ArraysException(ArraysException::INVALID_PATH, ['path' => $path]);
        }
        return null;
      }
      $subject = $subject[$key];
    }
    return $subject;
  }

  /**
   * Like array_merge_recursive(), but only merges arrays
   * (no casting: arrays will never be merged with scalar values).
   * @see <https://gist.github.com/adrian-enspired/e766b37334130ea04eaf>
   *
   * @param array $subject    the subject array
   * @param array ...$arrays  secondary array(s) to extend with
   * @return array            the extended array
   */
  public static function extend(array $subject, array ...$arrays) : array {
    foreach ($arrays as $array) {
      foreach ($array as $key => $value) {
        if (is_int($key)) {
          $subject[] = $value;
          continue;
        }

        if (isset($subject[$key]) && is_array($subject[$key]) && is_array($value)) {
          $subject[$key] = self::extend($subject[$key], $value);
          continue;
        }

        $subject[$key] = $value;
      }
    }
    return $subject;
  }

  /**
   * Indexes an array using the values of the given key
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
   * Determines whether an array is a list (has 0-based, sequential, incrementing integer keys).
   *
   * @param array $subject  the subject array
   * @return bool           true if subject is a list; false otherwise
   */
  public static function isList(array $subject) : bool {
    $i = 0;
    foreach ($subject as $k=>$v) {
      if ($k !== $i) { return false; }
      $i++;
    }
    return true;
  }

  /**
   * Selects one or more keys from an array at random.
   *
   * This method uses random_int().
   * Use Arrays::rand() unless you actually have need for cryptographic randomness.
   *
   * @param array $subject    the subject array
   * @param int   $number     the number of items to pick (must be a positive integer)
   * @throws ArraysException  when trying to pick more items than are in the array
   * @return scalar|scalar[]  the selected key(s)
   */
  public static function random(array $subject, int $number = 1) {
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
    for ($i = 0; $i < $number; $i++) {
      $random = random_int(0, count($keys) - 1);
      $randoms[] = $keys[$random];
      unset($keys[$random]);
      $keys = array_values($keys);
    }
    return ($number === 1) ? $randoms[0] : $randoms;
  }

  /**
   * Modifies an array's keys based on a callback function.
   *
   * The provided callback should return an integer or string key,
   * or null to exclude the item from the re-keyed array.
   *
   * @param array    $subject   the subject array
   * @param callable $callback  (string|int $k, mixed $v) : string|int|null
   * @throws ArraysException    if callback throws or returns an invalid key
   * @return array              the re-keyed array
   */
  public static function rekey(array $subject, callable $callback) : array {
    $rekeyed = [];

    try {
      foreach ($subject as $k=>$v) {
        $r = $callback($k, $v);
        if ($r !== null) {
          $rekeyed[$r] = $v;
        }
      }
    } catch (Throwable $e) {
      throw new ArraysException(ArraysException::BAD_CALL_RIPLEY, $e, ['method' => __METHOD__]);
    }

    return $rekeyed;
  }

  /**
   * Lists php array functions that this class supports (proxies).
   *
   * @return callable[]  a list of php array function names
   */
  public static function supportedArrayFunctions() : array {
    return self::_ARRAY_FUNCTIONS + self::_ARRAY_REF_FUNCTIONS;
  }
}
