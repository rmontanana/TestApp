<?php

use DI\ContainerBuilder;
use Twig\TwigFilter;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/confBD.php';

$constructor = new ContainerBuilder;
$definitions = [
    Twig::class => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../Recursos/Vistas');
        $twig = new Twig(
            $loader,
            ['cache' => ISDEBUG ? ! ISDEBUG : __DIR__ . '/../cache/twig',]
        );
        $filter = new TwigFilter('encode', '\App\Utiles\Cifrar::encode');
        $twig->addFilter($filter);
        return $twig;
    },
];

$constructor->addDefinitions($definitions);
return $constructor->build();
