# Guía de Exposición: Proyecto ESMAR BURGER (Avance 2)

Esta guía te ayudará a estructurar tu presentación frente al profesor o jurado paso a paso, explicando de forma técnica y profesional cada apartado del sistema.

---

## 1. Introducción y Propósito del Sistema (El "Pitch")
*   **¿Qué es?**: ESMAR BURGER es una aplicación web de delivery premium para hamburguesería, diseñada bajo el modelo SaaS (Software as a Service) modular y escalable.
*   **Objetivo de este Avance 2**: Lograr la completa disponibilidad del sistema en internet (despliegue en la nube) y la transición de un almacenamiento local estático (SQLite) a una base de datos distribuida y moderna en la nube (**Neon PostgreSQL**).

---

## 2. Arquitectura de Infraestructura (Cómo funciona en la nube)
Explica cómo está montado el sistema ahora:
*   **Servidor Web / Hosting (Vercel)**:
    *   Explicación: "Usamos Vercel para el despliegue del Frontend y las funciones Backend. Dado que Vercel es una plataforma serverless nativa, configuramos un runtime personalizado mediante `vercel-php` (comunidad) en el archivo `vercel.json` para ejecutar PHP de manera moderna y sin necesidad de pagar servidores dedicados VPS".
*   **Base de Datos en la Nube (Neon)**:
    *   Explicación: "Utilizamos Neon, que es una base de datos **Serverless PostgreSQL**. A diferencia de los servidores de bases de datos tradicionales que siempre están encendidos y consumiendo recursos, Neon auto-escala según la demanda y entra en suspensión cuando no hay tráfico, optimizando costos e infraestructura".
*   **Seguridad**:
    *   Explicación: "Las credenciales no están quemadas (hardcoded) en el código. Se manejan a través de **Variables de Entorno** (`DATABASE_URL`) directamente configuradas en el panel seguro de Vercel".

---

## 3. Explicación del Código y Estructura (El Backend)
Si te piden ver el código, abre los archivos clave y explica esto:

### A. [config.php](file:///c:/Users/User/OneDrive/Desktop/ESMAR%20BURGER%20-%20ING%20WEB/config.php) (El corazón del sistema)
*   **¿Qué hace?**: Maneja el estado de la sesión, define helper functions de seguridad (`isLoggedIn`, `isAdmin`) y realiza la conexión a la base de datos a través de **PDO (PHP Data Objects)**.
*   **Punto clave**: Explica que analiza la URL de conexión de forma dinámica. Si detecta un string `postgres`, adapta el driver a `pgsql` de manera transparente para el resto de las consultas del sistema.

### B. Estructura Modular ([includes/header.php](file:///c:/Users/User/OneDrive/Desktop/ESMAR%20BURGER%20-%20ING%20WEB/includes/header.php) y [includes/footer.php](file:///c:/Users/User/OneDrive/Desktop/ESMAR%20BURGER%20-%20ING%20WEB/includes/footer.php))
*   **Explicación**: "El diseño es modular. Usamos `require_once` para no repetir el código HTML del menú de navegación ni del pie de página en cada vista. Además, el Header calcula dinámicamente cuántos productos tiene el usuario en el carrito recorriendo la variable de sesión `$_SESSION['cart']`".

### C. La base de datos ([database.sql](file:///c:/Users/User/OneDrive/Desktop/ESMAR%20BURGER%20-%20ING%20WEB/database.sql))
*   **Explicación**: "Muestra la estructura de relaciones. Es una arquitectura limpia con llaves foráneas (`FOREIGN KEY`) que conectan a los usuarios con sus pedidos, y los pedidos con sus detalles de compra en cascada para evitar registros huérfanos".

---

## 4. Demostración del Flujo en Vivo (Paso a Paso)

### Flujo 1: Vista del Cliente
1.  **Home / Carta (`index.php`)**: Muestra cómo los productos se renderizan dinámicamente leyendo de la base de datos de Neon. Explica que puedes filtrar por categorías (Hamburguesas, Broaster, Combos) de forma fluida.
2.  **Carrito de Compras (`cart.php`)**: Añade un par de hamburguesas. Explica que el carrito se almacena en variables de sesión, manteniendo los productos mientras el cliente navega.
3.  **Registro y Login (`login.php` / `registro.php`)**: Inicia sesión con el usuario cliente (`cliente@gmail.com` / `cliente`). Explica que la contraseña se verifica de manera segura usando la función nativa de encriptación `password_verify` en PHP.
4.  **Checkout (`checkout.php`)**: Procesa el pedido rellenando dirección y método de pago. Muestra cómo el total se calcula y se registra.
5.  **Mis Pedidos (`mis_pedidos.php`)**: Muestra el historial del cliente y el estado del pedido actual (por defecto "pendiente").

### Flujo 2: Vista de Administrador
1.  **Cierre de sesión e ingreso**: Sal e ingresa con `admin@esmarburger.com` / `admin`.
2.  **Panel de Administración (`admin/dashboard.php`)**: Al iniciar sesión como admin, se habilita el botón "Panel Admin" en el menú superior.
3.  **Gestión**: Muestra que el administrador puede ver el listado de ventas totales, la cantidad de pedidos y actualizar el estado de los mismos (ej. cambiar de "pendiente" a "en camino").

---

## 5. Respuestas a Preguntas Difíciles (Preguntas del Profesor)

*   **P: ¿Por qué usaste PostgreSQL en vez de MySQL si en clase usamos phpMyAdmin?**
    *   *R: "Para el despliegue en la nube, PostgreSQL cuenta con mejores alternativas modernas de arquitectura 'Serverless' como Neon, que tiene un plan gratuito potente y permite branching de base de datos. De todos modos, la conexión PDO en PHP nos permite cambiar de motor fácilmente sin modificar el código del sistema, y dejamos el esquema documentado para ambos motores".*
*   **P: ¿Cómo manejan el carrito de compras en la nube si las funciones de Vercel son efímeras (Serverless)?**
    *   *R: "Utilizamos las sesiones nativas de PHP (`session_start()`). En Vercel, al ser consultas directas que mantienen las cookies del cliente, el estado del carrito se mantiene en el navegador del usuario y se procesa correctamente en el backend".*
*   **P: ¿Qué medidas de seguridad implementaron para las contraseñas?**
    *   *R: "Las contraseñas de los usuarios no se guardan en texto plano en la base de datos. Usamos la función hash recomendada de PHP `password_hash` con el algoritmo `PASSWORD_DEFAULT` (Bcrypt), protegiendo la información ante posibles filtraciones de datos".*
