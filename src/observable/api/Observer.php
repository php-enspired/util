<?php
/**
 * @package    at.util
 * @version    0.4
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2016
 * @license    GPL-3.0 (no other versions permitted)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  You MAY NOT apply the terms of any other version of the GPL.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare( strict_types = 1 );
namespace at\util\observable\api;

/**
 * refinement of SplObserver.
 * provides for named events, filtered subscriptions, and passing arbitrary data with updates.
 */
interface Observer extends \SplObserver {

  /**
   * removes an update handler or triggers.
   *
   * passing both a handler and trigger list will remove those triggers from that handler;
   * if the handler's trigger list is emptied it will be removed.
   *
   * passing only a handler will remove that handler.
   *
   * passing only trigger(s) will remove those triggers from all handlers.
   *
   * @param callable|null $handler     subject handler
   * @param string[]|null              event names to remove
   * @throws InvalidArgumentException  if both $handler and $triggers are null
   */
  public function off( callable $handler=null, array $triggers=null );

  /**
   * registers an update handler for given events.
   *
   * handlers should use the following signature:
   *  handler( SplSubject $subject [, string $eventName [, mixed …$args]] ) : void
   *
   * event "triggers" are regular expressions; pass NULL to register a handler for "all updates."
   *
   * @param callable      $handler   the handler to register
   * @param string[]|null $triggers  event names to invoke this handler for
   */
  public function on( callable $handler, array $triggers );

  /**
   * @see <http://php.net/SPLObserver.update>
   * in addition:
   *
   * an event name may be passed as the second arg, and will trigger any matching handlers.
   * implementations SHOULD provide for default handlers for empty event names.
   *
   * additional, arbitrary args may be passed after the event name,
   * and will be passed along to invoked handlers.
   *
   * @param SplSubject $subject   the subject of the update
   * @param string     $1         event name
   * @param mixed      $…         additional arguments for update
   * @throws ObservableException  if an uncaught exception is thrown during update
   */
  public function update( \SplSubject $subject );
}
