<?php

$phar2 = new Phar('mindy.phar', 0, 'mindy.phar');
$phar2->buildFromDirectory(dirname(__FILE__) . '/src/Mindy');
