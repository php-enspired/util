<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no later versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any later version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\mixin;

use at\util\Regex;

/**
 * default _parseTrigger() implementation for observer and observable traits. */
trait observableTrigger {

  /**
   * parses a string as a trigger regex.
   *
   * @param string $trigger  the event regex or literal event name
   * @return string          the parsed event regex
   */
  protected function _parseTrigger( string $trigger ) : string {
    if ( Regex::is_valid( $trigger ) ) {
      return $trigger;
    }
    if ( Regex::is_valid( "(^{$trigger}\b)ui" ) ) {
      return "(^{$trigger}\b)ui";
    }
    return '(^' . preg_quote( $trigger ) . '\b)ui';
  }
}
