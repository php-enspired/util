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

use at\exceptable\ {
  Exceptable as ExceptableInterface,
  Exception as Exceptable
};

/**
 * error cases for Vars methods.
 */
class FilterException extends Exceptable {

  /**
   * @type int BAD_CALL_RIPLEY
   * @type int INVALID_FILTER
   * @type int FILTER_FAILURE
   */
  const BAD_CALL_RIPLEY = (1<<2);
  const INVALID_FILTER = (1<<3);
  const FILTER_FAILURE = (1<<4);

  /** {@inheritDoc} */
  const INFO = [
    self::BAD_CALL_RIPLEY => [
      'message' => 'error invoking callable',
      'tr_message' => 'error invoking callable: {__rootMessage__}'
    ],
    self::INVALID_FILTER => [
      'message' => 'invalid filter definition',
      'severity' => Exceptable::WARNING,
      'tr_message' => 'invalid filter definition: {definition}'
    ],
    self::FILTER_FAILURE => [
      'message' => 'filter failure',
      'severity' => Exceptable::NOTICE,
      'tr_message' => 'filter ({filter}) rejected value: {value}'
    ]
  ];

  /**
   * {@inheritDoc}
   * special handling to get filter name.
   */
  public function addContext(array $context) : ExceptableInterface {
    if (isset($context['filter'])) {
      $filterList = filter_list();
      $context['filter'] = array_combine(
        array_map(function ($f) { return filter_id($f); }, $filterList),
        $filterList
      )[$context['filter']] ?? $context['filter'];
    }

    return parent::addContext($context);
  }
}
