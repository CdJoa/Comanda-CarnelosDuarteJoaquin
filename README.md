# PHP + Slim Framework

Proyecto de PHP + Slim Framework con lo justo y necesario para funcionar

# Pasos a seguir

1. Descargar el repositorio dentro de la carpeta htdocs de nuestro xampp
2. Utilizar el comando ``composer install`` para instalar Slim y Psr7.
3. Utilizar el comando ``php -S localhost:666 -t app`` para levantar el servidor.
4. Realizar un pedido GET a http://localhost:666 para revisar que todo funcione correctamente.
5. ¡Tu turno, a seguir codeando! 

# Subir a Vercel

6. Crear una cuenta en vercel, conectarla con su github.
7. Fork al proyecto y subirlo a su porpio github.

![{4DEE5655-BC9A-4AB8-875B-082F1BFABA6E}](https://github.com/user-attachments/assets/bbdeffed-69a6-4c01-80e1-25f130c5609a)

9. Crear la aplicación en vercel como "Other".

![{B5C8C8D9-2D6E-40D8-9704-480182E3E2BF}](https://github.com/user-attachments/assets/6bdf7fc9-3248-42dd-8338-e6304fa2a3c2)
 
10. Cuando le den a deploy, va a intentar pero va a fallar. Es normal.

![{0BDDB108-C09D-4594-860F-79D01DB32068}](https://github.com/user-attachments/assets/e0beabf4-6748-411a-b28c-8b71e39a6396)

12. Ir al proyecto, ir a las settings. Cambiar en la configuración la versión de node a 18x. No olvidar darle a save.

![{69AB7FFC-9727-4ABB-82AA-0DA55F433C15}](https://github.com/user-attachments/assets/622db8d6-31e0-450b-bf6a-697075eb7cd6)

13. Una vez hecho el cambio, se puede hacer un re-deplpoy para probar que funcione. Esto se hace desde la pestaña deployments.

![{651D93B4-1431-427B-B5DB-4C49D50EE2C6}](https://github.com/user-attachments/assets/9e82d9d7-b227-4474-bf5d-08389307c394)

14. Hasta acá, debería funcionar la ruta por defecto.

![{DBE96A3F-B9B8-401F-A6A8-225395F3E350}](https://github.com/user-attachments/assets/56ad3c14-685e-4c4f-ac0b-8240bf0aadfd)

15. Configurar PostgressSQL. Crear la base en la pestaña Storage.

![image](https://github.com/user-attachments/assets/e5e85cd5-a26a-43e9-a7e7-4a9ec5ef1088)

![image](https://github.com/user-attachments/assets/0ad3ebec-cffd-4ced-9bb1-4cb8c2867aa6)

16. Conecten la DB al proyecto. Configuración por defecto.

![{1193C9FB-1DD8-41CD-888A-2E6D80EBAD69}](https://github.com/user-attachments/assets/2b50ff6a-99e4-47f8-b9d9-21d7667ce851)

17. Busquen la opción de conexión para arcghvos .env, como el que estamos utilizando en el proyecto.
    Cuando le den a Copy Snippet se va a copiar todo al portapapeles.
    Pueden darle a view secret para ver todas las claves secretas. Esta información es importante para el próximo punto.
    
![image](https://github.com/user-attachments/assets/6e4bf14c-e2ac-4331-991e-7d7d40492439)

19. Pegar las configuraciones de entorno (environment) en la configuración de nuestro proyecto.

![{D85BFD0B-B7C4-4125-8726-D7A3572DA315}](https://github.com/user-attachments/assets/f47da39e-2f15-4bf2-8fe6-7cfde62c38f0)

![{C4B00CFD-679D-4E4C-A39C-27036B93FEEF}](https://github.com/user-attachments/assets/a9c9d467-26cc-492c-87b7-26e8aea3c86c)

- Si colocan el cursor en la primer key, y le dan a pegar a lo que copiamos antes, se va a completar todo automáticamente.

![{894DA115-E411-4CD7-8C9B-B79EC7D1C1E6}](https://github.com/user-attachments/assets/e1b74245-06f0-455f-9e8b-3bf97e38833c)

- Falta agregar un clave / valor que agregamos luego de modificar el proyecto. Ver el archivo .env.example para más info.
Falta agregar: DB_TYPE=pgsql

![{45239C0B-273C-4A5F-A610-7498C7AD0777}](https://github.com/user-attachments/assets/b44bb22e-1888-4870-a2a3-e354e09b191e)

- No olvidar darle a save!!


20. Volvemos al apartado del storage y corremos la / las querys necesarias para crear nuestras tablas.

```
  CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(250) NOT NULL,
  clave VARCHAR(250) NOT NULL,
  fechaBaja DATE DEFAULT NULL
);
```

![image](https://github.com/user-attachments/assets/d5551ab3-7e53-416e-a2a6-e690c38b1855)

![image](https://github.com/user-attachments/assets/f792a150-fc10-4773-901f-7024d57b65b8)

21. Listo! Prueben las rutas /usuarios por post, enviando **nombre** y **clave** por body, y luego la ruta /usuarios por GET

![{5FCEF403-573F-4440-B935-1B38B6C0AFB2}](https://github.com/user-attachments/assets/ce842147-fec1-48c2-9ebc-75b1dce506a8)

![{B5DE8D4D-90B7-4FB2-8810-B4DA60ED1640}](https://github.com/user-attachments/assets/c6e7dab4-ffa7-4011-9e0e-78f32c2bd233)

# Recuerden

Miren los archivos antes de probar todo. 

Q. ¿Qué hay en el index?
A. Queda en ustedes ver  eso.

Q. ¿Qué hay en el AccesoDatos.php, cómo conecta a la DB?
A. Está preparado para poder conectar a una base mysql y una postgres, 

Q. ¿De dónde salen los datos de $_ENV? 
A. En Vercel salen del paso 17 / 18. En el local, pueden mirar el archivo .env.example. Ese archivo sirve de ejemplo para que puedan crear su propio archivo de ambiente. 
Si crean en la base del proyecto un archivo .env y colocan ahí sus variables de ambiente, debería funcionar correctamente. De ahí salen los datos del array $_ENV.
