<?php

namespace NTPClock;

use \Calcinai\PHPi\Pin;
use \Calcinai\PHPi\Pin\PinFunction;
use \React\EventLoop\LoopInterface;
/**
 *
 */
class Clock
{

  private $loop;
  private $gpio_a;
  private $gpio_b;
  private $timezone;

  public function __construct(LoopInterface $loop, Pin $gpio_a, Pin $gpio_b, \DateTimeZone $timezone)
  {
    $this->loop = $loop;
    $this->gpio_a = $gpio_a;
    $this->gpio_b = $gpio_b;
    $this->timezone = $timezone;

    $this->gpio_a->setFunction(PinFunction::OUTPUT);
    $this->gpio_b->setFunction(PinFunction::OUTPUT);
  }

  public function tick()
  {
    //maintaining odd/even tick
    static $even = false;

    $gpio = $even ? $this->gpio_a : $this->gpio_b;
    $even = !$even;

    $gpio->high();
    $this->loop->addTimer(0.1, [$gpio, 'low']);
$time = file_get_contents("time");
echo $time;
file_put_contents ("time","hello");
}
}
