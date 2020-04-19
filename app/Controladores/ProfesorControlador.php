<?php

namespace App\Controladores;

use App\Modelos\Profesor;
use Twig\Environment as Twig;

class ProfesorControlador
{
    private $twig;

    public function __construct(Twig $twig, Profesor $profesor)
    {
        $this->twig = $twig;
        $this->modelo = $profesor;
    }

    public function todos()
    {
        echo $this->twig->render('profesores/lista.twig', [
            'profesores' => $this->modelo->getAll(),
        ]);
    }
    
    public function muestra($ident)
    {
        echo $this->twig->render('profesores/ficha.twig', [
            'profesor' => $this->modelo->get($ident),
        ]);
    }
}
