# PHP + Slim Framework

Proyecto de PHP + Slim Framework con lo justo y necesario para funcionar

# Pasos a seguir

1. Descargar el repositorio dentro de la carpeta htdocs de nuestro xampp
2. Utilizar el comando ``composer install`` para instalar Slim y Psr7.
3. Utilizar el comando ``php -S localhost:666 -t app`` para levantar el servidor.
4. Realizar un pedido GET a http://localhost:666 para revisar que todo funcione correctamente.
5. ¡Tu turno, a seguir codeando! 



20. Volvemos al apartado del storage y corremos la / las querys necesarias para crear nuestras tablas.

```
  CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(250) NOT NULL,
  clave VARCHAR(250) NOT NULL,
  fechaBaja DATE DEFAULT NULL
);
```

