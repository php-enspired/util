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

use at\PRO\PRO;
use at\util\ {
  ValidatorException,
  VarTools
};

/**
 * common/convenient validator functions. */
class Validator {

  /**
   * flags for invoking rulesets.
   *
   * @type int RULESET_RETURN_ON_FAILURE  stop testing early if a rule fails.
   * @type int RULESET_RETURN_ON_PASS     stop testing early if a rule passes.
   * @type int RULESET_TEST_ALL           invoke all tests.
   */
  const RULESET_RETURN_ON_FAILURE = 1;
  const RULESET_RETURN_ON_PASS = 2;
  const RULESET_TEST_ALL = 0;

  # callable aliases

  /**
   * "ruleset" rules (conditional structures for other rules)
   *
   * @type callable ALL       passes if all rules pass.
   * @type callable ANY       passes if any rule passes.
   * @type callable AT_LEAST  passes if at least N rules pass.
   * @type callable AT_MOST   passes if at most N rules pass.
   * @type callable IF        passes if the first rule fails, or if all other rules pass.
   * @type callable NONE      passes if all rules fail.
   * @type callable ONE       passes if only one rule passes.
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
   * individual rules (validators for specific values)
   *
   * @type callable AFTER    same as GREATER, but treats value as a timestring.
   * @type callable ALWAYS   always passes.
   * @type callable BEFORE   same as LESS, but treats value as a timestring.
   * @type callable BETWEEN  passes if min < value < max.
   * @type callable DURING   same as FROM, but treats value as a timestring.
   * @type callable EQUALS   passes if value is equal to test value.
   * @type callable FROM     passes if min <= value <= max.
   * @type callable GREATER  passes if value > test value.
   * @type callable IN       passes if value is one of given values.
   * @type callable IS_TYPE  passes if value is of given class, type, or pseudotype.
   * @type callable LESS     passes if value < test value.
   * @type callable MATCHES  passes if value matches given regular expression.
   * @type callable NEVER    always fails.
   */
  const AFTER = [self::class, 'after'];
  const ALWAYS = [self::class, 'always'];
  const BEFORE = [self::class, 'before'];
  const BETWEEN = [self::class, 'between'];
  const DURING = [self::class, 'during'];
  const EQUALS = [self::class, 'equals'];
  const FROM = [self::class, 'from'];
  const GREATER = [self::class, 'greater'];
  const IN = [ArrayTools::class, 'contains'];
  const IS_TYPE = [VarTools::class, 'typeCheck'];
  const LESS = [self::class, 'less'];
  const MATCHES = [self::class, 'matches'];
  const NEVER = [self::class, 'never'];

  /**
   * negations (same as rules above, but negated; replace "passes if" with "fails if")
   *
   * @type callable NOT_BETWEEN
   * @type callable NOT_EQUALS
   * @type callable NOT_FROM
   * @type callable NOT_GREATER
   * @type callable NOT_IN
   * @type callable NOT_LESS
   * @type callable NOT_MATCHES
   * @type callable NOT_TYPE
   */
  const NOT_BETWEEN = [self::class, 'notBetween'];
  const NOT_EQUALS = [self::class, 'notEquals'];
  const NOT_FROM = [self::class, 'notFrom'];
  const NOT_GREATER = [self::class, 'notGreater'];
  const NOT_IN = [self::class, 'notIn'];
  const NOT_LESS = [self::class, 'notLess'];
  const NOT_MATCHES = [self::class, 'notMatches'];
  const NOT_TYPE = [self::class, 'type'];

  /**
   * handles negations of other rules.
   * {@inheritDoc}
   * @see http://php.net/__callStatic
   *
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function __callStatic($name, $arguments) {
    if (strpos($name, 'not') === 0) {
      $rule = substr($name, 3);
      if (! method_exists(static::class, $rule)) {
        throw new ValidatorException(
          ValidatorException::NO_SUCH_RULE,
          ['rule' => static::class . "::{$name}"]
        );
      }

      return ! [static::class, $rule](...$arguments);
    }
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
    try {
      $compare = VarTools::to_DateTime($compare);
    } catch (Throwable $e) {
      throw new ValidatorException(ValidatorException::INVALID_TIME_VALUE, $e);
    }

    try {
      $value = VarTools::to_DateTime($value);
    } catch (Throwable $e) {
      return false;
    }

    return self::greater($value, $compare);
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
    return self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, $rules) === count($rules);
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
    return self::_invokeRuleset(self::RULESET_RETURN_ON_PASS, $rules) > 0;
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
    return self::_invokeRuleset(self::RULESET_TEST_ALL, $rules) >= $least;
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
    return self::_invokeRuleset(self::RULESET_TEST_ALL, $rules) <= $most;
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
    try {
      $compare = VarTools::to_DateTime($compare);
    } catch (Throwable $e) {
      throw new ValidatorException(ValidatorException::INVALID_TIME_VALUE, $e);
    }

    try {
      $value = VarTools::to_DateTime($value);
    } catch (Throwable $e) {
      return false;
    }

    return self::less($value, $compare);
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
    VarTools::typeHint(
      'min',
      $min,
      DateTimeInterface::class,
      VarTools::INT,
      VarTools::FLOAT,
      VarTools::STRING
    );
    VarTools::typeHint(
      'max',
      $max,
      DateTimeInterface::class,
      VarTools::INT,
      VarTools::FLOAT,
      VarTools::STRING
    );

    return $min < $value && $value < $max;
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
    try {
      $start = VarTools::to_DateTime($start);
      $end = VarTools::to_DateTime($end);
    } catch (Throwable $e) {
      throw new ValidatorException(ValidatorException::INVALID_TIME_VALUE, $e);
    }

    try {
      $value = VarTools::to_DateTime($value);
    } catch (Throwable $e) {
      return false;
    }

    return self::from($value, $start, $end);
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
    if (($value instanceof $compare) && method_exists($compare, 'equals')) {
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
    VarTools::typeHint(
      'min',
      $min,
      DateTimeInterface::class,
      VarTools::INT,
      VarTools::FLOAT,
      VarTools::STRING
    );
    VarTools::typeHint(
      'max',
      $max,
      DateTimeInterface::class,
      VarTools::INT,
      VarTools::FLOAT,
      VarTools::STRING
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
    VarTools::typeHint(
      'compare',
      $compare,
      DateTimeInterface::class,
      VarTools::INT,
      VarTools::FLOAT,
      VarTools::STRING
    );

    return $value > $compare;
  }

  /**
   * passes if the first rule fails, or if all other rules pass.
   *
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function if(array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, array_shift($rules)) === 0 ||
      self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, $rules) === count($rules);
  }

  /**
   * passes if value < test value.
   *
   * @param mixed            $value    the value to test
   * @param int|float|string $compare  the value to test against
   * @return bool                      true if validation succeeds; false otherwise
   */
  public static function less($value, $compare) : bool {
    VarTools::typeHint(
      'compare',
      $compare,
      DateTimeInterface::class,
      VarTools::INT,
      VarTools::FLOAT,
      VarTools::STRING
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
    VarTools::typeHint('regex', $regex, PRO::class, VarTools::STRING);

    if (! is_string($value)) {
      return false;
    }

    if ($regex instanceof PRO) {
      return $regex->matches($value);
    }

    // using preg_match so as to keep the PRO dependency "soft"
    return preg_match($regex, $value) === 1;
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
   * passes if value matches none of the given values.
   * comparison is strict.
   *
   * @param mixed $value    the value to test
   * @param array $compare  set of valid values
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function notIn($value, array $compare) : bool {
    return ! ArrayTools::contains($value, $compare, true);
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
    return self::_invokeRuleset(self::RULESET_TEST_ALL, $rules) === 1;
  }

  /**
   * passes if the first rule passes, or if all other rules pass.
   *
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws ValidatorException  if invoking a rule fails
   * @return bool                true if validation succeeds; false otherwise
   */
  public static function unless(array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, array_shift($rules)) === 1 ||
      self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, $rules) === count($rules);
  }

  /**
   * invokes each of given rules in turn.
   *
   * @param int   $flags    bitmask of RULESET_* constants
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
