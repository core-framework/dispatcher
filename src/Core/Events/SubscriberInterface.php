<?php
/**
 * This file is part of the CoreFramework package.
 *
 * (c) Shalom Sam <shalom.s@coreframework.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Events;


interface SubscriberInterface
{
    /**
     * @param DispatcherInterface $dispatcher
     * @return void
     */
    public function subscribe(DispatcherInterface $dispatcher);

    /**
     * @param DispatcherInterface $dispatcher
     *
     * @return mixed
     */
    public function unSubscribe(DispatcherInterface $dispatcher);
}