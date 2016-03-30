<?php

$basedir = realpath(__DIR__ . '/../');
# All calls are now relative to the root directory
chdir($basedir);

include $basedir . '/vendor/autoload.php';

$core = new \Eater\Glim\Core();
$core->startTimer(["total"]);
$core->boot($basedir);
$core->run();
$core->endTimer(["total"]);

