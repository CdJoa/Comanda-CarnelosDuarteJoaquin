<?php
require_once __DIR__ . '/../models/Pedido.php';
class encuesta
{

    public $id;
    public $nombreCliente;
    public $codigo_pedido;
    public $restaurante;
    public $mozo;
    public $cocinero;
    public $PuntajeFinal;
    public $texto;
    public $fecha;

    public function crearEncuesta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuestas (nombreCliente, codigo_pedido, restaurante, mozo, cocinero, texto, PuntajeFinal, fecha) VALUES (:nombreCliente, :codigo_pedido, :restaurante, :mozo, :cocinero, :texto, :PuntajeFinal, :fecha)");
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':restaurante', $this->restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':mozo', $this->mozo, PDO::PARAM_INT);
        $consulta->bindValue(':cocinero', $this->cocinero, PDO::PARAM_INT);
        $this->PuntajeFinal = $this->restaurante + $this->mozo + $this->cocinero;
        $consulta->bindValue(':PuntajeFinal', $this->PuntajeFinal, PDO::PARAM_INT);
        $consulta->bindValue(':texto', $this->texto, PDO::PARAM_STR);
        $this->fecha = date('Y-m-d');
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();
        
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function validarOrigenEncuesta($codigo_pedido, $nombreCliente)
    {
        try {
            $pedido = Pedido::obtenerPedidoCodigo($codigo_pedido);
            if (!$pedido) {
                throw new Exception('El pedido no existe');
            }
            if ($pedido->nombreCliente !== $nombreCliente) {
                throw new Exception('El nombre del cliente no coincide con el del pedido');
            }
            if ($pedido->estado !== 'pagado') {
                throw new Exception('El cliente todavia no pago como para evaluar');
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM encuestas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'encuesta');
    }

    public static function obtenerEncuestas30Dias()
    {
        $fechaHoy = date('Y-m-d');
        
        $fechaHace30Dias = date('Y-m-d', strtotime('-30 days', strtotime($fechaHoy)));

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM encuestas WHERE fecha_alta >= :fechaHace30Dias");
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