<?php

namespace App\Utiles;

use mysqli;

class Sql
{
    /**
     * Manejador de la base de datos a utilizar
     *
     * @var mysqli
     */
    private $_bdd;
    /**
     * Mensaje del último mensaje de error generado
     *
     * @var string
     */
    private $_mensajeError;
    /**
     * Almacena el estado de error o no de la última acción.
     *
     * @var boolean
     */
    private $_error;
    /**
     * Estado de la conexión con la base de datos.
     *
     * @var boolean
     */
    private $_estado;
    /**
     * Objeto que alberga la última consulta ejecutada.
     *
     * @var mixed
     */
    private $_peticion;
    /**
     * Número de tuplas afectadas en la última consulta.
     *
     * @var integer
     */
    private $_numero;
    /**
     * Id del último registro insertado
     *
     * @var integer
     */
    private $_insertId;

    /**
     * Crea un objeto Sql y conecta con la Base de Datos.
     *
     */
    public function __construct()
    {
        $servidor = SERVIDOR;
        $usuario = USUARIO;
        $clave = CLAVE;
        $baseDatos = BASEDATOS;
        $this->_numero = 0;
        $this->_bdd = @new mysqli($servidor, $usuario, $clave, $baseDatos);
        if (mysqli_connect_errno()) {
            $this->_mensajeError = '<h1>Fallo al conectar con el servidor MySQL.</h1>';
            $this->_mensajeError .= 'Servidor [' . $servidor . '] base de datos [' . $baseDatos . ']';
            $this->_error = true;
            $this->_estado = false;
        } else {
            $this->_mensajeError = '';
            $this->_error = false;
            $this->_estado = true;
            $this->_bdd->set_charset('utf8');
        }
        $this->_peticion = null;
    }

    /**
     * Cierra la conexión
     */
    public function __destruct()
    {
        // Si estaba conectada la base de datos la cierra.
        if ($this->_estado) {
            $this->_bdd->close();
        }
    }

    /**
     * Aborta la transacción actual en la base de datos
     *
     * @return bool
     */
    public function abortaTransaccion(): bool
    {
        $codigo = $this->_bdd->rollback();
        $this->_bdd->autocommit(true);
        return $codigo;
    }

    /**
     * Devuelve los campos que contiene la última consulta
     *
     * @return array
     */
    public function camposResultado(): ?array
    {
        if (!$this->_estado) {
            $this->_error = true;
            $this->_mensajeError = 'No está conectado a una base de datos';
            return null;
        }
        if (!$this->_peticion) {
            $this->_error = true;
            $this->_mensajeError = 'No hay un resultado disponible';
            return null;
        }
        $datos = $this->_peticion->fetch_field();
        $this->_error = false;
        $this->_mensajeError = '';
        return ($datos);
    }

    /**
     * Comienza una transacción en la base de datos
     *
     * @return bool
     */
    public function comienzaTransaccion(): bool
    {
        //En versiones antiguas esta función no existía y se utilizaba this->bdd->autocommit(false);
        return $this->_bdd->begin_transaction();
    }

    /**
     * Confirma la transacción actual en la base de datos
     *
     * @return bool
     */
    public function confirmaTransaccion(): bool
    {
        $codigo = $this->_bdd->commit();
        $this->_bdd->autocommit(true);
        $this->_peticion = null;
        return $codigo;
    }

    /**
     * Devuelve la condición de error de la última petición
     *
     * @return boolean condición de error.
     */
    public function error(): bool
    {
        return $this->_error;
    }

    /**
     * Devuelve la estructura de campos de una tabla.
     *
     * @param string $tabla Nombre de la tabla.
     *
     * @return string vector asociativo con la descripción de la tabla [campo]->valor
     */
    public function estructura(string $tabla): ?array
    {
        $salida = [];
        if ($this->_peticion) {
            $this->_peticion->free_result();
        }
        $comando = "show full columns from $tabla";
        if (!$this->ejecuta($comando)) {
            return false;
        }
        while ($dato = $this->procesaResultado()) {
            $salida[] = $dato;
        }
        return $salida;
    }

    /**
     * Ejecuta el comando
     *
     * @param string $comando comando a ejecutar
     *
     * @return bool
     */
    public function ejecuta(string $comando): bool
    {
        if (!$this->_estado) {
            $this->_error = true;
            $this->_mensajeError = 'No est&aacute; conectado';
            return false;
        }

        if (!$this->_peticion = $this->_bdd->query($comando)) {
            $this->_error = true;
            $this->_mensajeError = 'No pudo ejecutar la petici&oacute;n: ' . $comando;
            return false;
        }
        $this->_numero = $this->_bdd->affected_rows;
        $this->_insertId = $this->_bdd->insert_id;
        $this->_error = false;
        $this->_mensajeError = '';
        return true;
    }

    /**
     * Devuelve los resultados correspondientes a una consulta
     *
     * @return mixed
     */
    public function procesaResultado(): ?array
    {
        if (!$this->_estado) {
            $this->_error = true;
            $this->_mensajeError = 'No está conectado a una base de datos';
            return null;
        }
        if (!$this->_peticion) {
            $this->_error = true;
            $this->_mensajeError = 'No hay un resultado disponible';
            return null;
        }
        $datos = $this->_peticion->fetch_assoc();
        $this->_error = false;
        $this->_mensajeError = '';
        return ($datos);
    }

    /**
     * Filtra una cadena para utilizar en la base de datos
     *
     * @param string $cadena cadena a filtrar
     *
     * @return string
     */
    public function filtra(string $cadena): string
    {
        return $this->_bdd->real_escape_string($cadena);
    }

    /**
     * Devuelve el mensaje de error de la última petición
     *
     * @return string
     */
    public function mensajeError(): string
    {
        return $this->_mensajeError . $this->_bdd->error;
    }

    /**
     * Devuelve el código de error correspondiente a la última petición realizada
     *
     * @return int
     */
    public function numError(): int
    {
        return $this->_bdd->errno;
    }

    /**
     * Devuelve el número de tuplas total si se ha hecho una consulta select
     * con SELECT SQL_CALC_FOUND_ROWS * ...
     *
     * @return integer Número de tuplas.
     */
    public function numeroTotalTuplas(): int
    {
        $comando = 'select found_rows();';
        if (!$peticion = $this->_bdd->query($comando)) {
            $this->_error = true;
            $this->_mensajeError = 'No pudo ejecutar la petici&oacute;n: ' . $comando;
            return false;
        }
        $numero = $peticion->fetch_row();
        return $numero[0];
    }

    /**
     * Devuelve el número de tuplas afectadas en la última petición.
     *
     * @return integer Número de tuplas.
     */
    public function numeroTuplas(): int
    {
        return $this->_numero;
    }

    /**
     * Devuelve el manejador de la base de datos
     *
     * @return mysqli
     */
    public function obtieneManejador()
    {
        return $this->_bdd;
    }

    /**
     * Prepara un comando para ejecutar
     *
     * @param string $comando Comando a preparar
     *
     * @return \mysqli_stmt
     */
    public function prepara(string $comando)
    {
        return $this->_bdd->prepare($comando);
    }

    /**
     * Devuelve el id del último registro insertado
     *
     * @return int
     */
    public function ultimoId(): int
    {
        return $this->_insertId;
    }
}
