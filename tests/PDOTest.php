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

use at\util\PDO;
use PHPUnit\Framework\TestCase;

class PDOTest extends TestCase {

  /**
   * @covers PDO::__construct
   * @dataProvider _optionsProvider
   */
  public function test__construct(array $options) {
    $pdo = $this->_newPDO($options);

    $defaults = [
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];

    $this->assertEquals(
      $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE),
      $options[PDO::ATTR_DEFAULT_FETCH_MODE] ?? $defaults[PDO::ATTR_DEFAULT_FETCH_MODE]
    );
    $this->assertEquals(
      $pdo->getAttribute(PDO::ATTR_ERRMODE),
      $options[PDO::ATTR_ERRMODE] ?? $defaults[PDO::ATTR_ERRMODE]
    );

    // note: getAttribute() cannot retrieve the value of PDO::ATTR_EMULATE_PREPARES
    // (afaik there's no way to test this)
  }

  /**
   * @covers PDO::arrayParam
   */
  public function testArrayParam() {
    $pdo = $this->_newPDO();

    $params = ['foo', 'bar', 'baz', 'bazinga'];

    list($markers, $values) = $pdo->arrayParam($params);
    $this->assertEquals($markers, '?, ?, ?, ?');
    $this->assertEquals($values, $params);

    list($markers, $values) = $pdo->arrayParam($params, 'marker');
    $this->assertEquals($markers, ':marker_0, :marker_1, :marker_2, :marker_3');
    $this->assertEquals(
      $values,
      [
        ':marker_0' => 'foo',
        ':marker_1' => 'bar',
        ':marker_2' => 'baz',
        ':marker_3' => 'bazinga'
      ]
    );
  }

  /**
   * @covers PDO::preparedQuery
   */
  public function testPreparedQuery() {
    $pdo = $this->_newPDO();
    $pdo->exec('drop table if exists test');
    $pdo->exec('create table test ( a INT, b INT )');
    $pdo->exec('insert into test (a, b) values (1, 2), (1, 3), (2, 3), (2, 4)');

    $this->assertEquals(
      $pdo->preparedQuery('select b from test where a=?', [1])->fetchAll(),
      [['b' => 2], ['b' => 3]]
    );

    $this->assertEquals(
      $pdo->preparedQuery('select b from test where a=:a', ['a' => 2])->fetchAll(),
      [['b' => 3], ['b' => 4]]
    );
  }

  /**
   * @return array[] {
   *    @type array $0  key => value map of PDO options
   *  }
   */
  public function _optionsProvider() : array {
    return [
      [[]],
      [[PDO::ATTR_EMULATE_PREPARES => true]],
      [[PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]],
      [[PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM]]
    ];
  }

  /**
   * creates a test at\util\PDO instance.
   *
   * @param array $options
   * @return PDO
   */
  protected function _newPDO(array $options = []) : PDO {
    if (! in_array('sqlite', PDO::getAvailableDrivers())) {
      $this->markTestSkipped('PDO::SQLite driver not available');
    }

    return new PDO('sqlite::memory', null, null, $options);
  }
}
