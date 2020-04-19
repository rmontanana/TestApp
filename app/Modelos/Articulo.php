<?php

namespace App\Modelos;

use App\Utiles\Cifrar;

class Articulo
{
    private $datos;

    public function __construct()
    {
        $this->datos = [
            1 => [
                'id' => 1,
                'titulo' => 'Título artículo 1',
                'descripcion' => 'Descripción completa del artículo 1',
                'url' => $this->getUrl(1)
            ],
            2 => [
                'id' => 2,
                'titulo' => 'Título artículo 2',
                'descripcion' => 'Descripción completa del artículo 2',
                'url' => $this->getUrl(2)
            ]
        ];
    }

    public function getAll()
    {
        return $this->datos;
    }

    public function get($identificador)
    {
        return $this->datos[Cifrar::decode($identificador)];
    }

    public function getUrl($identificador): string
    {
        return '/articulo/' . Cifrar::encode($identificador);
    }
}
