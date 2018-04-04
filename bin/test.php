<?php

include __DIR__.'/../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$board = \Calcinai\PHPi\Factory::create($loop);

$clock = new \NTPClock\Clock(
  $loop,
  $board->getPin(17),
  $board->getPin(18),
  new DateTimeZone('Pacific/Auckland')
);

$loop->addPeriodicTimer(1, [$clock,'tick']);

$loop->run();
