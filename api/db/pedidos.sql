SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


    public $id;
    public $codigoPedido;
    public $estado;
    public $nombreCliente;
    public $codigoMesa;
    public $tiempoEstimado; 
    public $precio; 
    public $listaProductos = array();


CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigoPedido  VARCHAR(255)  NOT NULL,
    estado  VARCHAR(255)  NOT NULL,
    nombreCliente  VARCHAR(255)  NOT NULL,
    codigoMesa  VARCHAR(255)   NULL,
    tiempoEstimado  VARCHAR(255)   NULL,
    precio  VARCHAR(255)   NULL,
    listaProductos JSON  NULL
);

ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;