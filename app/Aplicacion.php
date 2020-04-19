<?php

namespace App;

use \DI\Container;

/**
 * Si se define ISDEBUG como false se activará la caché de rutas.
 * Esta caché que se encuentra en el directorio caché
 * habrá que borrarla siempre que se modifiquen las rutas
 */
define('ISDEBUG', true);

class Aplicacion
{
    private $contenedor;

    public function __construct(Container $contenedor)
    {
        $this->contenedor = $contenedor;
    }

    public function run()
    {
        [$metodo, $parametros] = $this->contenedor->call([Rutas\Rutas::class, 'procesa']);
        $this->contenedor->call($metodo, $parametros);
    }
}
