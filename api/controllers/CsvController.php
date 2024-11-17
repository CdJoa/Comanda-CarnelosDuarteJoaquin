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

    
    public static function DescargarProductoCSV(Request $request, Response $response){
        $productos = Producto::obtenerTodos();
        $csv = fopen(__DIR__ . '/../descargas/productosDescargados.csv', 'w');
        fputcsv($csv, ['id', 'nombre', 'cantidad', 'precioUnidad', 'tipo', 'seccion', 'tiempo']);
        foreach ($productos as $producto) {
            fputcsv($csv, [$producto->id, $producto->nombre, $producto->cantidad, $producto->precioUnidad, $producto->tipo, $producto->seccion, $producto->tiempo]);
        }
        fclose($csv);
        $response->getBody()->write(json_encode(['message' => 'Archivo CSV generado']));
        return $response->withHeader('Content-Type', 'application/json');
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

    public static function DescargarUsuarioCSV(Request $request, Response $response){
        $usuarios = Usuario::obtenerTodos();
        $csv = fopen(__DIR__ . '/../descargas/usuariosDescargados.csv', 'w');
        fputcsv($csv, ['id', 'usuario', 'clave', 'rol', 'estado']);
        foreach ($usuarios as $usuario) {
            fputcsv($csv, [$usuario->id, $usuario->usuario, $usuario->clave, $usuario->rol, $usuario->estado]);
        }
        fclose($csv);
        $response->getBody()->write(json_encode(['message' => 'Archivo CSV generado']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public static function DescargarPedidoCSV(Request $request, Response $response){
        $pedidos = Pedido::obtenerTodos();
        $csv = fopen(__DIR__ . '/../descargas/pedidosDescargados.csv', 'w');
        fputcsv($csv, ['id', 'codigo', 'mesa', 'estado', 'tiempoEstimado', 'tiempoInicio', 'tiempoEntrega', 'tiempoFinalizacion', 'precioTotal']);
        foreach ($pedidos as $pedido) {
            fputcsv($csv, [$pedido->id, $pedido->codigo, $pedido->mesa, $pedido->estado, $pedido->tiempoEstimado, $pedido->tiempoInicio, $pedido->tiempoEntrega, $pedido->tiempoFinalizacion, $pedido->precioTotal]);
        }
        fclose($csv);
        $response->getBody()->write(json_encode(['message' => 'Archivo CSV generado']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>
