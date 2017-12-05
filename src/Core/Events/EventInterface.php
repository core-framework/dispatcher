<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 28/11/17
 * Time: 12:30 PM
 */

namespace Core\Events;


interface EventInterface
{
    /**
     * Returns the name of the Event
     *
     * @return string
     */
    public function getName();

    /**
     * Set the Event Data
     *
     * @param $key      string  The data name
     * @param $value    mixed   The value associated with given key
     * @return void
     */
    public function addData($key, $value);

    /**
     * Returns the data bound to the Event
     *
     * @param   string   $key   The data key for which to fetch a value
     * @return  mixed|array     Returns an array of data as key value pairs
     *                          if no key is given
     */
    public function getData($key = null);

    /**
     * Stops the propagation of execution of the listeners on this event
     *
     * @return void
     */
    public function stop();

    /**
     * Returns whether the propagation of the execution of the listeners has stopped
     *
     * @return mixed
     */
    public function isStopped();
}