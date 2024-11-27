SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombreCliente VARCHAR(255) NOT NULL,
    codigo_pedido  VARCHAR(255)  NOT NULL,
    restaurante INT NOT NULL,
    mozo INT NOT NULL,
    cocinero INT NOT NULL,
    PuntajeFinal INT NOT NULL,
    texto  VARCHAR(255)  NOT NULL,
    fecha_alta  VARCHAR(255)  NOT NULL,


);

ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;