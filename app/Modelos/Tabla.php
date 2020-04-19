<?php

namespace App\Modelos;

use App\Utiles\Sql;

class Tabla
{
    protected $bdd;
    protected $error;
    protected $mensaje;
    protected $resultado;
    protected $tabla;
    protected $clavePrincipal;

    public function __construct(Sql $bdd, string $tabla)
    {
        $this->bdd = $bdd;
        $this->tabla = $tabla;
        $this->error = false;
    }

    /**
     * Ejecuta el comando SQL que se le pasa en la base de datos
     *
     * @param string $comando
     * @return bool resultado de la ejecución (true => correcto / false => error)
     */
    protected function ejecuta(string $comando): bool
    {
        $res = $this->bdd->ejecuta($comando);
        $this->error = $this->bdd->error();
        $this->mensaje = $this->bdd->mensajeError();
        return $res;
    }

    /**
     * Inserta un registro en la tabla
     *
     * @param array $datos array de datos a insertar
     * @param bool $idUnico Si la tabla tiene definida una clave principal como id
     * @return bool resultado de la ejecución (true => correcto / false => error)
     */
    public function insertaRegistro($datos, $idUnico = true): bool
    {
        $campos = implode(',', array_keys($datos));
        $valores = "'" . implode("','", array_values($datos)) . "'";
        if ($idUnico) {
            $idClave = 'id, ';
            $idValor = 'null, ';
        } else {
            $idClave = '';
            $idValor = '';
        }
        $comando = "insert into $this->tabla ($idClave$campos) values ($idValor$valores);";
        return $this->ejecuta($comando);
    }

    /**
     * Devuelve el mensaje de error asociado a la última operación realizada en la base de datos
     *
     * @return string
     */
    public function getMensajeError(): string
    {
        return $this->mensaje;
    }

    /**
     * Devuelve el estado de error asociado a la última operación realizada en la base de datos
     *
     * @return bool
     */
    public function getError(): bool
    {
        return $this->error;
    }

    /**
     * Realiza una consulta en la tabla sobre uno o varios registros y los ordena según un criterio
     *
     * @param string $id Clave principal o nulo para obtener todas
     * @param string $ord Criterio de ordenación
     * @return $this
     */
    public function getRegistro($id = null, $ord = null)
    {
        $where = !is_null($id) ? " where id='$id'" : '';
        $orden = !is_null($ord) ? " order by $ord" : '';
        $comando = 'select * from ' . $this->tabla . " $where $orden;";
        $this->ejecuta($comando);
        if (!is_null($id)) {
            $this->resultado = $this->bdd->procesaResultado();
        } else {
            $this->resultado = null;
        }
        return $this;
    }

    /**
     * Realiza una consulta en la tabla sobre uno o varios registros dependiendo del criterio de
     * selección y los ordena según el criterio de ordenación
     *
     * @param string $criterio Criterio de selección
     * @param string $ord Criterio de ordenación
     * @return $this
     */
    public function getCriterio($criterio = null, $ord = null)
    {
        $where = !is_null($criterio) ? " where $criterio" : '';
        $orden = !is_null($ord) ? " order by $ord" : '';
        $comando = 'select * from ' . $this->tabla . " $where $orden;";
        $this->ejecuta($comando);
        $this->resultado = null;
        return $this;
    }

    /**
     * Devuelve array con los resultados de la última petición a getRegistro
     *
     * @return ?array
     */
    public function resultado(): ?array
    {
        return $this->resultado;
    }

    /**
     * Construye un array con los resultados de una consulta
     *
     * @param array $campos Campos de la consulta a incluir en el array
     * @param string $ident Opcional el campo índice a utilizar en el array resultante
     * @return array
     */
    public function lista($campos = null, $ident = null): array
    {
        $salida = [];
        while ($fila = $this->bdd->procesaResultado()) {
            if (is_null($campos)) {
                $filap = $fila;
            } else {
                foreach ($campos as $campo) {
                    $filap[$campo] = $fila[$campo];
                }
            }
            if (is_null($ident)) {
                $salida[] = $filap;
            } else {
                $filap['url'] = $this->getUrl($filap[$ident]);
                $salida[$filap[$ident]] = $filap;
            }
        }
        
        return $salida;
    }

    /**
     * listaUnica
     *
     * @param array $campos Campos de la consulta a incluir en el array
     * @param string $id Campo índice a utilizar en el array resultante
     * @return array
     */
    public function listaUnica($campos = null, $id): array
    {
        $salida = [];
        while ($fila = $this->bdd->procesaResultado()) {
            if (is_null($campos)) {
                $filap = $fila;
            } else {
                foreach ($campos as $campo) {
                    $filap[$campo] = $fila[$campo];
                }
            }
            $indice = $fila[$id];
            $filap['url'] = $this->getUrl($indice);
            $salida[$indice] = $filap;
        }
        return $salida;
    }

    /**
     * Construye un array del tipo datos[id] = descripcion con cualquier consulta que tenga estos campos
     *
     * @param array $campos Campos de la consulta a incluir en el array
     * @return array
     */
    public function listaTxt($campos = null): array
    {
        $salida = [];
        while ($fila = $this->bdd->procesaResultado()) {
            if (is_null($campos)) {
                $filap = $fila;
            } else {
                foreach ($campos as $campo) {
                    if ($campo != 'id') {
                        if (count($campos) === 2) {
                            //Si es una lista sencilla id, nombre (p.ej.) no hace un vector con el resultado
                            $filap = $fila[$campo];
                        } else {
                            $filap[$campo] = $fila[$campo];
                        }
                    }
                }
            }
            $salida[array_key_exists('id', $fila) ? $fila['id'] : $fila[$campos[0]]] = $filap;
        }
        return $salida;
    }

    /**
     * Cifra el id que se le pase por parámetro
     */
    public function key(string $id): string
    {
        return Cifrar::encode($id);
    }
}
