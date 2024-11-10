<?php

class Usuario
{
    public $id;
    public $usuario;
    public $clave;
    public $rol;
    public $estado;
    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $this->estado = 'pendiente';

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, clave, rol,estado) VALUES (:usuario, :clave, :rol,:estado)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);

        if (self::existeNombreUsuario($this->usuario)) {
            throw new Exception("El nombre de usuario ya existe.");
        }


        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave,rol,estado  FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuariosPorRol($rol)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, rol, estado FROM usuarios WHERE rol = :rol");
        $consulta->bindValue(':rol', $rol, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }


    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, rol, estado FROM usuarios WHERE usuario = :usuario");
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

    public static function cambiarAEstadoLibre($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE usuarios SET estado = 'libre' WHERE id = :id");
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

}
?>