# 🎓 PuntoKampus — Marketplace Universitario UTB

[![Desplegado en Render](https://img.shields.io/badge/Render-Live-brightgreen?logo=render)](https://puntokampus-app.onrender.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-blue?logo=postgresql)](https://postgresql.org)
[![Tests](https://img.shields.io/badge/Tests-82%20passing-success)](servidor)

Sistema de comercio electrónico para estudiantes emprendedores de la **Universidad Técnica de Babahoyo (UTB)**. Permite a los estudiantes vender y comprar productos dentro del campus, coordinando puntos de entrega y pedidos vía WhatsApp, con autenticación sin contraseñas mediante correo institucional (`@utb.edu.ec`).

🌐 **Enlace público en producción:** [https://puntokampus-app.onrender.com](https://puntokampus-app.onrender.com)  
📱 **Portal Web de Estudiantes:** [https://puntokampus-app.onrender.com/app](https://puntokampus-app.onrender.com/app)  
🛡️ **Panel de Administración:** [https://puntokampus-app.onrender.com/admin-login](https://puntokampus-app.onrender.com/admin-login)  
📡 **API Base para Flutter:** `https://puntokampus-app.onrender.com/api/v1`

---

## 📌 Aclaración Importante: ¿Qué es la "Guía del Piloto" (`docs/manual-piloto.md`)?

> ⚠️ **¿Tu grupo tiene que usar la guía del piloto ahora mismo? NO.**
>
> El archivo [`docs/manual-piloto.md`](docs/manual-piloto.md) es un **entregable formal exigido por el PRD (Fase 7)**. Está redactado para cuando el sistema esté desplegado en un servidor real en internet y se convoque a los **10 estudiantes reales de la UTB** (5 vendedores y 5 compradores) para realizar la prueba de campo en el campus universitario.
>
> Para la **revisión interna del código, pruebas locales y evaluación académica**, deben usar esta **Guía Rápida** con los usuarios de prueba ya listos.

---

## 🚀 Guía Rápida para Clonar y Levantar el Proyecto

Si eres compañero de grupo y acabas de clonar el repositorio, sigue estos sencillos pasos:

### 1. Requisitos Previos
* **PHP 8.2 o superior** con las extensiones activas en tu `php.ini`:
  * `extension=pdo_pgsql`
  * `extension=pgsql`
  * `extension=intl`
  * `extension=fileinfo`
  * `extension=gd`
* **Composer** instalado.
* **PostgreSQL 16** (crear una base de datos vacía llamada `puntokampus`).

---

### 2. Instalación Paso a Paso

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/Jrodriguezz279/puntokampus.git
   cd puntokampus/servidor
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Configurar el archivo de entorno (`.env`):**
   ```bash
   cp .env.example .env
   # En Windows PowerShell:
   # Copy-Item .env.example .env
   ```
   Abre `.env` y asegúrate de que la conexión a PostgreSQL coincida con tu entorno:
   ```ini
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=puntokampus
   DB_USERNAME=postgres
   DB_PASSWORD=tu_password_de_postgres
   ```

4. **Generar la clave de la aplicación y crear el enlace de almacenamiento:**
   ```bash
   php artisan key:generate
   php artisan storage:link
   ```

5. **Ejecutar migraciones y cargar los datos de prueba:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Este comando creará automáticamente las tablas, categorías, puntos de entrega del campus UTB, el usuario administrador y los usuarios demo de vendedor y compradora).*

6. **Iniciar el servidor local:**
   ```bash
   php artisan serve --port=8000
   ```
   El sistema estará disponible en: **`http://127.0.0.1:8000`**

---

## 👥 Usuarios de Prueba y Cómo Iniciar Sesión

El sistema cuenta con dos interfaces: el **Panel de Administración (Filament)** y el **Portal Web de Estudiantes (`/app`)**.

### 1. Panel de Administración (Super Admin)
* **URL:** [`http://127.0.0.1:8000/admin`](http://127.0.0.1:8000/admin)  
  *(o usa el atajo directo: [`http://127.0.0.1:8000/admin-login`](http://127.0.0.1:8000/admin-login))*
* **Correo:** `admin@utb.edu.ec`
* **Contraseña:** `PuntoKampus2026!`
* **¿Cómo ingresar?:** Escribe el correo y la contraseña tradicionales.
* **¿Qué revisar?:**
  * Gestión de categorías y puntos de entrega del campus UTB.
  * Moderación de tiendas activas o dadas de baja.
  * Catálogo de productos.
  * Solicitudes de plan Premium ("Sacó 10") y revisión de reportes.

---

### 2. Estudiante Vendedor (Carlos Andrés Mendoza)
* **URL:** [`http://127.0.0.1:8000/app`](http://127.0.0.1:8000/app)
* **Correo:** `carlos.vendedor@est.utb.edu.ec`
* **Tienda activa:** *"Dulces & Snacks UTB"* (con productos como Brownie Artesanal, Empanada de Pollo, Jugo Natural).
* **¿Cómo iniciar sesión?:**
  1. Ve a [`http://127.0.0.1:8000/app/login`](http://127.0.0.1:8000/app/login).
  2. Ingresa el correo `carlos.vendedor@est.utb.edu.ec` y pulsa **Continuar**.
  3. En la pantalla de verificación, **el sistema autocompleta el código generado para el entorno local** (también lo muestra en pantalla). Solo presiona **"Verificar y Entrar"**.
* **¿Qué revisar?:**
  * Pestaña **"Mi Tienda"**: Ver y editar el perfil de la tienda, horario y WhatsApp.
  * Pestaña **"Pedidos"**: Gestionar el pedido de prueba recibido (`PK-DEMO01`), cambiar su estado a *Entregado* o *Cancelado*.
  * Pestaña **"Productos"**: Crear, editar o marcar disponibilidad de productos.

---

### 3. Estudiante Compradora (María José Zambrano)
* **URL:** [`http://127.0.0.1:8000/app`](http://127.0.0.1:8000/app)
* **Correo:** `maria.compradora@est.utb.edu.ec`
* **¿Cómo iniciar sesión?:**
  1. Si tienes una sesión abierta, ve a **"Mi Cuenta"** y presiona **"Cerrar Sesión"**.
  2. Ve a [`http://127.0.0.1:8000/app/login`](http://127.0.0.1:8000/app/login).
  3. Ingresa `maria.compradora@est.utb.edu.ec` y presiona el botón de verificación.
* **¿Qué revisar?:**
  * Pestaña **"Vitrina"**: Explorar tiendas, filtrar por categorías (Comida, Accesorios, etc.) o buscar por nombre.
  * Entrar a la tienda de Carlos, agregar empanadas o brownies al carrito.
  * Seleccionar el punto de entrega en el campus (ej. *Biblioteca Central* o *Entrada Principal*).
  * Confirmar el pedido para ver el mensaje preformateado que se envía a WhatsApp con el código de orden.
  * Pestaña **"Mis Pedidos"**: Ver el historial de compras realizadas.

---

## 🧪 Pruebas Automatizadas (Test Suite)

El backend cuenta con **82 tests automatizados** que cubren autenticación, vitrina, tiendas, productos, ciclo de pedidos, reportes, privacidad y seguridad:

```bash
cd servidor
php artisan test
```

---

## 🌐 Despliegue en la Nube con Render (1-Clic)

El repositorio incluye soporte nativo para **Render** mediante Blueprint (`render.yaml`) y contenedor `Dockerfile`:

1. Crea una cuenta en [render.com](https://render.com) (puedes iniciar sesión con tu GitHub).
2. En tu Dashboard, haz clic en **New +** → **Blueprint**.
3. Selecciona tu repositorio: `Jrodriguezz279/puntokampus`.
4. Render detectará automáticamente `render.yaml` y creará:
   * La base de datos gestionada **PostgreSQL** (`puntokampus-db`).
   * El servicio web con PHP 8.2 + Apache (`puntokampus-app`).
   * Las variables de entorno enlazadas.
5. Haz clic en **Apply**. Render compilará el Dockerfile, ejecutará las migraciones y seeders, y te dará tu URL pública con HTTPS (ej: `https://puntokampus-app.onrender.com`).

---

## 📁 Estructura del Repositorio

* `/servidor`: Backend Laravel 11/12 con Filament v3, API REST y Vistas Web para Estudiantes (`/app`).
* `/app`: Aplicación móvil y cliente universal en **Flutter (Material 3)**:
  * Conectada directamente a la API en producción en Render (`https://puntokampus-app.onrender.com/api/v1`).
  * Autenticación segura con tokens Sanctum en `SharedPreferences`.
  * Navegación oficial de 4 pestañas: **Vitrina**, **Mis pedidos**, **Mi tienda**, **Cuenta**.
  * Carrito de compras con validación de tienda única y apertura directa de WhatsApp (`url_launcher`).
* `/docs`: Documentación técnica y académica del proyecto:
  * [`docs/PRD-MAESTRO.md`](docs/PRD-MAESTRO.md): Especificación de requerimientos y reglas de negocio.
  * [`docs/despliegue.md`](docs/despliegue.md): Guía de despliegue para producción (VPS, Nginx, SSL).
  * [`docs/manual-piloto.md`](docs/manual-piloto.md): Manual de usuario final para los 10 estudiantes de la prueba de campo.
  * [`docs/registro-ia.md`](docs/registro-ia.md): Bitácora de desarrollo con IA.

---

## 👥 Equipo de Desarrollo
Proyecto de ingeniería de software para la **Universidad Técnica de Babahoyo (UTB)**.
