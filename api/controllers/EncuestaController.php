<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

require_once __DIR__ . '/../models/Encuesta.php';

class EncuestaController
{
    public function CargarUno($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        $nombreCliente = $parsedBody['nombreCliente'] ?? null;
        $codigoPedido = $parsedBody['codigoPedido'] ?? null;
        $restaurante = $parsedBody['restaurante'] ?? null;
        $mozo = $parsedBody['mozo'] ?? null;
        $cocinero = $parsedBody['cocinero'] ?? null;
        $texto = $parsedBody['texto'] ?? null;

        if (!$nombreCliente || !$codigoPedido || !$restaurante || !$mozo || !$cocinero || !$texto) {
            $payload = json_encode(["mensaje" => "Faltan campos obligatorios para crear la encuesta"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        
        if (!Encuesta::validarOrigenEncuesta($codigoPedido, $nombreCliente)) {
            $payload = json_encode(["mensaje" => "Codigo/NombreCliente Invalido"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        try {
            $encuesta = new Encuesta();
            $encuesta->nombreCliente = $nombreCliente;
            $encuesta->codigo_pedido = $codigoPedido;
            $encuesta->restaurante = $restaurante;
            $encuesta->mozo = $mozo;
            $encuesta->cocinero = $cocinero;
            $encuesta->texto = $texto;

            $encuesta->PuntajeFinal = $restaurante + $mozo + $cocinero;

            $id = $encuesta->crearEncuesta();

            $payload = json_encode([
                "mensaje" => "Encuesta creada con éxito",
                "idEncuesta" => $id,
                "puntajeFinal" => $encuesta->PuntajeFinal
            ]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public static function mejoresEncuestas($request, $response, $args){
        $encuestas = Encuesta::obtenerTodos();
        $encuestas = array_filter($encuestas, function($encuesta){
            return $encuesta->PuntajeFinal >= 25;
        });

        $encuestas = array_map(function($encuesta){
            return [
                "nombreCliente" => $encuesta->nombreCliente,
                "puntajeFinal" => $encuesta->PuntajeFinal
            ];
        }, $encuestas);

        $payload = json_encode($encuestas, JSON_PRETTY_PRINT);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }
}
