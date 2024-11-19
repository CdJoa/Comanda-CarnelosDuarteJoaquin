<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class PDFController
{
    public static function generarPDF($titulo, $contenido, $carpetaDestino = './Fotos-PDF')
    {
        $pdf = new TCPDF();
        $pdf->AddPage();

        $pathImagen = __DIR__ . '/../Fotos-PDF/restaurante.png';
        $pdf->SetFont('helvetica', 'I', 18); 
        $pdf->SetTextColor(0, 0, 255); 

        if (file_exists($pathImagen)) {
            $pdf->Cell(0, 10, $titulo, 0, 0, 'L', false, '', 0, false, 'T', 'U'); 
            $pdf->Image($pathImagen, 150, 10, 50, 30); 
        } else {
            $pdf->Cell(0, 10, $titulo, 0, 1, 'C', false, '', 0, false, 'T', 'U'); 
        }

        $pdf->Ln(20); 

        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(0, 0, 0); 
        foreach ($contenido as $linea) {
            $pdf->Cell(0, 10, $linea, 0, 1);
        }
        $pathCarpeta = __DIR__ . '/../' . $carpetaDestino;
        if (!is_dir($pathCarpeta)) {
            mkdir($pathCarpeta, 0777, true);
        }

        $nombreArchivo = $pathCarpeta . DIRECTORY_SEPARATOR . str_replace(' ', '_', $titulo) . '.pdf';
        $pdf->Output($nombreArchivo, 'F');

        return $nombreArchivo;
    }


    public static function listaProductos($request, $response, $args)
    {
        $productos = Producto::obtenerTodos();

        $contenido = [];
        foreach ($productos as $producto) {
            $contenido[] = "ID: " . $producto->id .  $producto->nombre . " - Precio: " . $producto->precioUnidad;
        }

        try {
            $titulo = "Lista de Productos";
            $archivoPDF = self::generarPDF($titulo, $contenido);

            $response = $response->withHeader('Content-Type', 'application/pdf')
                                 ->withHeader('Content-Disposition', 'inline; filename="productos.pdf"');
            $response->getBody()->write(file_get_contents($archivoPDF));

            return $response;
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public static function listaUsuarios($request, $response, $args)
    {
        $usuarios = Usuario::obtenerTodos();

        $contenido = [];
        foreach ($usuarios as $usuario) {
            $contenido[] = "ID: " . $usuario->id . " - Usuario: " . $usuario->usuario . " - Rol: " . $usuario->rol;
        }

        try {
            $titulo = "Lista de Usuarios";
            $archivoPDF = self::generarPDF($titulo, $contenido);

            $response = $response->withHeader('Content-Type', 'application/pdf')
                                 ->withHeader('Content-Disposition', 'inline; filename="usuarios.pdf"');
            $response->getBody()->write(file_get_contents($archivoPDF));

            return $response;
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
