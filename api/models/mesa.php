<?php

class Mesa
{
    public $id;
    public $estado;
    public $codigoMesa;
    public $codigo_pedido;
    public $usos;

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $this->estado = 'abierta';
        $this->codigo_pedido = 'libre';

        do {
            $this->codigoMesa = Mesa::crearCodigo(); 
        } while (Mesa::existeCodigoMesa($this->codigoMesa)); 

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigoMesa,estado,codigo_pedido) VALUES (:codigoMesa,:estado,:codigo_pedido)");
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }


    public static function crearCodigo()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $codigo = '';
        for ($i = 0; $i < 5; $i++) {
            $codigo .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $codigo;
    }

    public static function existeCodigoMesa($codigoMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) FROM mesas WHERE codigoMesa = :codigoMesa");
        $consulta->bindValue(':codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn() > 0; 
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

        public static function obtenerMesa($estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }


    public static function obtenerPrimeraMesaAbierta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigoMesa FROM mesas WHERE estado = 'abierta' LIMIT 1");
        $consulta->execute();
        return $consulta->fetchColumn();
    }


    public static function ObtenerCodigoMesaPorIDPedido($idPedidoIngresado){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigoMesa FROM mesas WHERE codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $idPedidoIngresado, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn();
    }

    public static function modificarMesa($mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado, codigoMesa = :codigoMesa, codigo_pedido = :codigo_pedido WHERE id = :id");
        
        $consulta->bindValue(':estado', $mesa->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $mesa->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigoMesa', $mesa->codigoMesa, PDO::PARAM_INT);
        $consulta->bindValue(':codigo_pedido', $mesa->codigo_pedido, PDO::PARAM_INT);

        $consulta->execute();
    }

    public static function borrarMesa($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);

        $consulta->execute();
    }


    public static function IngresarCodigoPedidoCambiarEstado($codigoMesa, $codigoPedidoIngresado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET codigo_pedido = :codigo_pedido, estado = 'cliente esperando' WHERE codigoMesa = :codigoMesa");
        $consulta->bindValue(':codigo_pedido', $codigoPedidoIngresado, PDO::PARAM_INT);
        $consulta->bindValue(':codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function cambiarEstadoMesa($codigoMesa, $nuevoEstado)
    {
        $estadosPermitidos = ['cliente pagando', 'cliente comiendo', 'cerrada', 'cliente esperando', 'abierta'];

        if (!in_array($nuevoEstado, $estadosPermitidos)) {
            throw new InvalidArgumentException("Estado inválido: $nuevoEstado");
        }

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :nuevoEstado WHERE codigoMesa = :codigoMesa");
        $consulta->bindValue(':nuevoEstado', $nuevoEstado, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->execute();
    }
    public static function sumarUso($codigoMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET usos = usos + 1 WHERE codigoMesa = :codigoMesa");
        $consulta->bindValue(':codigoMesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->execute();
    }
}
?>