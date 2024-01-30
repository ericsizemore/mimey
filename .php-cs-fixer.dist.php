<?php

$config = new PhpCsFixer\Config();

$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PSR2'   => true,
        '@PSR12'  => true,
        //'@PHP83Migration' => true,
        'array_syntax'           => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align',
                '='  => 'align',
            ],
        ],
        //'ordered_imports'   => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->in(__DIR__ . '/bin')
            ->append([__DIR__ . '/rector.php', __DIR__ . '/.php-cs-fixer.dist.php'])
    )
;

return $config;
