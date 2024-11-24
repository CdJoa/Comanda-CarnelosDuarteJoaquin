<?php

class Usuario
{
    public $id;
    public $usuario;
    public $clave;
    public $rol;
    public $estado;
    public $fecha_alta;
    public $operaciones;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $this->estado = 'libre';
        $this->fecha_alta = date('Y-m-d H:i:s');

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, clave, rol, estado, fecha_alta) VALUES (:usuario, :clave, :rol, :estado, :fecha_alta)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);

        if (self::existeNombreUsuario($this->usuario)) {
            throw new Exception("El nombre de usuario ya existe.");
        }

        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_alta', $this->fecha_alta, PDO::PARAM_STR);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function cuerpoCSV($usuario, $clave, $rol, $estado)
    {
        $usuario = new self();
        $usuario->usuario = $usuario;
        $usuario->clave = $clave;
        $usuario->rol = $rol;
        $usuario->estado = $estado;
        $usuario->fecha_alta = date('Y-m-d H:i:s');
        return $usuario->crearUsuario();
    }



    public static function existeNombreUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn() > 0; 
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuariosPorRol($rol)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE rol = :rol");
        $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }
    public static function obtener1UsuarioLibresPorRol($rol) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE rol = :rol AND estado = 'libre' LIMIT 1");
        $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            self::sumarOperaciones($usuario['usuario']);
        }

        return $usuario;
    }

    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function cambiarAEstadoOcupado($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET estado = 'ocupado' WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function cambiarAEstadoLibre($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET estado = 'libre' WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
    }   

    public static function cambiarAEstadoSuspendido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET estado = 'suspendido' WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET usuario = :usuario, clave = :clave, rol = :rol, estado = :estado WHERE id = :id");
        $consulta->bindValue(':usuario', $usuario->usuario, PDO::PARAM_STR);
        $claveHash = password_hash($usuario->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':id', $usuario->id, PDO::PARAM_INT);
        $consulta->bindValue(':rol', $usuario->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado',  $usuario->estado, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function borrarUsuario($id)
    {

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM usuarios WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        
    }

    public static function verificarClave($usuario, $clave)
    {
        if (!$usuario || !$clave) {
            return false; 
        }

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT clave FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
        $hash = $consulta->fetchColumn();

        if ($hash && password_verify($clave, $hash)) {
            return true; 
        }

        return false; 
    }

    public static function verificarRol($usuario, $rol)
    {
        if (!$usuario || !$rol) {
            return false; 
        }
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario AND rol = :rol");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn() > 0;
    }

    public static function sumarOperaciones($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET operaciones = operaciones + 1 WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function obtenerUsuariosAlta30Dias()
    {
        $fechaHoy = date('Y-m-d');
        
        $fechaHace30Dias = date('Y-m-d', strtotime('-30 days', strtotime($fechaHoy)));

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM usuarios WHERE fecha_alta >= :fechaHace30Dias");
        $consulta->bindValue(':fechaHace30Dias', $fechaHace30Dias, PDO::PARAM_STR);
        $consulta->execute();
        $ids = $consulta->fetchAll(PDO::FETCH_COLUMN, 0);
        $cantidad = count($ids);

        return [
            'ids' => $ids,
            'cantidad' => $cantidad
        ];
        return $consulta->fetchAll(PDO::FETCH_COLUMN, 0);
    }


}
?>