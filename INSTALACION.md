# Instrucciones de Instalación — Tienda Virtual CI3

## Prerequisitos

- XAMPP con PHP 8.0+ y Apache
- PostgreSQL 16 instalado y corriendo
- mod_rewrite habilitado (ya activo en XAMPP)

---

## PASO 1 — Habilitar extensiones PostgreSQL en PHP

Abrir `c:\xampp\php\php.ini` y buscar las líneas comentadas:

```
;extension=pgsql
;extension=pdo_pgsql
```

Cambiarlas a:

```
extension=pgsql
extension=pdo_pgsql
```

**Reiniciar Apache** desde el panel de control de XAMPP.

---

## PASO 2 — Crear la base de datos

1. Abrir **pgAdmin 4** o una terminal psql
2. Crear la base de datos `tienda_db`:
   ```sql
   CREATE DATABASE tienda_db ENCODING 'UTF8';
   ```
3. Conectarse a `tienda_db` y ejecutar el script:
   ```
   c:\xampp\htdocs\tienda-en-linea\database\tienda_db.sql
   ```

En pgAdmin: clic derecho en `tienda_db` → Query Tool → abrir el archivo SQL → ejecutar (F5).

---

## PASO 3 — Configurar credenciales de base de datos

Editar `application/config/database.php`:

```php
$db['default'] = array(
    'hostname' => 'localhost',
    'username' => 'postgres',   // ← tu usuario de PostgreSQL
    'password' => 'postgres',   // ← tu contraseña de PostgreSQL
    'database' => 'tienda_db',
    'port'     => 5432,
    // ...
);
```

---

## PASO 4 — Configurar datos de la tienda

Editar `application/config/settings.php`:

```php
$config['tienda_nombre']   = 'Mi Tienda Online';     // Nombre de tu tienda
$config['tienda_slogan']   = 'Ropa para todos';
$config['tienda_email']    = 'tu@email.com';
$config['whatsapp_numero'] = '51987654321';           // Tu número con código de país
```

---

## PASO 5 — Verificar la URL base

En `application/config/config.php`:

```php
$config['base_url'] = 'http://localhost/tienda-en-linea/';
```

Si el sitio está en un dominio propio, cambiar a: `'https://tudominio.com/'`

---

## Accesos del sistema

| Sección | URL |
|---------|-----|
| Tienda (home) | `http://localhost/tienda-en-linea/` |
| Carrito | `http://localhost/tienda-en-linea/carrito` |
| Checkout | `http://localhost/tienda-en-linea/checkout` |
| Panel admin | `http://localhost/tienda-en-linea/admin` |

**Credenciales admin por defecto:**

---

## Integración con Izipay (cuando tengas credenciales)

1. Editar `application/models/Pasarela_model.php` → método `simular_pago()`
2. Reemplazar el cuerpo del método con el SDK de Izipay
3. Actualizar `config_pasarela` en la BD con tus credenciales reales:
   ```sql
   UPDATE config_pasarela
   SET merchant_id = 'TU_MERCHANT_ID',
       clave_publica = 'TU_CLAVE_PUBLICA',
       clave_privada = 'TU_CLAVE_PRIVADA',
       entorno = 'sandbox'  -- cambiar a 'production' cuando esté listo
   WHERE id = 1;
   ```

---

## Agregar imágenes de productos

Las imágenes van en `assets/img/productos/`. Los nombres deben coincidir con el campo `imagen_url` en la tabla `productos`.

Si no tienes imágenes, se muestra el placeholder `default.jpg` automáticamente.

---

## Estructura de archivos

```
tienda-en-linea/
├── application/
│   ├── config/          ← Configuración (database, routes, settings)
│   ├── controllers/     ← Tienda, Carrito, Pago, Admin
│   ├── models/          ← Producto_model, Pedido_model, Pasarela_model
│   └── views/           ← Vistas HTML (layouts, tienda, pago, admin)
├── assets/
│   ├── css/tienda.css   ← Estilos personalizados
│   ├── js/carrito.js    ← Lógica del carrito
│   └── img/productos/   ← Imágenes de productos
├── database/
│   └── tienda_db.sql    ← Script SQL completo
├── system/              ← Núcleo de CodeIgniter 3
├── index.php            ← Entry point
└── .htaccess            ← URLs limpias (sin index.php)
```
