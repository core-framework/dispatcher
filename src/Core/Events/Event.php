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

/**
 * This is the Base Event Class. By default this class has no data, but provides for means to bind data.
 *
 * @package Core\Events
 */
class Event implements EventInterface
{
    /**
     * @var string Event Name
     */
    protected $name;

    /**
     * @var array|mixed Event Data
     */
    protected $data;

    /**
     * @var bool Whether no further event listeners should be triggered
     */
    protected $stopped = false;

    /**
     * Event constructor.
     *
     * @param string $name
     * @param mixed $data
     */
    public function __construct($name, $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $this->data;
        } else {
            return $this->data[$key];
        }
    }

    /**
     * @inheritdoc
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function isStopped()
    {
        return $this->stopped;
    }

    /**
     * @inheritdoc
     */
    public function stop()
    {
        $this->stopped = true;
    }
}