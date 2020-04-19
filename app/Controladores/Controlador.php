<?php

namespace App\Controladores;

use Twig\Environment as Twig;

class Controlador
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function todos()
    {
        echo $this->twig->render('inicio.twig');
    }

}
