<?php
/**
 * This file is part of the CoreFramework package.
 *
 * (c) shalom.s <shalom.s@coreframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Events;

/**
 * Interface ListenerInterface
 *
 * @package Core\Events
 */
interface ListenerInterface
{
    /**
     * @param EventInterface            $event          The Event Object
     * @param string|null               $eventName      The Event Name
     * @param DispatcherInterface|null  $dispatcher     The Dispatcher that called the Listener
     *
     * @return mixed
     */
    public function __invoke(EventInterface $event, string $eventName = null, DispatcherInterface $dispatcher = null);
}