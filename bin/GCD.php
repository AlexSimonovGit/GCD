#!/usr/bin/env php
<?php

declare(strict_types=1);

$autoloadDev = __DIR__ . '/../vendor/autoload.php';
$autoloadInstalled = __DIR__ . '/../../../autoload.php';

if (file_exists($autoloadDev)) {
    require $autoloadDev;
} else {
    require $autoloadInstalled;
}

use function AlexSimonovGit\GCD\Controller\startGame;

startGame();