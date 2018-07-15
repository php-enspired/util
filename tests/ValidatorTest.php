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

use at\util\tests\TestCase;

class ValidatorTest extends TestCase {

  /**
   * @covers Validator::all
   * @dataProvider _rulesetProvider
   */
  public function testAll(array $ruleset, int $expected) {
    $this->assertEquals(Validator::all(...$ruleset), $expected === count($ruleset));
  }


  /**
   * @covers Validator::any
   * @dataProvider _rulesetProvider
   */
  public function testAny(array $ruleset, int $expected) {
    $this->assertEquals(Validator::any(...$ruleset), $expected > 0);
  }

  /**
   * @covers Validator::atLeast
   * @dataProvider _rulesetProvider
   */
  public function testAtLeast(array $ruleset, int $expected) {
    foreach ([0, 1, 2, 3, 4, 5] as $min) {
      $this->assertEquals(Validator::atLeast($min, ...$ruleset), $expected >= $min);
    }
  }

  /**
   * @covers Validator::atMost
   * @dataProvider _rulesetProvider
   */
  public function testAtMost(array $ruleset, int $expected) {
    foreach ([0, 1, 2, 3, 4, 5] as $max) {
      $this->assertEquals(Validator::atMost($max, ...$ruleset), $expected <= $max);
    }
  }

  /**
   * @covers Validator::if
   * @dataProvider _rulesetProvider
   */
  public function testIf(array $ruleset, int $expected) {
    $pass = [function () { return true; }];
    $fail = [function () { return false; }];
    $count = count($ruleset);

    $this->assertTrue(Validator::if($fail, ...$ruleset));
    $this->assertEquals(Validator::if($pass, ...$ruleset), $expected === $count);
    $this->assertTrue(Validator::if(false, ...$ruleset));
    $this->assertEquals(Validator::if(true, ...$ruleset), $expected === $count);
  }

  /**
   * @covers Validator::none
   * @dataProvider _rulesetProvider
   */
  public function testNone(array $ruleset, int $expected) {
    $this->assertEquals(Validator::none(...$ruleset), $expected === 0);
  }

  /**
   * @covers Validator::one
   * @dataProvider _rulesetProvider
   */
  public function testOne(array $ruleset, int $expected) {
    $this->assertEquals(Validator::one(...$ruleset), $expected === 1);
  }

  /**
   * @covers Validator::unless
   * @dataProvider _rulesetProvider
   */
  public function testUnless(array $ruleset, int $expected) {
    $pass = [function () { return true; }];
    $fail = [function () { return false; }];
    $count = count($ruleset);

    $this->assertTrue(Validator::unless($pass, ...$ruleset));
    $this->assertEquals(Validator::unless($fail, ...$ruleset), $expected === $count);
    $this->assertTrue(Validator::unless(true, ...$ruleset));
    $this->assertEquals(Validator::unless(false, ...$ruleset), $expected === $count);
  }


  /**
   * @covers Validator::after
   * @dataProvider _datetimeableProvider
   */
  public function testAfter(array $times, int $expected) {
    list($subject, $compare) = $times;
    $this->assertEquals(Validator::after($subject, $compare), $expected > 0);
  }

  /**
   * @covers Validator::always
   * @dataProvider _comparableProvider
   */
  public function testAlways(array $values, int $expected) {
    $this->assertTrue(Validator::always(reset($values)));
  }

  /**
   * @covers Validator::before
   * @dataProvider _datetimeableProvider
   */
  public function testBefore(array $times, int $expected) {
    list($subject, $compare) = $times;
    $this->assertEquals(Validator::before($subject, $compare), $expected < 0);
  }

  /**
   * @covers Validator::between
   * @dataProvider _comparableProvider
   */
  public function testBetween(array $values, int $expected) {
    list($value, $min, $max) = $values;
    $this->assertEquals(Validator::between($value, $min, $max), $expected === 1);
  }

  /**
   * @covers Validator::byteLength
   */
  public function testByteLength() {
    $min = 2;
    $max = 5;

    $this->assertTrue(Validator::byteLength('foo', $min, $max));
    $this->assertFalse(Validator::byteLength('bazinga', $min, $max));
    $this->assertTrue(Validator::byteLength(101, $min, $max));
    $this->assertFalse(Validator::byteLength(3, $min, $max));
  }

  /**
   * @covers Validator::collection
   * @dataProvider _collectionProvider
   */
  public function testCollection($value, $of, bool $expected) {
    $this->assertEquals(Validator::collection($value, $of), $expected);
  }

  /**
   * @covers Validator::during
   * @dataProvider _datetimeableProvider
   */
  public function testDuring(array $times, int $expected) {
    list($subject, $start, $end) = $times;
    $this->assertEquals(
      in_array($expected, [0, 1, 2]),
      Validator::during($subject, $start, $end)
    );
  }

  /**
   * @covers Validator::email
   * @dataProvider _emailProvider
   */
  public function testEmail($value, bool $expected) {
    if (! function_exists('idn_to_ascii')) {
      $this->markTestSkipped('intl must be installed');
    }

    $this->assertEquals(Validator::email($value), $expected);
  }

  /**
   * @covers Validator::equals
   * @dataProvider _comparableProvider
   */
  public function testEquals(array $values, int $expected) {
    list($value, $compare) = $values;
    $this->assertEquals(Validator::equals($value, $compare), $expected === 0);
  }

  /**
   * @covers Validator::from
   * @dataProvider _comparableProvider
   */
  public function testFrom(array $values, int $expected) {
    list($value, $min, $max) = $values;
    $this->assertEquals(
      Validator::from($value, $min, $max),
      in_array($expected, [0, 1, 2])
    );
  }

  /**
   * @covers Validator::greater
   * @dataProvider _comparableProvider
   */
  public function testGreater(array $values, int $expected) {
    list($value, $compare) = $values;
    $this->assertEquals(Validator::greater($value, $compare), $expected > 0);
  }

  /**
   * @covers Validator::less
   * @dataProvider _comparableProvider
   */
  public function testLess(array $values, int $expected) {
    list($value, $compare) = $values;
    $this->assertEquals(Validator::less($value, $compare), $expected === -1);
  }

  /**
   * @todo
   * @covers Validator::matches
   * @dataProvider _matchProvider
   */
  public function testMatches($value, $regex, bool $expected) {
    $this->assertEquals(Validator::matches($value, $regex), $expected);
  }

  /**
   * @covers Validator::never
   * @dataProvider _comparableProvider
   */
  public function testNever(array $values, int $expected) {
    $this->assertFalse(Validator::never(reset($values)));
  }


  /**
   * @return array[] {
   *    @type mixed       $0  value to test
   *    @type string|null $1  the collection type to test against
   *    @type bool        $2  is the value a valid collection?
   *  }
   */
  public function _collectionProvider() : array {
    $A = new A;
    $AA = new AA;
    $IA = new IA;
    $IAA = new IAA;

    return [
      [[1, 2, 3, 4], 'integer', true],
      [[1, 2, 3, 4], null, true],
      [[1, 2, 3, 'z'], 'integer', false],
      [[1, 2, 3, 'z'], null, false],
      [[true, false], 'boolean', true],
      [[true, false], null, true],
      [[true, false, null], null, false],
      [[$A, $AA], A::class, true],
      [[$A, $AA], null, true],
      [[$IA, $IAA], I::class, true],
      [[$IA, $IAA], null, true],
      [[$A, $IAA], I::class, false],
      [[$A, $IAA], null, true],
      [new \stdClass, null, false],
      ['foo', null, false]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed[] $0  values: [0] subject, [1] min, and [2] max
   *    @type int     $1  indicates how values are expected to compare:
   *                      -1 if $0[0] < $0[1]
   *                       0 if $0[0] = $0[1]
   *                       1 if $0[0] > $0[1]
   *                       2 if $0[0] = $0[2]
   *                       3 if $0[0] > $0[2]
   *  }
   */
  public function _comparableProvider() : array {
    return [
      [[1, 2, 3], -1],
      [[1, 1, 3], 0],
      [[2, 1, 3], 1],
      [[3, 2, 3], 2],
      [[3, 1, 2], 3],

      [[1.5, 2.5, 3.5], -1],
      [[1.5, 1.5, 3.5], 0],
      [[2.5, 1.5, 3.5], 1],
      [[3.5, 2.5, 3.5], 2],
      [[3.5, 1.5, 2.5], 3],

      [['a', 'b', 'c'], -1],
      [['a', 'a', 'c'], 0],
      [['b', 'a', 'c'], 1],
      [['c', 'b', 'c'], 2],
      [['c', 'a', 'b'], 3]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed[] $0  time values: [0] subject, [1] min, and [2] max
   *    @type int     $1  indicates how values are expected to compare:
   *                      -1 if $0[0] < $0[1]
   *                       0 if $0[0] = $0[1]
   *                       1 if $0[0] > $0[1]
   *                       2 if $0[0] = $0[2]
   *                       3 if $0[0] > $0[2]
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
      [[$max, $min, $max], 2],
      [[$max, $min, $mid], 3],

      [[$minDT, $midDT, $maxDT], -1],
      [[$minDT, $minDT, $maxDT], 0],
      [[$midDT, $minDT, $maxDT], 1],
      [[$maxDT, $minDT, $maxDT], 2],
      [[$maxDT, $minDT, $midDT], 3],

      [[(int) $minDT->format('U'), (int) $midDT->format('U'), (int) $maxDT->format('U')], -1],
      [[(int) $minDT->format('U'), (int) $minDT->format('U'), (int) $maxDT->format('U')], 0],
      [[(int) $midDT->format('U'), (int) $minDT->format('U'), (int) $maxDT->format('U')], 1],
      [[(int) $maxDT->format('U'), (int) $minDT->format('U'), (int) $maxDT->format('U')], 2],
      [[(int) $maxDT->format('U'), (int) $minDT->format('U'), (int) $midDT->format('U')], 3],

      [[$minDT->format('r'), $midDT->format('r'), $maxDT->format('r')], -1],
      [[$minDT->format('r'), $minDT->format('r'), $maxDT->format('r')], 0],
      [[$midDT->format('r'), $minDT->format('r'), $maxDT->format('r')], 1],
      [[$maxDT->format('r'), $minDT->format('r'), $maxDT->format('r')], 2],
      [[$maxDT->format('r'), $minDT->format('r'), $midDT->format('r')], 3],

      [[$min, $midDT, (int) $maxDT->format('U')], -1],
      [[$minDT, (int) $minDT->format('U'), $maxDT->format('r')], 0],
      [[(int) $midDT->format('U'), $minDT->format('r'), $max], 1],
      [[$maxDT->format('r'), $min, (int) $maxDT->format('U')], 2],
      [[$maxDT->format('r'), $min, (int) $midDT->format('U')], 3]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed $0  the value to test
   *    @type bool  $1  whether the value should validate as an email address
   *  }
   */
  public function _emailProvider() : array {
    return [
      ['用户@例子.广告', true],
      ['उपयोगकर्ता@उदाहरण.कॉम', true],
      ['юзер@екзампл.ком', true],
      ['θσερ@εχαμπλε.ψομ', true],
      ['Dörte@Sörensen.example.com', true],
      ['аджай@экзампл.рус', true],
      ['me@localhost', false],
      ['singleword', false],
      ['@@.com', false],
      [12345, false]
    ];
  }

  /**
   * @return array[] {
   *    @type mixed  $0  a subject value
   *    @type string $1  a regular expression
   *    @type bool   $2  whether a match is expected
   *  }
   */
  public function _matchProvider() : array {
    return [
      ['foo', '(a)', false],
      ['bar', '(a)', true],
      [12345, '(a)', false]
    ];
  }

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
}

// stubs for test cases
if (! class_exists(A::class)) {
  class A {}
}

if (! class_exists(AA::class)) {
  class AA extends A {}
}

if (! interface_exists(I::class)) {
  interface I {}
}

if (! class_exists(IA::class)) {
  class IA extends A implements I {}
}

if (! class_exists(IAA::class)) {
  class IAA extends IA {}
}
