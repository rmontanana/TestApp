<?php

namespace App\Controladores;

use Twig\Environment as Twig;

class ErrorControlador
{
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function e404()
    {
        echo $this->twig->render('utiles/error.twig', [
            'mensaje' => 'Página no encontrada',
        ]);
    }
    
    public function e405()
    {
        echo $this->twig->render('utiles/error.twig', [
            'mensaje' => 'Método no encontrado',
        ]);
    }
}
