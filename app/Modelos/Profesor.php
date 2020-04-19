<?php

namespace App\Modelos;

use App\Utiles\Cifrar;
use App\Utiles\Sql;

class Profesor extends Tabla
{
    public function __construct(Sql $bdd)
    {
        parent::__construct($bdd, 'Profesor');
    }

    public function getProfesor($id = null, $orden = null)
    {
        return $this->getRegistro($id, $orden);
    }

    public function getUrl($identificador): string
    {
        return '/profesores/' . Cifrar::encode($identificador);
    }

    public function getAll(): array
    {
        return $this->getRegistro()->lista(null, 'id');
    }

    public function get($identificador): array
    {
        return $this->getRegistro(Cifrar::decode($identificador))->resultado();
    }
}
