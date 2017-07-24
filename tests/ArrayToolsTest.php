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

use stdClass,
    Throwable;
use at\util\ArrayTools,
    at\util\ArrayToolsException;
use PHPUnit\Framework\TestCase;

class ArrayToolsTest extends TestCase {

  /**
   * various simple arrays for test cases.
   *
   * @var array $_simpleArrayA  a map with keys a, b, c
   * @var array $_simpleArrayB  a map with keys b, c, d
   * @var array $_simpleArray0  a zero-based list
   * @var array $_simpleArray1  a one-based list
   */
  private $_simpleArrayA = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
  private $_simpleArrayB = ['b' => 'B', 'c' => 'C', 'd' => 'D'];

  /**
   * various nested arrays for test cases.
   *
   * @var array[] $nestedAArrayA  a list of maps with keys a, b, c
   * @var array[] $nestedAArrayB  a list of maps with keys b, c, d
   */
  private $_nestedArrayA = [
    ['a' => 'A', 'b' => 'B', 'c' => 'C'],
    ['a' => 'D', 'b' => 'E', 'c' => 'F'],
    ['a' => 'G', 'b' => 'H', 'c' => 'I']
  ];
  private $_nestedArrayB = [
    ['b' => 'B', 'c' => 'C', 'd' => 'D'],
    ['b' => 'E', 'c' => 'F', 'd' => 'G'],
    ['b' => 'H', 'c' => 'I', 'd' => 'J']
  ];

  /**
   * @covers ArrayTools::__callStatic()
   * @dataProvider _arrayFunctionProvider
   */
  public function testArrayFunctions(string $name, array $args, $expected) {
    if ($expected instanceof Throwable) {
      $this->_setExceptionExpectations($expected);
    }

    $actual = ArrayTools::{$name}(...$args);
    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers ArrayTools::categorize()
   */
  public function testCategorize() {
    // success case
    $categorized = ArrayTools::categorize($this->_nestedArrayA, 'a');
    $expectedKeys = array_column($this->_nestedArrayA, 'a');
    $this->assertEquals($expectedKeys, array_keys($categorized));
    foreach ($expectedKeys as $i => $key) {
      $this->assertEquals($this->_nestedArrayA[$i], $categorized[$key][0]);
    }

    // failure case (bad key)
    $this->_setExceptionExpectations(
      new ArrayToolsException(ArrayToolsException::INVALID_CATEGORY_KEY), ['key' => 'x']
    );
    ArrayTools::categorize($this->_nestedArrayA, 'x');
  }

  /**
   * @covers ArrayTools::contains()
   * @dataProvider _containsProvider
   */
  public function testContains(array $subject, $value, $expected) {
    if ($expected) {
      $this->assertTrue(ArrayTools::contains($subject, $value));
      return;
    }
    $this->assertFalse(ArrayTools::contains($subject, $value));
  }

  /**
   * @covers ArrayTools::dig()
   * @dataProvider _digProvider
   */
  public function testDig(array $subject, string $path, array $opts, $expected) {
    if ($expected instanceof Throwable) {
      $this->_setExceptionExpectations($expected);
    }

    $actual = ArrayTools::dig($subject, $path, $opts);
    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers ArrayTools::extend()
   * @dataProvider _extendRecursiveProvider
   */
  public function testExtendRecursive(array $arrays, array $expected) {
    $this->assertEquals($expected, ArrayTools::extend(...$arrays));
  }

  /**
   * @covers ArrayTools::index()
   */
  public function testIndex() {
    $this->assertEquals(
      array_column($this->_nestedArrayA, null, 'a'),
      ArrayTools::index($this->_nestedArrayA, 'a')
    );
  }

  /**
   * @covers ArrayTools::isList()
   */
  public function testIsList() {
    // lists
    $this->assertTrue(ArrayTools::isList([1, 2, 3]));
    $this->assertTrue(ArrayTools::isList([0 => 1, 1 => 2, 2 => 3]));

    // not lists
    $this->assertFalse(ArrayTools::isList([1 => 1, 2 => 2, 3 => 3]));
    $this->assertFalse(ArrayTools::isList(['a' => 1, 'b' => 2, 'c' => 3]));
    $this->assertFalse(ArrayTools::isList([1, 2, 3, 'a' => 4]));
  }

  /**
   * @covers ArrayTools::random()
   * @dataProvider _randomProvider
   */
  public function testRandom(array $subject, int $num, Throwable $expected = null) {
    if ($expected) {
      $this->_setExceptionExpectations($expected);
    }

    $random = ArrayTools::random($subject, $num);
    if ($num === 1) {
      $this->assertTrue(is_string($random) || is_int($random));
      $this->assertArrayHasKey($random, $this->_simpleArrayA);
      return;
    }
    $this->assertTrue(is_array($random));
    $this->assertEquals(array_unique($random), $random);
    foreach ($random as $key) {
      $this->assertArrayHasKey($key, $this->_simpleArrayA);
    }
  }

  /**
   * @covers ArrayTools::rekey()
   */
  public function testRekey() {
    $rekeyed = ArrayTools::rekey(
      $this->_simpleArrayA + ['x' => 'X'],
      function ($k, $v) {
        return in_array($v, $this->_simpleArrayA) ? "{$k}:{$v}" : null;
      }
    );

    $this->assertArrayNotHasKey('x', $rekeyed);
    foreach ($this->_simpleArrayA as $k => $v) {
      $this->assertArrayHasKey("{$k}:{$v}", $rekeyed);
      $this->assertArrayNotHasKey($k, $rekeyed);
    }
  }

  /**
   * provides test cases for testArrayFunctions()
   *
   * @return array[]  test cases
   */
  public function _arrayFunctionProvider() : array {
    $tests = [];
    $simpleA = $this->_simpleArrayA;
    $simpleB = $this->_simpleArrayB;
    $simpleAB = $simpleA + $simpleB;
    $nestedA = $this->_nestedArrayA;
    $nestedB = $this->_nestedArrayB;
    $ucompare = function($a, $b=0) { return $a <=> $b; };

    // array_* functions

    $arrays = [
      'change_key_case' => [[$simpleA, CASE_LOWER], [$simpleA, CASE_UPPER]],
      'chunk' => [[$simpleA, 1], [$simpleA, 1, true]],
      'column' => [
        [$nestedA, key($nestedA)],
        [$nestedA, key($nestedA), key($nestedA)],
        [$nestedA, null, key($nestedA)]
      ],
      'combine' => [[array_keys($simpleA), array_values($simpleA)]],
      'count_values' => [[$simpleA]],
      'diff_assoc' => [[$simpleAB, $simpleB]],
      'diff_key' => [[$simpleAB, $simpleB]],
      'diff_uassoc' => [[$simpleAB, $simpleB, $ucompare]],
      'diff_ukey' => [[$simpleAB, $simpleB, $ucompare]],
      'diff' => [[$simpleAB, $simpleB]],
      'fill_keys' => [[array_keys($simpleA), reset($simpleA)]],
      'fill' => [[1,2,3]],
      'filter' => [
        [$simpleA, function($v) { return $v !== 'a'; }],
        [$simpleA, function($k) { return $k !== 'a'; }, ARRAY_FILTER_USE_KEY],
        [$simpleA, function($v, $k) { return $k !== 'a'; }, ARRAY_FILTER_USE_BOTH]
      ],
      'flip' => [[$simpleA]],
      'intersect_assoc' => [[$simpleA, $simpleB]],
      'intersect_key' => [[$simpleA, $simpleB]],
      'intersect_uassoc' => [[$simpleA, $simpleB, $ucompare]],
      'intersect_ukey' => [[$simpleA, $simpleB, $ucompare]],
      'intersect' => [[$simpleA, $simpleB]],
      'key_exists' => [[key($simpleA), $simpleA]],
      'keys' => [[$simpleA]],
      'map' => [[$ucompare, $simpleA]],
      'merge_recursive' => [[$nestedA, $nestedB]],
      'merge' => [[$simpleA, $simpleB]],
      'pad' => [[$simpleA, 5, 'foo'], [$simpleA, -5, 'foo']],
      'product' => [[[1, 2, 3]]],
      'reduce' => [[$simpleA, function($c, $v) { return "{$c}:{$v}"; }]],
      'replace_recursive' => [[$nestedA, $nestedB]],
      'replace' => [[$simpleA, $simpleB]],
      'reverse' => [[$simpleA]],
      'search' => [[reset($simpleA), $simpleA], [reset($simpleA), $simpleA, true]],
      'slice' => [
        [$simpleA, 1],
        [$simpleA, 1, 1],
        [$simpleA, 1, -1],
        [$simpleA, 1, 1, true]
      ],
      'sum' => [[[1, 2, 3]]],
      'udiff_assoc' => [[$simpleA, $simpleB, $ucompare]],
      'udiff_uassoc' => [[$simpleA, $simpleB, $ucompare, $ucompare]],
      'udiff' => [[$simpleA, $simpleB, $ucompare]],
      'uintersect_assoc' => [[$simpleA, $simpleB, $ucompare]],
      'uintersect_uassoc' => [[$simpleA, $simpleB, $ucompare, $ucompare]],
      'uintersect' => [[$simpleA, $simpleB, $ucompare]],
      'unique' => [[[1, 1, 2, 3, 5, 5]]],
      'values' => [[$simpleA]]
    ];
    foreach ($arrays as $func => $cases) {
      foreach ($cases as $i => $args) {
        $array_func = "array_{$func}";
        $tests["{$func}:{$i}"] = [$func, $args, $array_func(...$args)];
      }
    }

    // sorting functions
    $unsorted = $simpleA;
    while ($unsorted === $simpleA) {
      shuffle($unsorted);
    }

    $flagSorts = ['arsort', 'asort', 'krsort', 'ksort', 'rsort', 'sort'];
    $flags = [
      SORT_REGULAR,
      SORT_NUMERIC,
      SORT_STRING,
      SORT_STRING|SORT_FLAG_CASE,
      SORT_LOCALE_STRING,
      SORT_NATURAL,
      SORT_NATURAL|SORT_FLAG_CASE
    ];
    foreach ($flagSorts as $sort) {
      foreach ($flags as $flag) {
        $sorted = $unsorted;
        $sort($sorted, $flag);
        $tests["{$sort}:{$flag}"] = [$sort, [$unsorted, $flag], $sorted];
      }
    }

    $userSorts = ['uasort', 'uksort', 'usort'];
    foreach ($userSorts as $sort) {
      $sorted = $unsorted;
      $sort($sorted, $ucompare);
      $tests[$sort] = [$sort, [$unsorted, $ucompare], $sorted];
    }

    $naturalSorts = ['natcasesort', 'natsort'];
    foreach ($naturalSorts as $sort) {
      $sorted = $unsorted;
      $sort($sorted);
      $tests[$sort] = [$sort, [$unsorted], $sorted];
    }

    // a nonexistant (unsupported) function
    $tests['invalid'] = [
      'foo',
      [$simpleA],
      new ArrayToolsException(ArrayToolsException::NO_SUCH_METHOD, ['method' => 'foo'])
    ];

    return $tests;
  }

  /**
   * provides test cases for testContains()
   *
   * @return array[]  test cases
   */
  public function _containsProvider() : array {
    $o = new stdClass;
    $subject = [1, 2, 3, ['a', 'b', 'c'], $o];
    return [
      [$subject, 1, true],
      [$subject, ['a', 'b', 'c'], true],
      [$subject, $o, true],
      [$subject, '1', false],
      [$subject, 'foo', false],
      [$subject, [], false],
      [$subject, new stdClass, false]
    ];
  }

  /**
   * provides test cases for testDig()
   *
   * @return array[]  test cases
   */
  public function _digProvider() : array {
    $subject = ['a' => ['b' => ['c' => 'foo']]];
    return [
      [$subject, 'a.b', [], ['c' => 'foo']],
      [$subject, 'a.b.c', [], 'foo'],
      [$subject, 'a/b/c', [], null],
      [$subject, 'a/b/c', [ArrayTools::OPT_DELIM => '/'], 'foo'],
      [$subject, 'a.c', [], null],
      [
        $subject,
        'a.c',
        [ArrayTools::OPT_THROW => true],
        new ArrayToolsException(ArrayToolsException::INVALID_PATH, ['path' => 'a.c'])
      ]
    ];
  }

  /**
   * provides test cases for testExtendRecursive()
   *
   * @return array[]  test cases
   */
  public function _extendRecursiveProvider() : array {
    $subject = [
      'a' => 'A',
      'b' => [1],
      'c' => [1],
      'd' => ['foo' => 'how', 1]
    ];
    $other1 = [
      'a' => 'ehy',
      'b' => 0,
      'c' => [2],
      'd' => ['foo' => 'who', 2]
    ];
    $other2 = [
      'c' => [3],
      'd' => [],
      'x' => 42
    ];

    $expectS1 = [
      'a' => 'ehy',
      'b' => 0,
      'c' => [1, 2],
      'd' => ['foo' => 'who', 1, 2]
    ];
    $expect1S = [
      'a' => 'A',
      'b' => [1],
      'c' => [2, 1],
      'd' => ['foo' => 'how', 2, 1]
    ];
    $expectS12 = [
      'a' => 'ehy',
      'b' => 0,
      'c' => [1, 2, 3],
      'd' => ['foo' => 'who', 1, 2],
      'x' => 42
    ];
    $expectS21 = [
      'a' => 'ehy',
      'b' => 0,
      'c' => [1, 3, 2],
      'd' => ['foo' => 'who', 1, 2],
      'x' => 42
    ];

    return [
      [[$subject], $subject],
      [[$subject, $other1], $expectS1],
      [[$other1, $subject], $expect1S],
      [[$subject, $other1, $other2], $expectS12],
      [[$subject, $other2, $other1], $expectS21]
    ];
  }

  /**
   * provides test cases for testRandom()
   *
   * @return array[]  test cases
   */
  public function _randomProvider() : array {
    $subject = $this->_simpleArrayA;
    $count = count($subject);
    $exception = new ArrayToolsException(ArrayToolsException::INVALID_SAMPLE_SIZE, ['max' => $count]);
    return [
      [$subject, 1, null],
      [$subject, 2, null],
      [
        $subject,
        0,
        new ArrayToolsException(
          ArrayToolsException::INVALID_SAMPLE_SIZE,
          ['count' => $count, 'size' => 0]
        )
      ],
      [
        $subject,
        $count + 1,
        new ArrayToolsException(
          ArrayToolsException::INVALID_SAMPLE_SIZE,
          ['count' => $count, 'size' => $count + 1]
        )
      ]
    ];
  }

  /**
   * sets test expectations for a given exception.
   *
   * @param Throwable $e  the exception on which to base expectations
   */
  protected function _setExceptionExpectations(Throwable $expected) {
    $this->expectException(get_class($expected));
    $message = $expected->getMessage();
    $code = $expected->getCode();
    if (! empty($message)) {
      $this->expectExceptionMessage($message);
    }
    if (! empty($code)) {
      $this->expectExceptionCode($code);
    }
  }
}