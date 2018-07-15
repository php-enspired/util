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

use Throwable;

use at\util\RuleException;

/**
 * rulesets (rules for composing other rules).
 */
class Ruleset {

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

  /**
   * callable aliases.
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
   * passes if none of the given rules fail.
   *
   * @param array ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function all(array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, ...$rules) === count($rules);
  }

  /**
   * passes if any of the given rules pass.
   *
   * @param array ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function any(array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_RETURN_ON_PASS, ...$rules) > 0;
  }

  /**
   * passes if at least N rules pass.
   *
   * @param int   $least     minimum number of rules that must pass
   * @param array ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function atLeast(int $least, array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, ...$rules) >= $least;
  }

  /**
   * passes if at most N rules pass.
   *
   * @param int   $most      maximum number of rules that may pass
   * @param array ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function atMost(int $most, array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, ...$rules) <= $most;
  }

  /**
   * passes if exactly N rules pass.
   *
   * @param int   $exactly   number of tests which must pass
   * @param array ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function exactly(int $exactly, array ...$rules) : bool {
    return self::_invokeRuleset(self::RULESET_TEST_ALL, ...$rules) === $exactly;
  }

  /**
   * passes if the first rule fails, or if all other rules pass.
   *
   * @param bool|array $if        condition
   * @param array      ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function if($if, array ...$rules) : bool {
    try{
      if (is_array($if)) {
        $if = array_shift($if)(...$if);
      }
      if (! is_bool($if)) {
        throw new RuleException(
          RuleException::INVALID_CONDITION,
          ['if' => $if, 'type' => Value::type($if)]
        );
      }

      return ! $if ||
        self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, ...$rules) === count($rules);
    } catch (RuleException $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new RuleException(RuleException::BAD_CALL_RIPLEY, $e);
    }
  }

  /**
   * passes if none of the given rules pass.
   *
   * @param array $rules  {
   *    @type array $...  {
   *      @type callable $0    rule to invoke
   *      @type mixed    $...  arguments
   *    }
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function none(array ...$rules) : bool {
    return ! self::any(...$rules);
  }

  /**
   * passes if exactly one of the given rules pass.
   *
   * @param array $rules  {
   *    @type array $...  {
   *      @type callable $0    rule to invoke
   *      @type mixed    $...  arguments
   *    }
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function one(array ...$rules) : bool {
    return self::exactly(1, ...$rules);
  }

  /**
   * passes if the first rule passes, or if all other rules pass.
   *
   * @param bool|array $if        condition
   * @param array      ...$rules  {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return bool           true if validation succeeds; false otherwise
   */
  public static function unless($if, array ...$rules) : bool {
    try {
      if (is_array($if)) {
        $if = array_shift($if)(...$if);
      }
      if (! is_bool($if)) {
        throw new RuleException(
          RuleException::INVALID_CONDITION,
          ['if' => $if, 'type' => Value::type($if)]
        );
      }

      return $if ||
        self::_invokeRuleset(self::RULESET_RETURN_ON_FAILURE, ...$rules) === count($rules);
    } catch (RuleException $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new RuleException(RuleException::BAD_CALL_RIPLEY, $e);
    }
  }

  /**
   * invokes each of given rules in turn.
   *
   * @param int   $flag     one of the RULESET_* constants
   * @param array ...$rules {
   *    @type callable $0    rule to invoke
   *    @type mixed    $...  arguments
   *  }
   * @throws RuleException  if invoking a rule fails
   * @return int            count of tests that passed
   */
  protected static function _invokeRuleset(int $flags, array ...$rules) : int {
    try {
      $count = 0;
      foreach ($rules as $rule) {
        if (! ($rule instanceof Rule || $rule instanceof Ruleset)) {
          $rule = new Rule(...$rule);
        }

        if (array_shift($rule)(...$rule)) {
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
      throw new RuleException(RuleException::BAD_CALL_RIPLEY, $e);
    }
  }


  public function __construct(callable $requires = self::ALL, ...$rules) {
    $this->_requires = $requires;
    foreach ($rules as $rule) {
      $this->addRule(...(is_array($rule) ? $rule : [$rule]));
    }
  }

  public function __invoke($value = null) : bool {
    $rules = $this->_rules;
    if ($value !== null) {
      foreach ($rules as $rule) {
        $rule->setValue($value);
      }
    }

    return ($this->_requires)(...$rules);
  }

  public function addRule(callable $rule, ...$arguments) : Ruleset {
    if (! ($rule instanceof Rule || $rule instanceof Ruleset)) {
      $rule = new Rule($rule, ...$arguments);
    }
    $this->_rules[] = $rule;
  }
}
