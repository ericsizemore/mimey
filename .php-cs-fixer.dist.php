<?php
$config = new PhpCsFixer\Config();

$config
    ->setRules([
        '@PER-CS' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->in(__DIR__ . '/bin')
    )
;

return $config;
