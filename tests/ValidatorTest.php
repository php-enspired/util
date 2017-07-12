<?php
/**
 * @package    at.util.tests
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

namespace at\util\tests;

use Throwable,
    DateTimeImmutable;
use at\util\ {
  Validator,
  ValidatorException
};
use PHPUnit\Framework\TestCase;

/**
 * in progress.
 */
class ValidatorTest extends TestCase {

  /**
   * @covers Validator::all
   * @dataProvider _rulesetProvider
   */
  public function testAll (array $ruleset, int $expected) {
    $this->assertEquals(Validator::all(...$ruleset), $expected === count($ruleset));
  }


  /**
   * @covers Validator::any
   * @dataProvider _rulesetProvider
   */
  public function testAny (array $ruleset, int $expected) {
    $this->assertEquals(Validator::any(...$ruleset), $expected > 0);
  }

  /**
   * @covers Validator::atLeast
   * @dataProvider _rulesetProvider
   */
  public function testAtLeast (array $ruleset, int $expected) {
    foreach ([0, 1, 2, 3, 4, 5] as $min) {
      $this->assertEquals(Validator::atLeast($min, ...$ruleset), $expected >= $min);
    }
  }

  /**
   * @covers Validator::atMost
   * @dataProvider _rulesetProvider
   */
  public function testAtMost (array $ruleset, int $expected) {
    foreach ([0, 1, 2, 3, 4, 5] as $max) {
      $this->assertEquals(Validator::atMost($max, ...$ruleset), $expected <= $max);
    }
  }

  /**
   * @covers Validator::if
   * @dataProvider _rulesetProvider
   */
  public function testIf (array $ruleset, int $expected) {
    $pass = [function () { return true; }];
    $fail = [function () { return false; }];
    $count = count($ruleset);

    $this->assertTrue(Validator::if($fail, ...$ruleset));
    $this->assertEquals(Validator::if($pass, ...$ruleset), $expected === $count);
  }

  /**
   * @covers Validator::none
   * @dataProvider _rulesetProvider
   */
  public function testNone (array $ruleset, int $expected) {
    $this->assertEquals(Validator::none(...$ruleset), $expected === 0);
  }

  /**
   * @covers Validator::one
   * @dataProvider _rulesetProvider
   */
  public function testOne (array $ruleset, int $expected) {
    $this->assertEquals(Validator::one(...$ruleset), $expected === 1);
  }

  /**
   * @covers Validator::unless
   * @dataProvider _rulesetProvider
   */
  public function testUnless (array $ruleset, int $expected) {
    $pass = [function () { return true; }];
    $fail = [function () { return false; }];
    $count = count($ruleset);

    $this->assertTrue(Validator::unless($pass, ...$ruleset));
    $this->assertEquals(Validator::unless($fail, ...$ruleset), $expected === $count);
  }

  /**
   * @covers Validator::after
   * @dataProvider _datetimeableProvider
   */
  public function testAfter (array $times, int $expected) {
    list($subject, $compare) = $times;
    $this->assertEquals(Validator::after($subject, $compare), $expected > 0);
  }

  public function testAlways () {}

  /**
   * @covers Validator::after
   * @dataProvider _datetimeableProvider
   */
  public function testBefore (array $times, int $expected) {
    list($subject, $compare) = $times;
    $this->assertEquals(Validator::before($subject, $compare), $expected < 0);
  }
  public function testBetween () {}

  /**
   * @covers Validator::after
   * @dataProvider _datetimeableProvider
   */
  public function testDuring (array $times, int $expected) {
    list($subject, $start, $end) = $times;
    $this->assertEquals(
      Validator::during($subject, $start, $end),
      ($expected === 0) || ($expected === 1)
    );
  }
  public function testEquals () {}
  public function testFrom () {}
  public function testGreater () {}
  public function testIn () {}
  public function testIsType () {}
  public function testLess () {}
  public function testMatches () {}
  public function testNever () {}
  public function testSize () {}

  /**
   * @return array[] {
   *    @type callable[] $0  the ruleset to test
   *    @type int        $1  the number of rules in the set that should pass
   *  }
   */
  public function _rulesetProvider() : array {
    $pass = [function () { return true; }];
    $fail = [function () { return false; }];

    return [
      [[$fail, $fail, $fail, $fail], 0],

      [[$fail, $fail, $fail, $pass], 1],
      [[$fail, $fail, $pass, $fail], 1],
      [[$fail, $pass, $fail, $fail], 1],
      [[$pass, $fail, $fail, $fail], 1],

      [[$fail, $fail, $pass, $pass], 2],
      [[$pass, $fail, $fail, $pass], 2],
      [[$pass, $pass, $fail, $fail], 2],
      [[$fail, $pass, $pass, $fail], 2],

      [[$fail, $pass, $pass, $pass], 3],
      [[$pass, $fail, $pass, $pass], 3],
      [[$pass, $pass, $fail, $pass], 3],
      [[$pass, $pass, $pass, $fail], 3],

      [[$pass, $pass, $pass, $pass], 4]
    ];
  }

  const COMPARE_LS_MIN = -1;
  const COMPARE_EQ_MIN = 0;
  const COMPARE_GR_MIN = 1;
  const COMPARE_GR_MAX = 2;

  /**
   * @return array[] {
   *    @type string|int|DateTimeInterface[] $0  time values: [0] subject, [1] min, and [2] max
   *    @type int                            $1  indicates how values are expected to compare:
   *                                             -1 if $0[0] < $0[1]
   *                                              0 if $0[0] = $0[1]
   *                                              1 if $0[0] > $0[1]
   *                                              2 if $0[0] > $0[2]
   *  }
   */
  public function _datetimeableProvider() : array {
    $min = 'yesterday';
    $mid = 'today';
    $max = 'tomorrow';

    $minDT = new DateTimeImmutable($min);
    $midDT = new DateTimeImmutable($mid);
    $maxDT = new DateTimeImmutable($max);

    return [
      [[$min, $mid, $max], -1],
      [[$min, $min, $max], 0],
      [[$mid, $min, $max], 1],
      [[$max, $min, $mid], 2],

      [[$minDT, $midDT, $maxDT], -1],
      [[$minDT, $minDT, $maxDT], 0],
      [[$midDT, $minDT, $maxDT], 1],
      [[$maxDT, $minDT, $midDT], 2],

      [[$minDT->format('U'), $midDT->format('U'), $maxDT->format('U')], -1],
      [[$minDT->format('U'), $minDT->format('U'), $maxDT->format('U')], 0],
      [[$midDT->format('U'), $minDT->format('U'), $maxDT->format('U')], 1],
      [[$maxDT->format('U'), $minDT->format('U'), $midDT->format('U')], 2],

      [[$minDT->format('r'), $midDT->format('r'), $maxDT->format('r')], -1],
      [[$minDT->format('r'), $minDT->format('r'), $maxDT->format('r')], 0],
      [[$midDT->format('r'), $minDT->format('r'), $maxDT->format('r')], 1],
      [[$maxDT->format('r'), $minDT->format('r'), $midDT->format('r')], 2],

      [[$min, $midDT, $maxDT->format('U')], -1],
      [[$minDT, $minDT->format('U'), $maxDT->format('r')], 0],
      [[$midDT->format('U'), $minDT->format('r'), $max], 1],
      [[$maxDT->format('r'), $min, $midDT->format('U')], 2]
    ];
  }
}
