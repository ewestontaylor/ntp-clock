<?php

namespace NTPClock;

use Calcinai\PHPi\Pin;
use Calcinai\PHPi\Pin\PinFunction;
use React\EventLoop\LoopInterface;

class Clock
{
    private $loop;
    private $gpio_a;
    private $gpio_b;
    private $timezone;
    private $cache_filename;
    private $physical_time;

    /**
     * Clock constructor.
     *
     * @param LoopInterface $loop
     * @param Pin $gpio_a
     * @param Pin $gpio_b
     * @param \DateTimeZone $timezone
     * @throws \Calcinai\PHPi\Exception\InvalidPinFunctionException
     */
    public function __construct(LoopInterface $loop, Pin $gpio_a, Pin $gpio_b, \DateTimeZone $timezone)
    {
        $this->loop = $loop;
        $this->gpio_a = $gpio_a;
        $this->gpio_b = $gpio_b;
        $this->timezone = $timezone;

        $this->gpio_a->setFunction(PinFunction::OUTPUT);
        $this->gpio_b->setFunction(PinFunction::OUTPUT);

        $tz_name = preg_replace('/[^\w]+/', '-', strtolower($this->timezone->getName()));

        $this->cache_filename = sprintf('%s-%s', $gpio_a->getPinNumber(), $tz_name);

        $this->physical_time = $this->getPhysicalTime();
    }

    public function getPhysicalTime()
    {
        //If the cache doesn't exist, return null
        if (!file_exists($this->cache_filename)) {
            return new \DateTime('now', $this->timezone);
        }

        //Read the file from disk
        $time = file_get_contents($this->cache_filename);
        return \DateTime::createFromFormat('H:i:s', $time, $this->timezone);
    }

    public function persistPhysicalTime()
    {
        //Cache the time
        file_put_contents($this->cache_filename, $this->physical_time->format('H:i:s'));
    }

    /**
     * @throws \Calcinai\PHPi\Exception\InvalidPinFunctionException
     * @throws \Exception
     */
    public function autoTick()
    {
        $currentTime = new \DateTime('now', $this->timezone);
        $diff = $currentTime->diff($this->physical_time);
        $seconds = $diff->h * 60 * 60 + $diff->i * 60 + $diff->s;

        //Compensate for 24h time
        $seconds = $seconds % 43200;

        if ($seconds > 0) {
            $this->tick();
        }
    }

    /**
     * @throws \Calcinai\PHPi\Exception\InvalidPinFunctionException
     * @throws \Exception
     */
    private function tick()
    {
        //maintaining odd/even tick
        static $even = false;
        $gpio = $even ? $this->gpio_a : $this->gpio_b;

        //Toggle flag
        $even = !$even;

        $gpio->high();
        $this->loop->addTimer(0.1, [$gpio, 'low']);

        //Update physical and store
        $this->physical_time->add(new \DateInterval('PT1S'));
        $this->persistPhysicalTime();
    }
}
