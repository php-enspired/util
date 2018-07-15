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

use DateTimeImmutable,
    DateTimeInterface;

use at\PRO\PRO;
use at\util\ {
  RuleException,
  Value
};

/**
 * common/convenient validator functions.
 *
 * all rules (validator functions):
 *  - *must* take the value to test as their first argument; additional arguments *may* follow.
 *  - *must* return true if the test passes, and false otherwise.
 *  - *must not* throw exceptions or trigger errors.
 *
 * note, any callable (closures, methods of other classes, built-in php functions, etc.),
 * may be used anywhere a rule is expected so long as they meet these requirements.
 *
 * the dependency on at\PRO is "soft" (things work just fine if it doesn't exist).
 */
class Rule {

  /**
   * callable aliases.
   *
   * @type callable AFTER       same as GREATER, but treats value as a timestring.
   * @type callable ALWAYS      always passes.
   * @type callable BEFORE      same as LESS, but treats value as a timestring.
   * @type callable BETWEEN     passes if min < value < max.
   * @type callable BYTELENGTH  same as FROM, but checks byte length of a string value.
   * @type callable CHARLENGTH  same as BYTELENGTH, but counts characters.
   * @type callable COLLECTION  passes if value is iterable, and all items are of the same type/class.
   * @type callable DURING      same as FROM, but treats value as a timestring.
   * @type callable EQUALS      passes if value is equal to test value.
   * @type callable FROM        passes if min <= value <= max.
   * @type callable GREATER     passes if value > test value.
   * @type callable IS          passes if value is of given class, type, or pseudotype.
   * @type callable LESS        passes if value < test value.
   * @type callable MATCHES     passes if value matches given regular expression.
   * @type callable NEVER       always fails.
   * @type callable ONE_OF      passes if value is one of given values.
   */
  const AFTER = [self::class, 'after'];
  const ALWAYS = [self::class, 'always'];
  const BEFORE = [self::class, 'before'];
  const BETWEEN = [self::class, 'between'];
  const BYTELENGTH = [self::class, 'byteLength'];
  const CHARLENGTH = [self::class, 'charLength'];
  const COLLECTION = [self::class, 'collection'];
  const DURING = [self::class, 'during'];
  const EQUALS = [self::class, 'equals'];
  const FROM = [self::class, 'from'];
  const GREATER = [self::class, 'greater'];
  const IS = [self::class, 'is'];
  const LESS = [self::class, 'less'];
  const MATCHES = [self::class, 'matches'];
  const NEVER = [self::class, 'never'];
  const ONE_OF = [self::class, 'oneOf'];

  /**
   * negations (same as rules above, but opposite result).
   *
   * prefer using NOT where possible, as other NOT_* methods eventually invoke it anyway.
   *
   * @type callable NOT          negates another rule.
   * @type callable NOT_AFTER    same as NOT_GREATER, but treats value as a timestring.
   * @type callable NOT_BEFORE   same as NOT_LESS, but treats value as a timestring.
   * @type callable NOT_BETWEEN  fails if min < value < max.
   * @type callable NOT_DURING   same as NOT_FROM, but treats value as a timestring.
   * @type callable NOT_EQUALS   fails if value is equal to test value.
   * @type callable NOT_FROM     fails if min <= value <= max.
   * @type callable NOT_GREATER  fails if value > test value.
   * @type callable NOT_IS       fails if value is of given class, type, or pseudotype.
   * @type callable NOT_LESS     fails if value < test value.
   * @type callable NOT_MATCHES  fails if value matches given regular expression.
   * @type callable NOT_ONE_OF   fails if value is one of given values.
   */
  const NOT = [self::class, 'not'];
  const NOT_AFTER = [self::class, 'notAfter'];
  const NOT_BEFORE = [self::class, 'notBefore'];
  const NOT_BETWEEN = [self::class, 'notBetween'];
  const NOT_DURING = [self::class, 'notDuring'];
  const NOT_EQUALS = [self::class, 'notEquals'];
  const NOT_FROM = [self::class, 'notFrom'];
  const NOT_GREATER = [self::class, 'notGreater'];
  const NOT_IS = [self::class, 'notIs'];
  const NOT_LESS = [self::class, 'notLess'];
  const NOT_MATCHES = [self::class, 'notMatches'];
  const NOT_ONE_OF = [self::class, 'notOneOf'];

  /**
   * {@inheritDoc}
   * @see http://php.net/__callStatic
   *
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function __callStatic($name, $arguments) {
    if (strpos($name, 'not') === 0) {
      $rule = substr($name, 3);
      if (method_exists(static::class, $rule)) {
        return self::not([static::class, $rule], ...$arguments);
      }
    }

    throw new RuleException(
      RuleException::NO_SUCH_RULE,
      ['rule' => static::class . "::{$name}"]
    );
  }

  /**
   * same as GREATER, but treats value as a timestring.
   *
   * @param mixed                              $value    the value to test
   * @param int|float|string|DateTimeInterface $compare  the time to compare against
   * @throws RuleException                               if comparison is not a time value
   * @return bool                                        true if validation succeeds; false otherwise
   */
  public static function after($value, $compare) : bool {
    $compareDT = Value::filter($compare, Value::DATETIME);
    if (! $compareDT) {
      throw new RuleException(RuleException::INVALID_TIME_VALUE, ['time' => $compare]);
    }

    $valueDT = Value::filter($value, Value::DATETIME);
    return $valueDT && $valueDT > $compareDT;
  }

  /**
   * always passes.
   *
   * @return bool  true
   */
  public static function always() : bool {
    return true;
  }

  /**
   * same as LESS, but treats value as a timestring.
   *
   * @param mixed $value    the value to test
   * @param mixed $compare  the time to compare against
   * @throws RuleException  if comparison is not a time value
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function before($value, $compare) : bool {
    $compareDT = Value::filter($compare, Value::DATETIME);
    if (! $compareDT) {
      throw new RuleException(RuleException::INVALID_TIME_VALUE, ['time' => $compare]);
    }

    $valueDT = Value::filter($value, Value::DATETIME);
    return $valueDT && $valueDT < $compareDT;
  }

  /**
   * passes if min < value < max.
   *
   * @param mixed            $value  the value to test
   * @param int|float|string $min    minimum value
   * @param int|float|string $min    minimum value
   * @return bool                    true if validation succeeds; false otherwise
   */
  public static function between($value, $min, $max) : bool {
    Value::hint('min', $min, Value::INT, Value::FLOAT, Value::STRING);
    Value::hint('max', $max, Value::INT, Value::FLOAT, Value::STRING);

    return Value::is($value, Value::INT, Value::FLOAT, Value::STRING) &&
      ($min < $value && $value < $max);
  }

  /**
   * same as FROM, but converts values to string and checks byte length.
   * values which cannot be converted to string fail.
   *
   * @param mixed            $value  the value to test
   * @param int|float|string $min    minimum value
   * @param int|float|string $min    minimum value
   * @return bool                    true if validation succeeds; false otherwise
   */
  public static function byteLength($value, int $min, int $max = PHP_INT_MAX) : bool {
    $value = Value::filter($value, Value::STRING);
    return $value !== null && self::from(strlen($value), $min, $max);
  }

  /**
   * same as BYTELENGTH, but counts characters.
   *
   * @param mixed            $value  the value to test
   * @param int|float|string $min    minimum value
   * @param int|float|string $min    minimum value
   * @return bool                    true if validation succeeds; false otherwise
   */
  public static function charLength($value, int $min, int $max = PHP_INT_MAX) : bool {
    $value = Value::filter($value, Value::STRING);
    return $value !== null && self::from(mb_strlen($value), $min, $max);
  }

  /**
   * passes if value is iterable, and all items are of the same type or class/interface.
   *
   * @param mixed  $value  the value to test
   * @param string $of     the collection type (inferred from first item if omitted)
   * $return bool          true if validation succeeds; false otherwise
   */
  public static function collection($value, string $of = null) : bool {
    if (! is_iterable($value)) {
      return false;
    }
    $of = $of ?? Value::type(reset($value));

    foreach ($value as $item) {
      if (! Value::is($item, $of)) {
        return false;
      }
    }
    return true;
  }

  /**
   * same as FROM, but treats value as a timestring.
   *
   * @param mixed                        $value  the value to test
   * @param int|string|DateTimeInterface $start  the starting time to compare against
   * @param int|string|DateTimeInterface $end    the ending time to compare against
   * @throws RuleException                       if start or end are not time values
   * @return bool                                true if validation succeeds; false otherwise
   */
  public static function during($value, $start, $end) : bool {
    $startDT = Value::filter($start, Value::DATETIME);
    if (! $start) {
      throw new RuleException(RuleException::INVALID_TIME_VALUE, ['time' => $start]);
    }
    $endDT = Value::filter($end, Value::DATETIME);
    if (! $end) {
      throw new RuleException(RuleException::INVALID_TIME_VALUE, ['time' => $end]);
    }

    $valueDT = Value::filter($value, Value::DATETIME);
    return $valueDT && $startDT < $valueDT && $valueDT < $endDT;
  }

  /**
   * passes if the value is a well-formed email address.
   *
   * this method works with internationalized email addresses,
   * but otherwise has the same limitations as FILTER_VALIDATE_EMAIL.
   *
   * the only way to validate an email address is to send an email to it, and get a reply back.
   *
   * @param mixed $value  the value to test
   * @return bool         true if validation succeeds; false otherwise
   */
  public static function email($value) : bool {
    return filter_var(
      preg_replace_callback(
        '([^@]+$)',
        function ($part) { return idn_to_ascii($part[0], 0, INTL_IDNA_VARIANT_UTS46); },
        $value
      ),
      FILTER_VALIDATE_EMAIL,
      FILTER_FLAG_EMAIL_UNICODE
    ) !== false;
  }

  /**
   * passes if the value is equal to the test value.
   *
   * if compared to an object with an equals() method (e.g., a Modelable or DS\Hashable),
   * comparison will be performed using that method.
   *
   * @param mixed $value    the value to test
   * @param mixed $compare  the value to test against
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function equals($value, $compare) : bool {
    if (
      (is_string($compare) || is_object($compare)) &&
      ($value instanceof $compare) &&
      method_exists($compare, 'equals')
    ) {
      return $compare->equals($value);
    }

    return $value === $compare;
  }

  /**
   * passes if min <= value <= max.
   *
   * @param mixed            $value  the value to test
   * @param int|float|string $min    minimum value
   * @param int|float|string $min    minimum value
   * @return bool                    true if validation succeeds; false otherwise
   */
  public static function from($value, $min, $max) : bool {
    Value::hint('min', $min, Value::INT, Value::FLOAT, Value::STRING);
    Value::hint('max', $max, Value::INT, Value::FLOAT, Value::STRING);

    return $min <= $value && $value <= $max;
  }

  /**
   * passes if value > test value.
   *
   * @param mixed            $value    the value to test
   * @param int|float|string $compare  the value to test against
   * @return bool                      true if validation succeeds; false otherwise
   */
  public static function greater($value, $compare) : bool {
    Value::hint('compare', $compare, Value::INT, Value::FLOAT, Value::STRING);

    return $value > $compare;
  }

  /**
   * passes if value is of given class, type, or pseudotype.
   *
   * @param mixed  $value  the value to test
   * @param string $types  type/pseudotype/classname(s)
   * @return bool          true if validation succeeds; false otherwise
   */
  public static function is($value, string ...$types) : bool {
    return Value::is($value, ...$types);
  }

  /**
   * passes if value < test value.
   *
   * @param mixed            $value    the value to test
   * @param int|float|string $compare  the value to test against
   * @return bool                      true if validation succeeds; false otherwise
   */
  public static function less($value, $compare) : bool {
    Value::hint('compare', $compare, Value::INT, Value::FLOAT, Value::STRING);

    return $value < $compare;
  }

  /**
   * passes if the value is a string, and matches the given regular expression.
   *
   * @param mixed      $value  the value to test
   * @param string|PRO $regex  regular expression, as a string or PRO instance
   * @throws RuleException     if regex is invalid
   * @return bool              true if validation succeeds; false otherwise
   */
  public static function matches($value, $regex) : bool {
    Value::hint('regex', $regex, PRO::class, Value::STRING);

    if (! is_string($value)) {
      return false;
    }

    if ($regex instanceof PRO) {
      return $regex->matches($value);
    }

    $matches = @preg_match($regex, $value);
    if ($matches === false) {
      throw new RuleException(RuleException::INVALID_REGEX, error_get_last());
    }
    return $matches === 1;
  }

  /**
   * always fails.
   *
   * @return bool  false
   */
  public static function never() : bool {
    return false;
  }

  /**
   * handles negations of other rules.
   *
   * @param callable $rule        the rule to negate
   * @param mixed    …$arguments  arguments to invoke the rule with
   * @throws RuleException        if invoking the rule fails
   * @return bool                 true if validation succeeds; false otherwise
   */
  public function not(callable $rule, ...$arguments) : bool {
    try {
      return ! $rule(...$arguments);
    } catch (Throwable $e) {
      throw new RuleException(RuleException::BAD_CALL_RIPLEY, $e);
    }
  }

  /**
   * passes if value matches one of the given values. comparison is strict.
   *
   * @param mixed $value    the value to test
   * @param array $compare  set of valid values
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function oneOf($value, array $compare) : bool {
    return in_array($value, $compare, true);
  }


  public function __construct(callable $rule, ...$defaultArguments) {
    $this->_rule = $rule;
    $this->_defaultArguments = $defaultArguments;
  }

  public function __invoke(...$arguments) : bool {
    ($this->_rule)(...$arguments + $this->_defaultArguments);
  }
}
