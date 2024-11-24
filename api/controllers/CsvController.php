<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Usuario.php';

class CsvController
{

    public static function leerCsv(Request $request): array
    {
        $uploadedFiles = $request->getUploadedFiles();
        $archivo = $uploadedFiles['archivo'] ?? null;

        if (!$archivo || $archivo->getError() !== UPLOAD_ERR_OK) {
            throw new Exception(!$archivo ? 'No file uploaded' : 'File upload error');
        }

        $fileContents = $archivo->getStream()->getContents();
        $rows = array_map('str_getcsv', explode(PHP_EOL, $fileContents));

        return $rows;
    }

    public static function guardarProductoCSV(Request $request, Response $response): Response
    {
        try {
            $fila = self::leerCsv($request);

            foreach ($fila as $columna) {
                if (count($columna) < 6) {
                    continue;
                }

                Producto::cuerpoCSV($columna[0], $columna[1], $columna[2], $columna[3], $columna[4], $columna[5]);
            }

            $response->getBody()->write(json_encode(['message' => 'Productos guardados exitosamente']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }


    public static function guardarUsuario(Request $request, Response $response): Response
    {
        try {
            $fila = self::leerCsv($request);

            foreach ($fila as $columna) {
                if (count($columna) < 4) {
                    continue;
                }

                Usuario::cuerpoCSV($columna[0], $columna[1], $columna[2], $columna[3]);
            }

            $response->getBody()->write(json_encode(['message' => 'Usuarios guardados exitosamente']));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public static function DescargarProductoCSV(Request $request, Response $response) {
        $productos = Producto::obtenerTodos();
    
        $output = fopen('php://temp', 'w');
        fputcsv($output, ['id', 'nombre', 'cantidad', 'precioUnidad', 'tipo', 'seccion', 'tiempo']);
    
        foreach ($productos as $producto) {
            fputcsv($output, [$producto->id, $producto->nombre, $producto->cantidad, $producto->precioUnidad, $producto->tipo, $producto->seccion, $producto->tiempo]);
        }
    
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
    
        $response->getBody()->write($csvContent);
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="productosDescargados.csv"');
    }
    
    public static function DescargarUsuarioCSV(Request $request, Response $response) {
        $usuarios = Usuario::obtenerTodos();
    
        $output = fopen('php://temp', 'w');
        fputcsv($output, ['id', 'usuario', 'clave', 'rol', 'estado', 'fecha_alta']);
    
        foreach ($usuarios as $usuario) {
            fputcsv($output, [$usuario->id, $usuario->usuario, $usuario->clave, $usuario->rol, $usuario->estado, $usuario->fecha_alta]);
        }
    
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
    
        $response->getBody()->write($csvContent);
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="usuariosDescargados.csv"');
    }
    
    public static function DescargarPedidoCSV(Request $request, Response $response) {
        $pedidos = Pedido::obtenerTodos();
    
        $output = fopen('php://temp', 'w');
        fputcsv($output, ['id', 'codigoPedido', 'estado', 'nombreCliente', 'codigoMesa', 'tiempoEstimado', 'precio', 'listaProductos']);
    
        foreach ($pedidos as $pedido) {
            $productos = json_encode($pedido['listaProductos'], JSON_UNESCAPED_UNICODE);
    
            $fila = [
                $pedido['id'] ?? '',
                $pedido['codigoPedido'] ?? '',
                $pedido['estado'] ?? '',
                $pedido['nombreCliente'] ?? '',
                $pedido['codigoMesa'] ?? '',
                $pedido['tiempoEstimado'] ?? '',
                $pedido['precio'] ?? '',
                $productos ?? ''
            ];
            fputcsv($output, $fila);
        }
    
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
    
        $response->getBody()->write($csvContent);
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="pedidosDescargados.csv"');
    }
    
}
?>
