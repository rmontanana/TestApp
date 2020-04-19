<?php

namespace App\Controladores;

use App\Modelos\Articulo;

use Twig\Environment as Twig;

class ArticuloControlador
{
    private $twig;
    private $modelo;

    public function __construct(Twig $twig, Articulo $articulo)
    {
        $this->twig = $twig;
        $this->modelo = $articulo;
    }

    public function todos()
    {
        echo $this->twig->render('articulos/lista.twig', [
            'articulos' => $this->modelo->getAll(),
        ]);
    }
    
    public function muestra($ident)
    {
        echo $this->twig->render('articulos/ficha.twig', [
            'articulo' => $this->modelo->get($ident),
        ]);
    }
}
