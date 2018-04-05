<?php

include __DIR__.'/../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$board = \Calcinai\PHPi\Factory::create($loop);

$clocks = [];

$clocks[] = new \NTPClock\Clock(
  $loop,
  $board->getPin(17),
  $board->getPin(18),
  new DateTimeZone('Pacific/Auckland')
);


$loop->addPeriodicTimer(0.1, function() use($clocks){
  foreach ($clocks as $clock) {
    $clock->autoTick();
  }
});

$loop->run();
