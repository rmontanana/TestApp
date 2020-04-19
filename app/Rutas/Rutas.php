<?php

namespace App\Rutas;

use function \FastRoute\cachedDispatcher;
use \FastRoute\RouteCollector;

class Rutas
{
    private $datosRutas;

    public function __construct()
    {
        $this->datosRutas = [
            ['tipo' => 'GET', 'URI' => '/', 'controlador' => [\App\Controladores\Controlador::class, 'todos']],
            ['tipo' => 'GET', 'URI' => '/articulos', 'controlador' => [\App\Controladores\ArticuloControlador::class, 'todos']],
            ['tipo' => 'GET', 'URI' => '/articulo/{ident}', 'controlador' => [\App\Controladores\ArticuloControlador::class, 'muestra']],
            ['tipo' => 'GET', 'URI' => '/profesores', 'controlador' => [\App\Controladores\ProfesorControlador::class, 'todos']],
            ['tipo' => 'GET', 'URI' => '/profesores/{ident}', 'controlador' => [\App\Controladores\ProfesorControlador::class, 'muestra']],

        ];
    }

    public function buscaURL(string $url): array
    {
        $clave = array_search($url, array_column($this->datosRutas, 'URI'));
        return $clave !== false ? $this->datosRutas[$clave] : [];
    }

    public function procesa(): array
    {
        $procesador = cachedDispatcher(function (RouteCollector $r) {
            foreach ($this->datosRutas as $datoRuta) {
                $r->addRoute($datoRuta['tipo'], $datoRuta['URI'], $datoRuta['controlador']);
            }
        }, [
            'cacheFile'     => __DIR__ . '/../../cache/route.cache', /* required */
            'cacheDisabled' => ISDEBUG,     /* optional, enabled by default */
        ]);

        $uri = $_SERVER['REQUEST_URI'];
        $ruta = $procesador->dispatch($_SERVER['REQUEST_METHOD'], $uri);

        switch ($ruta[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                return [['App\Controladores\ErrorControlador', 'e404'], []];
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                return [['App\Controladores\ErrorControlador', 'e405'], []];

            case \FastRoute\Dispatcher::FOUND:
                $controller = $ruta[1];
                $parameters = $ruta[2];
                return [$controller, $parameters];
        }
    }
}
