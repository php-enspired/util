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
use at\util\Arrays,
    at\util\ArraysException;
use PHPUnit\Framework\TestCase;

class ArraysTest extends TestCase {

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
   * @covers Arrays::__callStatic()
   * @dataProvider _arrayFunctionProvider
   */
  public function testArrayFunctions(string $name, array $args, $expected) {
    if ($expected instanceof Throwable) {
      $this->_setExceptionExpectations($expected);
    }

    $actual = Arrays::{$name}(...$args);
    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers Arrays::categorize()
   */
  public function testCategorize() {
    // success case
    $categorized = Arrays::categorize($this->_nestedArrayA, 'a');
    $expectedKeys = array_column($this->_nestedArrayA, 'a');
    $this->assertEquals($expectedKeys, array_keys($categorized));
    foreach ($expectedKeys as $i => $key) {
      $this->assertEquals($this->_nestedArrayA[$i], $categorized[$key][0]);
    }

    // failure case (bad key)
    $this->_setExceptionExpectations(
      new ArraysException(ArraysException::INVALID_CATEGORY_KEY), ['key' => 'x']
    );
    Arrays::categorize($this->_nestedArrayA, 'x');
  }

  /**
   * @covers Arrays::contains()
   * @dataProvider _containsProvider
   */
  public function testContains(array $subject, $value, $expected) {
    if ($expected) {
      $this->assertTrue(Arrays::contains($subject, $value));
      return;
    }
    $this->assertFalse(Arrays::contains($subject, $value));
  }

  /**
   * @covers Arrays::dig()
   * @dataProvider _digProvider
   */
  public function testDig(array $subject, string $path, array $opts, $expected) {
    if ($expected instanceof Throwable) {
      $this->_setExceptionExpectations($expected);
    }

    $actual = Arrays::dig($subject, $path, $opts);
    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers Arrays::extend()
   * @dataProvider _extendRecursiveProvider
   */
  public function testExtendRecursive(array $arrays, array $expected) {
    $this->assertEquals($expected, Arrays::extend(...$arrays));
  }

  /**
   * @covers Arrays::index()
   */
  public function testIndex() {
    $this->assertEquals(
      array_column($this->_nestedArrayA, null, 'a'),
      Arrays::index($this->_nestedArrayA, 'a')
    );
  }

  /**
   * @covers Arrays::isList()
   */
  public function testIsList() {
    // lists
    $this->assertTrue(Arrays::isList([1, 2, 3]));
    $this->assertTrue(Arrays::isList([0 => 1, 1 => 2, 2 => 3]));

    // not lists
    $this->assertFalse(Arrays::isList([1 => 1, 2 => 2, 3 => 3]));
    $this->assertFalse(Arrays::isList(['a' => 1, 'b' => 2, 'c' => 3]));
    $this->assertFalse(Arrays::isList([1, 2, 3, 'a' => 4]));
  }

  /**
   * @covers Arrays::random()
   * @dataProvider _randomProvider
   */
  public function testRandom(array $subject, int $num, Throwable $expected = null) {
    if ($expected) {
      $this->_setExceptionExpectations($expected);
    }

    $random = Arrays::random($subject, $num);
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
   * @covers Arrays::rekey()
   */
  public function testRekey() {
    $rekeyed = Arrays::rekey(
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
    $proxyFunctions = array_flip(
      Arrays::ARRAY_FUNCTIONS + Arrays::ARRAY_REF_FUNCTIONS
    );

    $tests = [];
    $simpleA = $this->_simpleArrayA;
    $simpleB = $this->_simpleArrayB;
    $simpleAB = $simpleA + $simpleB;
    $nestedA = $this->_nestedArrayA;
    $nestedB = $this->_nestedArrayB;
    $ucompare = function($a, $b=0) { return $a <=> $b; };

    // array_* functions

    $arrays = [
      'array_change_key_case' => [[$simpleA, CASE_LOWER], [$simpleA, CASE_UPPER]],
      'array_chunk' => [[$simpleA, 1], [$simpleA, 1, true]],
      'array_column' => [
        [$nestedA, key($nestedA)],
        [$nestedA, key($nestedA), key($nestedA)],
        [$nestedA, null, key($nestedA)]
      ],
      'array_combine' => [[array_keys($simpleA), array_values($simpleA)]],
      'array_count_values' => [[$simpleA]],
      'array_diff_assoc' => [[$simpleAB, $simpleB]],
      'array_diff_key' => [[$simpleAB, $simpleB]],
      'array_diff_uassoc' => [[$simpleAB, $simpleB, $ucompare]],
      'array_diff_ukey' => [[$simpleAB, $simpleB, $ucompare]],
      'array_diff' => [[$simpleAB, $simpleB]],
      'array_fill_keys' => [[array_keys($simpleA), reset($simpleA)]],
      'array_fill' => [[1,2,3]],
      'array_filter' => [
        [$simpleA, function($v) { return $v !== 'a'; }],
        [$simpleA, function($k) { return $k !== 'a'; }, ARRAY_FILTER_USE_KEY],
        [$simpleA, function($v, $k) { return $k !== 'a'; }, ARRAY_FILTER_USE_BOTH]
      ],
      'array_flip' => [[$simpleA]],
      'array_intersect_assoc' => [[$simpleA, $simpleB]],
      'array_intersect_key' => [[$simpleA, $simpleB]],
      'array_intersect_uassoc' => [[$simpleA, $simpleB, $ucompare]],
      'array_intersect_ukey' => [[$simpleA, $simpleB, $ucompare]],
      'array_intersect' => [[$simpleA, $simpleB]],
      'array_key_exists' => [[key($simpleA), $simpleA]],
      'array_keys' => [[$simpleA]],
      'array_map' => [[$ucompare, $simpleA]],
      'array_merge_recursive' => [[$nestedA, $nestedB]],
      'array_merge' => [[$simpleA, $simpleB]],
      'array_pad' => [[$simpleA, 5, 'foo'], [$simpleA, -5, 'foo']],
      'array_product' => [[[1, 2, 3]]],
      'array_reduce' => [[$simpleA, function($c, $v) { return "{$c}:{$v}"; }]],
      'array_replace_recursive' => [[$nestedA, $nestedB]],
      'array_replace' => [[$simpleA, $simpleB]],
      'array_reverse' => [[$simpleA]],
      'array_search' => [[reset($simpleA), $simpleA], [reset($simpleA), $simpleA, true]],
      'array_slice' => [
        [$simpleA, 1],
        [$simpleA, 1, 1],
        [$simpleA, 1, -1],
        [$simpleA, 1, 1, true]
      ],
      'array_sum' => [[[1, 2, 3]]],
      'array_udiff_assoc' => [[$simpleA, $simpleB, $ucompare]],
      'array_udiff_uassoc' => [[$simpleA, $simpleB, $ucompare, $ucompare]],
      'array_udiff' => [[$simpleA, $simpleB, $ucompare]],
      'array_uintersect_assoc' => [[$simpleA, $simpleB, $ucompare]],
      'array_uintersect_uassoc' => [[$simpleA, $simpleB, $ucompare, $ucompare]],
      'array_uintersect' => [[$simpleA, $simpleB, $ucompare]],
      'array_unique' => [[[1, 1, 2, 3, 5, 5]]],
      'array_values' => [[$simpleA]]
    ];
    foreach ($arrays as $function => $cases) {
      foreach ($cases as $i => $args) {
        $result = $function(...$args);
        $tests["{$function}:{$i}"] = [$function, $args, $result];
        $proxy = $proxyFunctions[$function] ?? null;
        if ($proxy) {
          $tests["{$proxy}:{$i}"] = [$proxy, $args, $result];
        }
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
      new ArraysException(ArraysException::NO_SUCH_METHOD, ['method' => 'foo'])
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
      [$subject, 'a/b/c', [Arrays::OPT_DELIM => '/'], 'foo'],
      [$subject, 'a.c', [], null],
      [
        $subject,
        'a.c',
        [Arrays::OPT_THROW => true],
        new ArraysException(ArraysException::INVALID_PATH, ['path' => 'a.c'])
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
    $exception = new ArraysException(ArraysException::INVALID_SAMPLE_SIZE, ['max' => $count]);
    return [
      [$subject, 1, null],
      [$subject, 2, null],
      [
        $subject,
        0,
        new ArraysException(
          ArraysException::INVALID_SAMPLE_SIZE,
          ['count' => $count, 'size' => 0]
        )
      ],
      [
        $subject,
        $count + 1,
        new ArraysException(
          ArraysException::INVALID_SAMPLE_SIZE,
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
