<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests/unit')
    ->in(__DIR__ . '/tests/functional')
;

return PhpCsFixer\Config::create()
    ->finder($finder)
;
