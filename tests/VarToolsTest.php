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

use PHPUnit\Framework\TestCase;

class VarToolsTest extends TestCase {

  /**
   * @covers VarTools::debug
   * @dataProvider _varProvider
   */
  public function testDebug(array $vars, $expected) {}

  /**
   * @covers VarTools::filter
   * @dataProvider _filterProvider
   */
  public function testFilter($value, $filter, $expected) {}

  /**
   *
   */
  public function _varProvider() : array {

    // @todo
    $this->markTestIncomplete('not yet implemented');
    return [[]];

  }

  /**
   * @return array[] {
   *    @type mixed $0  a value to filter
   *    @type mixed $1  the filter to apply
   *    @type mixed $2  the expected result
   *  }
   */
  public function _filterProvider() : array {

    // @todo
    $this->markTestIncomplete('not yet implemented');
    return [[]];

    return array_merge(
      $this->_boolProvider(),
      $this->_emailProvider(),
      $this->_floatProvider(),
      $this->_intProvider(),
      $this->_iterableProvider(),
      $this->_dateTimeableProvider(),
      $this->_callableProvider(),
      $this->_stringableProvider()
    );

    $emails = [
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

}
