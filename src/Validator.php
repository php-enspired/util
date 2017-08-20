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
  ValidatorException,
  Vars
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
class Validator {

  /**
   * flags for invoking rulesets.
   *
   * @type int RULESET_RETURN_ON_FAILURE  stop invoking rules early if one fails.
   * @type int RULESET_RETURN_ON_PASS     stop invoking rules early if one passes.
   * @type int RULESET_TEST_ALL           invoke all rules.
   */
  const RULESET_RETURN_ON_FAILURE = 1;
  const RULESET_RETURN_ON_PASS = (1<<1);
  const RULESET_TEST_ALL = 0;

  # callable aliases

  /**
   * "ruleset" rules (conditional structures for other rules).
   *
   * @type callable ALL       passes if all rules pass.
   * @type callable ANY       passes if any rule passes.
   * @type callable AT_LEAST  passes if at least N rules pass.
   * @type callable AT_MOST   passes if at most N rules pass.
   * @type callable EXACTLY   passes if exactly N rules pass.
   * @type callable IF        passes if the first rule fails, or if all other rules pass.
   * @type callable NONE      EXACTLY zero.
   * @type callable ONE       EXACTLY one.
   * @type callable UNLESS    passes if the first rule passes, or if all other rules pass.
   */
  const ALL = [self::class, 'all'];
  const ANY = [self::class, 'any'];
  const AT_LEAST = [self::class, 'atLeast'];
  const AT_MOST = [self::class, 'atMost'];
  const IF = [self::class, 'if'];
  const NONE = [self::class, 'none'];
  const ONE = [self::class, 'one'];
  const UNLESS = [self::class, 'unless'];

  /**
   * individual rules (validators for specific values).
   *
   * @type callable AFTER       same as GREATER, but treats value as a timestring.
   * @type callable ALWAYS      always passes.
   * @type callable BEFORE      same as LESS, but treats value as a timestring.
   * @type callable BETWEEN     passes if min < value < max.
   * @type callable BYTELENGTH  same as NOT_FROM, but checks byte length of a string value.
   * @type callable COLLECTION  passes if value is iterable, and all items are of the same type/class.
   * @type callable DURING      same as FROM, but treats value as a timestring.
   * @type callable EQUALS      passes if value is equal to test value.
   * @type callable FROM        passes if min <= value <= max.
   * @type callable GREATER     passes if value > test value.
   * @type callable IS_TYPE     passes if value is of given class, type, or pseudotype.
   * @type callable LESS        passes if value < test value.
   * @type callable MATCHES     passes if value matches given regular expression.
   * @type callable NEVER       always fails.
   * @type callable SIZE        same as FROM, but checks byte length when given a string value.
   * @type callable ONE_OF      passes if value is one of given values.
   */
  const AFTER = [self::class, 'after'];
  const ALWAYS = [self::class, 'always'];
  const BEFORE = [self::class, 'before'];
  const BETWEEN = [self::class, 'between'];
  const BYTELENGTH = [self::class, 'byteLength'];
  const COLLECTION = [self::class, 'collection'];
  const DURING = [self::class, 'during'];
  const EQUALS = [self::class, 'equals'];
  const FROM = [self::class, 'from'];
  const GREATER = [self::class, 'greater'];
  const IS_TYPE = [self::class, 'isType'];
  const LESS = [self::class, 'less'];
  const MATCHES = [self::class, 'matches'];
  const NEVER = [self::class, 'never'];
  const ONE_OF = [self::class, 'oneOf'];

  /**
   * negations (same as rules above, but opposite result).
   *
   * prefer using NOT where possible, as other NOT_* methods eventually invoke it anyway.
   *
   * @type callable NOT             negates another rule.
   * @type callable NOT_AFTER       same as NOT_GREATER, but treats value as a timestring.
   * @type callable NOT_BEFORE      same as NOT_LESS, but treats value as a timestring.
   * @type callable NOT_BETWEEN     fails if min < value < max.
   * @type callable NOT_DURING      same as NOT_FROM, but treats value as a timestring.
   * @type callable NOT_EQUALS      fails if value is equal to test value.
   * @type callable NOT_FROM        fails if min <= value <= max.
   * @type callable NOT_GREATER     fails if value > test value.
   * @type callable NOT_IS_TYPE     fails if value is of given class, type, or pseudotype.
   * @type callable NOT_LESS        fails if value < test value.
   * @type callable NOT_MATCHES     fails if value matches given regular expression.
   * @type callable NOT_ONE_OF      fails if value is one of given values.
   */
  const NOT = [self::class, 'not'];
  const NOT_AFTER = [self::class, 'notAfter'];
  const NOT_BEFORE = [self::class, 'notBefore'];
  const NOT_BETWEEN = [self::class, 'notBetween'];
  const NOT_DURING = [self::class, 'notDuring'];
  const NOT_EQUALS = [self::class, 'notEquals'];
  const NOT_FROM = [self::class, 'notFrom'];
  const NOT_GREATER = [self::class, 'notGreater'];
  const NOT_IS_TYPE = [self::class, 'notIsType'];
  const NOT_LESS = [self::class, 'notLess'];
  const NOT_MATCHES = [self::class, 'notMatches'];
  const NOT_ONE_OF = [self::class, 'notOneOf'];

  /**
   * {@inheritDoc}
   * @see http://php.net/__callStatic
   *
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function __callStatic($name, $arguments) {
    if (strpos($name, 'not') === 0) {
      $rule = substr($name, 3);
      if (method_exists(static::class, $rule)) {
        return self::not([static::class, $rule], ...$arguments);
      }
    }

    throw new ValidatorException(
      ValidatorException::NO_SUCH_RULE,
      ['rule' => static::class . "::{$name}"]
    );
  }

  /**
   * same as GREATER, but treats value as a timestring.
   *
   * @param mixed                        $value    the value to test
   * @param int|string|DateTimeInterface $compare  the time to compare against
   * @throws ValidatorException                    if comparison is not a time value
   * @return bool                                  true if validation succeeds; false otherwise
   */
  public static function after($value, $compare) : bool {
    $after = Vars::filter($compare, Vars::DATETIME);
    if (! $after) {
      throw new ValidatorException(
        ValidatorException::INVALID_TIME_VALUE,
        ['time' => $compare]
      );
    }
    $value = Vars::filter($value, Vars::DATETIME);
    return ($value !== null) ? self::greater($value, $after) : false;
  }

  /**
   * passes if none of the given rules fail.
   *
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function all(array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, ...$rules) === count($rules);
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
   * passes if any of the given rules pass.
   *
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function any(array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_RETURN_ON_PASS, ...$rules) > 0;
  }

  /**
   * passes if at least N rules pass.
   *
   * @param int   $least    minimum number of rules that must pass
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function atLeast(int $least, array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, ...$rules) >= $least;
  }

  /**
   * passes if at most N rules pass.
   *
   * @param int   $most     maximum number of rules that may pass
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function atMost(int $most, array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, ...$rules) <= $most;
  }

  /**
   * same as LESS, but treats value as a timestring.
   *
   * @param mixed                        $value    the value to test
   * @param int|string|DateTimeInterface $compare  the time to compare against
   * @throws ValidatorException                    if comparison is not a time value
   * @return bool                                  true if validation succeeds; false otherwise
   */
  public static function before($value, $compare) : bool {
    $before = Vars::filter($compare, Vars::DATETIME);
    if (! $before) {
      throw new ValidatorException(
        ValidatorException::INVALID_TIME_VALUE,
        ['time' => $compare]
      );
    }
    $value = Vars::filter($value, Vars::DATETIME);
    return ($value !== null) ? self::less($value, $before) : false;
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
    Vars::typeHint(
      'min',
      $min,
      DateTimeInterface::class,
      Vars::INT,
      Vars::FLOAT,
      Vars::STRING
    );
    Vars::typeHint(
      'max',
      $max,
      DateTimeInterface::class,
      Vars::INT,
      Vars::FLOAT,
      Vars::STRING
    );

    return $min < $value && $value < $max;
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
    if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
      return self::from(strlen(strval($value)), $min, $max);
    }
    return false;
  }

  /**
   * passes if value is iterable, and all items are of the same type or class/interface.
   *
   * @param mixed  $value  the value to test
   * @param string $of     the collection type (inferred from first item if omitted)
   * $return bool          true if validation succeeds; false otherwise
   */
  public static function collection($value, string $of = null) : bool {
    if (! Vars::isIterable($value)) {
      return false;
    }

    if (empty($of)) {
      $first = reset($value);
      $of = is_object($first) ? $first : Vars::type($first);
    }

    foreach ($value as $item) {
      if (! ($item instanceof $of || Vars::type($item) === $of)) {
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
   * @throws ValidatorException                  if start or end are not time values
   * @return bool                                true if validation succeeds; false otherwise
   */
  public static function during($value, $start, $end) : bool {
    $dtStart = Vars::filter($start, Vars::DATETIME);
    if (! $dtStart) {
      throw new ValidatorException(ValidatorException::INVALID_TIME_VALUE, ['time' => $start]);
    }
    $dtEnd = Vars::filter($end, Vars::DATETIME);
    if (! $dtEnd) {
      throw new ValidatorException(ValidatorException::INVALID_TIME_VALUE, ['time' => $end]);
    }

    $value = Vars::filter($value, Vars::DATETIME);
    return ($value !== null) ? self::from($value, $dtStart, $dtEnd) : false;
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
        '([^@]+)',
        function ($part) { return idn_to_ascii($part[0], 0, INTL_IDNA_VARIANT_UTS46); },
        $value
      ),
      FILTER_VALIDATE_EMAIL
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
   * passes if exactly N rules pass.
   *
   * @param int   $exactly  number of tests which must pass
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function exactly(int $exactly, array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, ...$rules) === $exactly;
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
    Vars::typeHint(
      'min',
      $min,
      DateTimeInterface::class,
      Vars::INT,
      Vars::FLOAT,
      Vars::STRING
    );
    Vars::typeHint(
      'max',
      $max,
      DateTimeInterface::class,
      Vars::INT,
      Vars::FLOAT,
      Vars::STRING
    );

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
    Vars::typeHint(
      'compare',
      $compare,
      DateTimeInterface::class,
      Vars::INT,
      Vars::FLOAT,
      Vars::STRING
    );

    return $value > $compare;
  }

  /**
   * passes if the first rule fails, or if all other rules pass.
   *
   * @param bool|array $if       condition
   * @param array      ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function if($if, array ...$rules) : bool {
    if (is_array($if) && is_callable(reset($if))) {
      $if = (bool) self::_invokeRuleset(self::RULESET_TEST_ALL, $if);
    }
    if (! is_bool($if)) {
      throw new ValidatorException(
        ValidatorException::INVALID_CONDITION,
        ['if' => $if, 'type' => Vars::type($if)]
      );
    }

    return ! $if ||
      self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, ...$rules) === count($rules);
  }

  /**
   * passes if value is not one of given (pseudo) types.
   * @see Vars::typeCheck()
   *
   * @param mixed  $value     the value to test
   * @param string ...$types  allowed type(s)
   * @return bool             true if validation succeeds; false otherwise
   */
  public static function isType($value, string ...$types) : bool {
    return Vars::typeCheck($value, ...$types);
  }

  /**
   * passes if value < test value.
   *
   * @param mixed            $value    the value to test
   * @param int|float|string $compare  the value to test against
   * @return bool                      true if validation succeeds; false otherwise
   */
  public static function less($value, $compare) : bool {
    Vars::typeHint(
      'compare',
      $compare,
      DateTimeInterface::class,
      Vars::INT,
      Vars::FLOAT,
      Vars::STRING
    );

    return $value < $compare;
  }

  /**
   * passes if the value is a string, and matches the given regular expression.
   *
   * @param mixed      $value  the value to test
   * @param string|PRO $regex  regular expression, as a string or PRO instance
   * @return bool              true if validation succeeds; false otherwise
   */
  public static function matches($value, $regex) : bool {
    Vars::typeHint('regex', $regex, PRO::class, Vars::STRING);

    if (! is_string($value)) {
      return false;
    }

    if ($regex instanceof PRO) {
      return $regex->matches($value);
    }

    $matches = @preg_match($regex, $value);
    if ($matches === false) {
      throw new ValidatorException(ValidatorException::INVALID_REGEX, error_get_last());
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
   * passes if none of the given rules pass.
   *
   * @param array $rules {
   *    @type array $... {
   *      @type callable $0    rule to invoke
   *      @type mixed    $...  arguments
   *    }
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function none(array ...$rules) : bool {
    return ! self::any(...$rules);
  }

  /**
   * handles negations of other rules.
   *
   * @param callable $rule         the rule to negate
   * @param mixed    …$arguments   arguments to invoke the rule with
   * @throws ValidatorException    if invoking the rule fails
   * @return bool                  true if validation succeeds; false otherwise
   */
  public function not(callable $rule, ...$arguments) : bool {
    return ! $rule(...$arguments);
  }

  /**
   * passes if exactly one of the given rules pass.
   *
   * @param array $rules {
   *    @type array $... {
   *      @type callable $0    rule to invoke
   *      @type mixed    $...  arguments
   *    }
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function one(array ...$rules) : bool {
    return self::exactly(1, ...$rules);
  }

  /**
   * passes if value matches none of the given values. comparison is strict.
   * @see Arrays::contains()
   *
   * @param mixed $value    the value to test
   * @param array $compare  set of valid values
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function oneOf($value, array $compare) : bool {
    return Arrays::contains($value, $compare, true);
  }

  /**
   * passes if the first rule passes, or if all other rules pass.
   *
   * @param bool|array $if       condition
   * @param array      ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function unless($if, array ...$rules) : bool {
    if (is_array($if) && is_callable(reset($if))) {
      $if = self::_invokeRuleset(self::RULESET_TEST_ALL, $if) > 0;
    }
    if (! is_bool($if)) {
      throw new ValidatorException(
        ValidatorException::INVALID_CONDITION,
        ['if' => $if, 'type' => Vars::type($if)]
      );
    }

    return $if ||
      self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, ...$rules) === count($rules);
  }

  /**
   * invokes each of given rules in turn.
   *
   * @param int   $flag     one of the RULESET_* constants
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return int                 count of tests that passed
   */
  protected static function _invokeRuleset(int $flags, array ...$rules) : int {
    try {
      $count = 0;
      foreach ($rules as $arguments) {
        $rule = array_shift($arguments);
        if ($rule(...$arguments)) {
          $count++;
          if ($flags & self::RULESET_RETURN_ON_PASS) {
            break;
          }
        } elseif ($flags & self::RULESET_RETURN_ON_FAILURE) {
          break;
        }
      }
      return $count;
    } catch (\Throwable $e) {
      throw new ValidatorException(ValidatorException::BAD_CALL_RIPLEY, $e);
    }
  }
}
