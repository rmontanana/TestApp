<?php

use App\Aplicacion;

/**
 * Contenedor para la inyección de dependencias
 */
$contenedor = require '../conf/conf.php';

$aplicacion = new Aplicacion($contenedor);

$aplicacion->run();
