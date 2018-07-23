<?php

include __DIR__ . '/../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$board = \Calcinai\PHPi\Factory::create($loop);


//$board->getPin(18)->setFunction(\Calcinai\PHPi\Pin\PinFunction::PWM0);
//
//$board->getPWM(\Calcinai\PHPi\Peripheral\PWM::PWM0)
//    ->setFrequency(1920)
//    ->setRange(200)
//    ->setDutyCycle(100)
//    ->start();

//Set to pwm, no idea why above isn't working (1 is wiringPi BCM 18)
`sudo gpio mode 1 pwm && sudo gpio pwm 1 380`;

$clocks = [
    new \NTPClock\Clock(
        $loop,
        $board->getPin(4),
        $board->getPin(17),
        new DateTimeZone('Europe/London')
    ),
    new \NTPClock\Clock(
        $loop,
        $board->getPin(27),
        $board->getPin(22),
        new DateTimeZone('America/Los_Angeles')
    ),
    new \NTPClock\Clock(
        $loop,
        $board->getPin(23),
        $board->getPin(24),
        new DateTimeZone('America/New_York')
    ),
    new \NTPClock\Clock(
        $loop,
        $board->getPin(10),
        $board->getPin(9),
        new DateTimeZone('America/New_York')
    ),
    new \NTPClock\Clock(
        $loop,
        $board->getPin(25),
        $board->getPin(11),
        new DateTimeZone('America/Los_Angeles')
    ),
    new \NTPClock\Clock(
        $loop,
        $board->getPin(8),
        $board->getPin(7),
        new DateTimeZone('Pacific/Auckland')
    )
];


$loop->addPeriodicTimer(0.1, function () use ($clocks, $loop) {
    foreach ($clocks as $clock) {
        $clock->autoTick();
    }
});

$loop->run();
