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
        $pdf->SetFont('helvetica', 'I', 18); 
        $pdf->SetTextColor(0, 0, 255); 
        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', false, '', 0, false, 'T', 'U'); 

        $pdf->Ln(10);

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
}
