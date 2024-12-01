<?php



class Tarea {
    public $id;
    public $codigoPedido;
    public $nombreProducto;
    public $trabajadorAsignado;
    public $cantidad;
    public $estado;
    public $seccion;
    public $tiempoEstimado;
    public $Precio;
    public $fecha_alta;

    public function crearTarea() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO Tarea (codigoPedido, nombreProducto, trabajadorAsignado, cantidad, estado, seccion, tiempoEstimado, fecha_alta) 
             VALUES (:codigoPedido, :nombreProducto, :trabajadorAsignado, :cantidad, :estado, :seccion, :tiempoEstimado, :fecha_alta)"
        );
        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_INT);
        $consulta->bindValue(':nombreProducto', $this->nombreProducto, PDO::PARAM_STR);
        $consulta->bindValue(':trabajadorAsignado', $this->trabajadorAsignado, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
        $this->estado = 'pendiente';
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $this->seccion = Producto::obtenerProducto($this->nombreProducto)['seccion'];
        $consulta->bindValue(':seccion', $this->seccion, PDO::PARAM_STR);
        $this->tiempoEstimado = Tarea::obtenerTiempoEstimado($this->nombreProducto);
        $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_INT);
        $this->fecha_alta = date('Y-m-d H:i:s');
        $consulta->bindValue(':fecha_alta', $this->fecha_alta, PDO::PARAM_STR);


        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public function setCodigoPedido($codigoPedido) {
        $this->codigoPedido = $codigoPedido;
    }

    public function setNombreProducto($nombreProducto) {
        $this->nombreProducto = $nombreProducto;
    }

    public static function asignarTrabajador($producto)
{
    if (!is_array($producto) || !isset($producto['nombre']) || !isset($producto['cantidad'])) {
        throw new Exception("Producto inválido en asignarTrabajador");
    }

    $productoObtenido = Producto::obtenerProducto($producto['nombre']);
    if ($productoObtenido) {
        $seccionProducto = $productoObtenido['seccion'];
        $cantidad = $producto['cantidad'];
        $tiempoEstimado = $productoObtenido['tiempo'];
        $productosAsignados = [];

        switch ($seccionProducto) {
            case 'cocina':
                for ($i = 0; $i < $cantidad; $i++) {
                    $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol('cocinero');
                    $productosAsignados[] = [
                        'nombre' => $producto['nombre'],
                        'cantidad' => 1,
                        'empleadoAsignado' => $empleadoAsignado ? $empleadoAsignado['usuario'] : null,
                        'estado' => $empleadoAsignado ? 'preparandose' : 'pendiente',
                        'tiempoEstimado' => $empleadoAsignado ? $tiempoEstimado * rand(10, 13) / 10 : 'pendiente'
                    ];
                    if ($empleadoAsignado) {
                        Usuario::cambiarAEstadoOcupado($empleadoAsignado['id']);
                    }
                }
                break;

            case 'chopera':
            case 'tragos':
                for ($i = 0; $i < $cantidad; $i++) {
                    if ($i % 5 == 0) {
                        $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol($seccionProducto == 'chopera' ? 'cervezero' : 'bartender');
                    }
                    $productosAsignados[] = [
                        'nombre' => $producto['nombre'],
                        'cantidad' => min(5, $cantidad - $i),
                        'empleadoAsignado' => $empleadoAsignado ? $empleadoAsignado['usuario'] : null,
                        'estado' => $empleadoAsignado ? 'preparandose' : 'pendiente',
                        'tiempoEstimado' => $empleadoAsignado ? $tiempoEstimado * rand(10, 13) / 10 : 'pendiente'
                    ];
                    if ($empleadoAsignado) {
                        Usuario::cambiarAEstadoOcupado($empleadoAsignado['id']);
                    }
                }
                break;

            default:
                throw new Exception("Sección de producto no válida.");
        }
        return $productosAsignados;
    } else {
        throw new Exception("Producto no encontrado: " . $producto['nombre']);
    }
}
    public static function PrepararTarea($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE Tarea SET estado = 'preparandose' WHERE codigoPedido = :codigoPedido");
        
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_INT);

        $consulta->execute();
    }


    public static function liberarTrabajadores($codigoPedido){
        $tareas = Tarea::obtenerTareas($codigoPedido);
        foreach ($tareas as $tarea) {
            if ($tarea->estado == 'preparandose') {
                Usuario::cambiarAEstadoLibre($tarea->trabajadorAsignado);
            }
        }
    }

    public static function FinalizarTarea($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE Tarea SET estado = 'listo' WHERE codigoPedido = :codigoPedido");
        
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_INT);

        $consulta->execute();
    }

    public static function obtenerTareas($codigoPedido) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT * FROM Tarea WHERE codigoPedido = :codigoPedido"
        );
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Tarea');
    }

    public static function calcularPrecioFinal($codigoPedido) {
        $tareas = Tarea::obtenerTareas($codigoPedido);
        $precioFinal = 0;
        foreach ($tareas as $tarea) {
            $producto = Producto::obtenerProducto($tarea->nombreProducto);
            if ($producto) {
                $precioFinal += $producto['precioUnidad'] * $tarea->cantidad;
            }
        }
        return $precioFinal;
    }

    public static function obtenerTiempoEstimado($nombreProducto) {
        if ($nombreProducto === null) {
            throw new Exception("Debe proporcionar un nombre de producto.");
        }

        $producto = Producto::obtenerProducto($nombreProducto);
        if ($producto) {
            return $producto['tiempo'] * rand(9, 13) / 10;
        }
        throw new Exception("Producto no encontrado: " . $nombreProducto);
    }

}
?>
