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

use Throwable;
use at\util\Validator,
    at\util\ValidatorException;
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

  public function testAfter () {}
  public function testAlways () {}
  public function testBefore () {}
  public function testBetween () {}
  public function testDuring () {}
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
      [[$fail, $pass, $pass, $fail], 2],
      [[$pass, $pass, $fail, $fail], 2],
      [[$fail, $pass, $pass, $pass], 3],
      [[$pass, $pass, $pass, $fail], 3],
      [[$pass, $fail, $pass, $pass], 3],
      [[$pass, $pass, $pass, $pass], 4]
    ];
  }
}
