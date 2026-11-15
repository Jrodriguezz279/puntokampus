# Decisiones de diseño — PuntoKampus

> Registro de decisiones técnicas tomadas con autonomía del agente durante la implementación.
> Formato: Tema | Opciones consideradas | Elegida | Por qué

---

## R17 — Confirmación de entrega con código (adelantado de Tramo 2, aprobado por el dueño del producto el 2026-11-15)

| Tema | Opciones consideradas | Elegida | Por qué |
|---|---|---|---|
| **Longitud del código** | 4 dígitos (PRD sugiere), 6 dígitos, UUID | **4 dígitos numéricos** | El PRD sugiere 4; es suficientemente único para el contexto (máx. 10 000 combinaciones, con bloqueo tras 5 intentos = riesgo aceptable). Fácil de dictar verbalmente. |
| **Cifrado del código** | bcrypt/argon2 (hash no reversible), AES-256 (`Crypt::encryptString`), sin cifrar | **`Crypt::encryptString` / `Crypt::decryptString` de Laravel (AES-256-CBC)** | bcrypt no es reversible, así que no podría mostrarse al comprador. Sin cifrar violaría la confidencialidad. `Crypt` es la herramienta oficial de Laravel, ya disponible sin paquetes extra. |
| **Campo para almacenar** | Mismo campo `codigo` del pedido, campo nuevo `codigo_entrega` | **Campo nuevo `codigo_entrega` (string 500, nullable)** | El campo `codigo` identifica el pedido públicamente (PK-XXXXXX). Mezclarlo con el código de entrega rompería la semántica y el `$hidden`. |
| **Campos auxiliares** | Solo `codigo_entrega`, con lógica en el controlador | **`intentos_codigo` (tinyint) + `codigo_bloqueado` (boolean)** | Guardar el contador en la BD es más seguro y permite auditoría. El flag booleano hace trivial la consulta para el admin. |
| **Ruta API del vendedor** | `POST`, `PATCH`, `PUT` con distintos paths | **`PUT /api/v1/mi-tienda/pedidos/{codigo}/confirmar-entrega`** | Consistente con el patrón REST ya usado en el proyecto (`PUT /entregado`, `PUT /cancelar`). El verbo `PUT` es idempotente semánticamente. |
| **Bloqueo tras intentos fallidos** | 3 intentos, 5 intentos, 10 intentos | **5 intentos** | Balance entre usabilidad (el vendedor puede equivocarse al teclear) y seguridad (10 000 / 5 = 2 000 ataques de fuerza bruta necesarios antes del bloqueo, prácticamente imposible en contexto presencial). |
| **Desbloqueo** | Automático por tiempo, solo admin, comprador solicita nuevo código | **Solo admin desde panel Filament** | Mantiene control. El flujo es presencial, así que el comprador puede mostrar su pantalla al admin para resolver el caso. Se añadió acción "Desbloquear" en `PedidoResource` de Filament. |
| **Visibilidad del código para el comprador** | Siempre visible en lista, solo en detalle, solo cuando pendiente | **Solo en `GET /mis-pedidos/{codigo}` (detalle), y solo si estado = pendiente y no bloqueado** | Minimiza exposición. En la lista `/mis-pedidos` el código no aparece. |
| **Manejo de código en la lista `/mis-pedidos`** | Incluirlo en todos los pedidos, solo en pendientes, nunca | **Nunca en la lista; solo en el detalle** | La lista puede ser interceptada más fácilmente en caché. El detalle es una petición específica que ya garantiza autenticación y autoría. |
| **Limpiar el código tras entrega exitosa** | Dejar el cifrado, poner null | **`null` (limpiar el campo)** | Una vez entregado el pedido, el código no tiene uso. Limpiar el campo reduce el riesgo de exposición futura y es consistente con el principio de mínimo privilegio. |
| **¿Qué pasa si el comprador no puede mostrar el código?** | QR, pantalla de recuperación en la app, contactar admin | **Contactar al admin para desbloqueo o confirmación manual desde panel** | Sin QR (Tramo 2). La confirmación manual desde panel es la opción más segura y auditable. |
| **Validación de `hora_entrega` en el servidor** | Solo validar que sea futura (`after:now`), validar rango 30min-7días | **`after:+29 minutes` y `before:+7 days`** | El PRD (A4) especifica mínimo 30 min y máximo 7 días. Se usa "+29 minutes" para dar margen a la latencia de red sin romper el límite de 30 min. |
| **El vendedor nunca recibe `codigo_entrega`** | Filtrar en cada controlador, usar `$hidden` en el modelo | **`$hidden` en el modelo `Pedido` + `PedidoResource` solo añade el campo cuando el comprador autenticado es dueño del pedido** | `$hidden` es la protección de primera capa. `PedidoResource` agrega una segunda capa explícita: compara `auth()->id()` con `comprador_id` antes de descifrar. |

---

*Documento actualizado: 2026-11-15. Generado por Antigravity (Gemini / Claude Sonnet 4.6).*
