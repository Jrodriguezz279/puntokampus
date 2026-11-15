# PuntoKampus: reglas del agente

Vitrina digital para que los estudiantes de la Universidad Técnica de Babahoyo (UTB) vendan a la
comunidad universitaria. Requisitos completos en docs/PRD-MAESTRO.md. Léelo antes de programar algo nuevo.

## Estructura
- servidor/ : Laravel (PHP 8.3+) con API REST en /api/v1, PostgreSQL, Laravel Sanctum (tokens), Filament (panel en /admin).
- app/      : Flutter (Material 3). Un solo código para Android y web.
- docs/     : PRD-MAESTRO.md, registro-ia.md, ideas-tramo-2.md.

## Diseño visual
- La fuente de verdad visual es docs/DISENO-VISUAL.md. Los colores, tipografías, radios y sombras salen
  SOLO de los tokens definidos ahí, centralizados en un único archivo de tema:
  app/lib/theme/ (Flutter) y la configuración de colores del panel (Filament).
- Prohibido escribir colores hexadecimales, tamaños de letra o radios sueltos dentro de las pantallas.
  Siempre se usan los tokens del tema.
- Prohibido fondo blanco puro (#FFFFFF) o negro puro (#000000) en cualquier pantalla o componente.
- docs/PROMPTS-STITCH.md solo describe contenido y textos de cada pantalla; su paleta no se usa.
- Un solo modo visual (claro cálido). No hay modo oscuro en el tramo 1.

## Reglas que no se rompen
1. Fuente de verdad: las tablas, campos, rutas y reglas R1 a R31 de docs/PRD-MAESTRO.md. Nombres de tablas,
   campos y rutas en español y sin tildes, exactamente como están escritos. Si algo no está, pregunta.
2. No agregues funciones del tramo 2 ni nada que no esté en el alcance del tramo 1.
3. Todo texto visible al usuario va en español, de tú, con las palabras del catálogo de textos (parte 8.6).
   Código, variables y comentarios pueden ir en inglés, salvo nombres de tablas, campos y rutas.
   Vocabulario fijo: "tienda" (no negocio/local), "pedido" (no orden), "vitrina" (no catálogo).
4. Toda regla de negocio se valida en el servidor. La app valida solo para ayudar; el servidor decide
   (el total del pedido siempre lo calcula el servidor).
5. Cada ruta protegida comprueba que el usuario solo toca lo suyo. Cada ruta con candado lleva una prueba
   con un usuario ajeno que debe recibir 403 o 404.
6. Cada regla R1..R31 implementada lleva al menos una prueba automática (Pest en servidor; widget tests en Flutter
   para formularios de tienda, producto y pedido).
7. Nunca subir .env, contraseñas, claves ni tokens. Mantener .env.example actualizado.
8. No instales paquetes sin decir cuál, para qué y si está mantenido. Prefiere lo que ya trae Laravel/Flutter.
9. Fotos de comprobantes: almacenamiento privado (fuera de public/), servidas solo por el servidor a un
   admin o al dueño de la solicitud.
10. Toda pantalla con datos tiene estado de carga (esqueletos), vacío y error. Botones de al menos 48x48 dp,
    etiquetas visibles sobre los campos, contraste 4.5:1, texto normal 16, mínimo 12.
11. Antes de escribir código di qué archivos vas a crear/cambiar y por qué. Cambios pequeños, uno por historia.
    Al terminar, di qué prueba de las historias ya pasa.
12. Si una instrucción mía contradice el PRD, avísame antes de seguir.
13. Si dudas de una versión de Laravel, Filament o Flutter, consulta la documentación oficial en vez de suponer.
14. Commits pequeños en español con formato "H05: crear tienda" o "F2: tablas y migraciones".

