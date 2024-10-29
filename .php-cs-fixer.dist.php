<?php

declare(strict_types=1);

$config = new IfCastle\CodeStyle\Config();
$config->getFinder()
       ->in(__DIR__ . '/src');

$config->setCacheFile(__DIR__ . '/.php_cs.cache');

return $config;
