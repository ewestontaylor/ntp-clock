<?php
namespace NTPClock;
use Calcinai\PHPi\Pin;
use Calcinai\PHPi\Pin\PinFunction;
use React\EventLoop\LoopInterface;
/**
 *
 */
class Clock
{
    private $loop;
    private $gpio_a;
    private $gpio_b;
    private $timezone;
    private $cache_filename;
    private $physical_time;
    public function __construct(LoopInterface $loop, Pin $gpio_a, Pin $gpio_b, \DateTimeZone $timezone)
    {
        $this->loop = $loop;
        $this->gpio_a = $gpio_a;
        $this->gpio_b = $gpio_b;
        $this->timezone = $timezone;
        $this->gpio_a->setFunction(PinFunction::OUTPUT);
        $this->gpio_b->setFunction(PinFunction::OUTPUT);
        $this->cache_filename = preg_replace('/[^\w]+/', '-', strtolower($this->timezone->getName()));
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
    public function autoTick()
    {
        $currentTime = new \DateTime('now', $this->timezone);
        $diff = $currentTime->diff($this->physical_time);
        $seconds = $diff->h * 60 * 60 + $diff->i * 60 + $diff->s;
        echo $seconds . "\n";
        $seconds = $seconds %43200;
        if ($seconds > 0) {
            $this->tick();
        }
    }
    private function tick()
    {
        //maintaining odd/even tick
        static $even = false;
        $gpio = $even ? $this->gpio_a : $this->gpio_b;
        $even = !$even;
        $gpio->high();
        $this->loop->addTimer(0.1, [$gpio, 'low']);
        $this->physical_time->add(new \DateInterval('PT1S'));
        $this->persistPhysicalTime();
    }
}
