# SISTEMA VISUAL DE PuntoKampus (para Antigravity IDE)

> **Cómo usar**
> 1. Guarda este archivo como `docs/DISENO-VISUAL.md`.
> 2. Guarda también `PROMPTS-STITCH-PuntoKampus.md` como `docs/PROMPTS-STITCH.md`. Se usa **solo como descripción de qué lleva cada pantalla y sus textos**. Su paleta (azul/naranja/dorado) queda **anulada** por este documento.
> 3. Agrega a `AGENTS.md` la regla de la parte 1.
> 4. Lanza el `/goal` de la parte 8 (Fase D, antes de la Fase 3 del PRD).

---

# PARTE 1. REGLA PARA `AGENTS.md`

```markdown
## Diseño visual
- La fuente de verdad visual es docs/DISENO-VISUAL.md. Los colores, tipografías, radios y sombras salen
  SOLO de los tokens definidos ahí, centralizados en un único archivo de tema:
  app/lib/theme/ (Flutter) y la configuración de colores del panel (Filament).
- Prohibido escribir colores hexadecimales, tamaños de letra o radios sueltos dentro de las pantallas.
  Siempre se usan los tokens del tema.
- Prohibido fondo blanco puro (#FFFFFF) o negro puro (#000000) en cualquier pantalla o componente.
- docs/PROMPTS-STITCH.md solo describe contenido y textos de cada pantalla; su paleta no se usa.
- Un solo modo visual (claro cálido). No hay modo oscuro en el tramo 1.
```

---

# PARTE 2. IDENTIDAD VISUAL

**Estilo:** Warm Minimalism, UI moderna y diseño editorial limpio.
**Sensación:** elegante, cálida, ordenada y profesional; mucho aire, jerarquía tipográfica clara, tarjetas suaves. Debe sentirse como una revista de feria universitaria, no como una app corporativa fría.

> Nota: se pidió usar una imagen de referencia, pero en este chat no llegó ninguna. Si la tienes, adjúntala al agente en Antigravity junto con este documento para afinar espaciados y composición. Los colores obligatorios no cambian.

---

# PARTE 3. TOKENS DE COLOR (obligatorios)

| Token | Valor | Uso |
|---|---|---|
| `primario` | `#CC4E01` | Botones principales, elementos activos, acentos |
| `sobre_primario` | `#FFFFFF` | Texto sobre botones primarios (único uso permitido del blanco puro) |
| `fondo` | `#FAF5F2` | Fondo general de pantallas |
| `tarjeta` | `#FEFAF8` | Fondo de tarjetas, hojas, modales, campos |
| `titulo` | `#171717` | Títulos y texto principal |
| `secundario` | `#77716D` | Textos secundarios, ayudas, etiquetas pequeñas |
| `borde` | `#D9D2CC` | Bordes de tarjetas, campos y divisores |
| `ok_fondo` / `ok_texto` | `#D8F0D5` / `#176B27` | Estados positivos (Entregado, aprobada, éxito) |
| `error` | `#B71C1C` | Errores, acciones destructivas |

**Colores derivados** (tonos del mismo naranja, para no introducir colores nuevos; el agente debe usar exactamente estos):

| Token | Valor | Uso |
|---|---|---|
| `primario_presionado` | `#A83F00` | Botón primario al tocarlo |
| `primario_suave` | `#F7E3D6` | Fondo de chips seleccionados, insignias, pastillas activas |
| `primario_suave_texto` | `#8A3500` | Texto sobre `primario_suave` |
| `error_fondo` | `#F8DCDC` | Fondo de avisos de error o "Vencido" |
| `aviso_fondo` / `aviso_texto` | `#F6E7C8` / `#7A4F00` | Estado "Pendiente" |
| `neutro_fondo` | `#ECE6E1` | Estado "Cancelado", esqueletos de carga |
| `deshabilitado` | `#E4DDD7` (texto `#A39C97`) | Controles deshabilitados |

**Insignias**
- "Matriculado" (plan gratis): fondo `tarjeta`, borde `borde`, texto `secundario`, ícono de birrete lineal.
- "Sacó 10" (Premium): fondo `primario_suave`, texto `primario_suave_texto`, ícono de estrella lineal. Es más llamativa que "Matriculado" a propósito.

**Estados de pedido:** Pendiente = `aviso_*` · Entregado = `ok_*` · Cancelado = `neutro_fondo` con texto `secundario` · Vencido = `error_fondo` con texto `error`. Siempre con texto, nunca solo con color.

## Contraste (revisar antes de cerrar la fase)

Cálculo aproximado con la fórmula WCAG; el agente debe verificarlo con una herramienta y reportarlo:
- Blanco sobre `#CC4E01`: ≈ 4.5:1. Cumple el mínimo para botones, con muy poco margen. Usar siempre texto en negrita, 16 px o más.
- `#CC4E01` como **texto** sobre `fondo`: ≈ 4.2:1, por debajo de 4.5. Para enlaces y texto naranja pequeño usar `primario_suave_texto` (`#8A3500`) o poner el texto naranja solo en tamaño grande/negrita (≥ 18 px).
- `secundario` `#77716D` sobre `fondo`: ≈ 4.4:1 y sobre `tarjeta` ≈ 4.6:1. Úsalo solo en tarjetas o en texto no esencial; si algo importante debe ir en gris, usar `#6B655F`.
- `error` sobre `fondo`: ≈ 5.6:1. Cumple.

---

# PARTE 4. TIPOGRAFÍA, FORMA Y ESPACIADO

**Tipografía:** una sola familia sans moderna y legible, por ejemplo **DM Sans** (o Plus Jakarta Sans), cargada con el paquete `google_fonts` o incluida como activo para que funcione sin internet. Con respaldo a la fuente del sistema.

| Estilo | Tamaño | Peso | Color |
|---|---|---|---|
| Título de pantalla | 28 | Negrita (700) | `titulo` |
| Título de sección | 20 | Negrita (700) | `titulo` |
| Título de tarjeta | 16–18 | Seminegrita (600) | `titulo` |
| Cuerpo | 16 | Normal (400) | `titulo` |
| Texto secundario | 14 | Normal | `secundario` |
| Etiqueta/ayuda | 12–13 (nunca menos de 12) | Medio (500) | `secundario` |
| Botón | 16 | Seminegrita (600) | según botón |

**Radios:** tarjetas y hojas 16 · botones y campos 12 · chips e insignias 999 (pastilla) · imágenes de producto 12.
**Bordes:** 1 px `borde` en tarjetas, campos y divisores.
**Sombras:** una sola, suave y discreta: desplazamiento 0/2, desenfoque 12, color negro a 6 % de opacidad. Sin sombras fuertes.
**Espaciado (múltiplos de 4):** 4, 8, 12, 16, 24, 32, 48. Margen lateral de pantalla 16 en móvil; separación entre secciones 24–32; separación entre elementos tocables ≥ 8.
**Iconografía:** íconos lineales (trazo 1.5–2 px) y minimalistas, por ejemplo Lucide o los íconos "outlined" de Material. Un solo set en toda la app. Ningún botón solo con ícono: siempre con texto o con etiqueta semántica.
**Fondos:** nunca blanco ni negro puros; las superficies usan `fondo` o `tarjeta`.

---

# PARTE 5. COMPONENTES (misma identidad en todos lados)

| Componente | Regla |
|---|---|
| Botón primario | Fondo `primario`, texto blanco en negrita, radio 12, alto mínimo 48, ancho completo en móvil. Presionado `primario_presionado`. Deshabilitado `deshabilitado` |
| Botón secundario | Fondo `tarjeta`, borde `borde`, texto `titulo` |
| Botón de texto | Sin fondo, texto `primario_suave_texto` en negrita |
| Botón destructivo | Texto o borde `error`; el definitivo ("Borrar mi cuenta para siempre") con fondo `error` y texto blanco |
| Campo de texto | Fondo `tarjeta`, borde `borde`, radio 12, etiqueta visible encima, foco con borde `primario` de 2 px, error con borde `error` y mensaje debajo |
| Tarjeta | Fondo `tarjeta`, borde 1 px `borde`, radio 16, sombra suave, relleno 16 |
| Chip de categoría | Normal: `tarjeta` + borde. Seleccionado: `primario_suave` + texto `primario_suave_texto` |
| Barra inferior | Fondo `tarjeta`, borde superior `borde`; activa con pastilla `primario_suave` y texto/ícono `primario_suave_texto` |
| Modal / hoja inferior | Fondo `tarjeta`, radio 16 arriba, fondo detrás atenuado con `titulo` al 40 % |
| Avisos (snackbar) | Éxito `ok_*`; error `error_fondo` + texto `error` |
| Esqueletos de carga | `neutro_fondo`, misma forma del contenido |
| Interruptor | Encendido `primario`; apagado `borde` |
| Tablas (web/panel) | Encabezado `titulo` en 600 sobre `fondo`, filas sobre `tarjeta`, divisores `borde`, hover `fondo` |

---

# PARTE 6. RESPONSIVE (obligatorio)

La misma app de Flutter debe verse bien en tres anchos:

| Ancho | Diseño |
|---|---|
| Móvil < 600 | Una columna, barra inferior de 4 secciones, tarjetas de ancho completo, cuadrícula de productos de 2 columnas |
| Tableta 600–1024 | Contenido centrado con ancho máximo 720; cuadrícula de productos de 3 columnas; barra de navegación lateral compacta (`NavigationRail`) en lugar de la inferior |
| Escritorio > 1024 | Contenido con ancho máximo 1120 centrado; barra superior (o lateral) con "Vitrina", "Mis pedidos", "Mi tienda", "Cuenta"; tiendas en cuadrícula de 3–4 columnas; formularios en columna de 560 |

Usar `LayoutBuilder`/`MediaQuery` con breakpoints definidos **una sola vez** en el archivo de tema. Probar en 360×640, 768×1024 y 1440×900. Nada debe desbordarse ni mostrar scroll horizontal.

---

# PARTE 7. APLICACIÓN A PANTALLAS Y AL PANEL

**App (P01–P22):** los contenidos y textos están en `docs/PROMPTS-STITCH.md` y en la parte 8 del PRD maestro; aquí solo cambia el estilo. Reglas por pantalla clave:
- **P01 Bienvenida:** fondo `fondo`, logotipo provisional en `primario`, ilustración lineal simple en `primario` y `borde`, título editorial grande en `titulo`, botón primario "Entrar con mi correo UTB".
- **P05/P06/P07/P08:** fotos de producto grandes con radio 12; precios en negrita `titulo`; "Agotado" como pastilla `neutro_fondo`; botón "Agregar" primario.
- **P10/P14/P16/P18:** formularios en tarjetas por sección, con título de sección en negrita; botón primario fijo abajo.
- **P11/P12/P17:** estados de pedido como pastillas con los colores de la parte 3.
- **Pantallas vacías:** ilustración lineal pequeña, título en negrita y botón de acción.

**Panel de administrador (Filament):** aplicar la misma identidad con la personalización de colores y tema de Filament: color primario `#CC4E01`, fondo `#FAF5F2`, tarjetas `#FEFAF8`, bordes `#D9D2CC`, la misma tipografía, y estados con los colores de la parte 3 (por ejemplo, "Aprobada" `ok_*`, "Rechazada" `error_fondo`). Sin modo oscuro (desactivarlo). Tablas y modales con el estilo de la parte 5.

**Páginas web del servidor** (`/privacidad`, `/borrar-cuenta`): mismo estilo, usando los mismos tokens.

---

# PARTE 8. `/goal` PARA ANTIGRAVITY: FASE D (DISEÑO)

Pega esto en Antigravity **después de la Fase 2** del PRD (cuando ya existe el proyecto Flutter y el acceso) y **antes** de la Fase 3. Si quieres revisar antes de que programe, ejecuta primero `/plan` con el mismo texto.

```
/goal Implementa el sistema visual de docs/DISENO-VISUAL.md en toda la app Flutter y en el panel Filament.
Usa docs/PROMPTS-STITCH.md solo como guía del contenido y los textos de cada pantalla (ignora su paleta).

Pasos:
1. Crea el tema centralizado en app/lib/theme/ (colores, tipografía, radios, sombras, espaciados,
   breakpoints, ThemeData Material 3 completo con ColorScheme, botones, campos, tarjetas, chips,
   barra de navegación, diálogos, snackbars e interruptores). Cero colores sueltos en las pantallas.
2. Crea los componentes reutilizables: BotonPrimario, BotonSecundario, TarjetaPunto, CampoEtiquetado,
   InsigniaMatriculado, InsigniaSaco10, PastillaEstadoPedido, EstadoVacio, EstadoError, Esqueleto,
   NavegacionAdaptable (barra inferior en móvil, NavigationRail en tableta, barra superior en escritorio).
3. Aplica el tema a todas las pantallas ya construidas (P01–P04 y P13) y deja las siguientes
   (P05–P22) listas para usar solo estos componentes.
4. Configura el color primario, fondo, tarjetas, bordes y tipografía del panel Filament y desactiva el modo oscuro.
5. Crea una pantalla de desarrollo /galeria-componentes (solo en modo debug) que muestre cada componente
   y estado (normal, presionado, deshabilitado, error, vacío, cargando).

Está terminado cuando:
(a) no existe ningún color hexadecimal, tamaño de letra ni radio fuera de app/lib/theme/ (verifícalo con una búsqueda
    y muéstrame el resultado);
(b) flutter analyze y flutter test pasan, con al menos una prueba de widget por componente reutilizable;
(c) con el navegador integrado, capturas de P01, P02, P03, P04, P13 y /galeria-componentes en 360×640,
    768×1024 y 1440×900, sin desbordes ni scroll horizontal;
(d) una tabla de contraste real (calculada, no estimada) para: blanco sobre primario, primario sobre fondo,
    secundario sobre fondo y tarjeta, error sobre fondo, ok_texto sobre ok_fondo, aviso_texto sobre aviso_fondo,
    con la solución aplicada donde baje de 4.5:1;
(e) capturas del panel Filament con la paleta aplicada;
(f) entregas el Walkthrough del formato de la parte 14 del PRD maestro. No avances a la Fase 3.
```

**Después de la Fase D**, cada `/goal` de las Fases 3 a 7 debe incluir esta línea en sus criterios:
*"Toda pantalla nueva usa solo los componentes y tokens de app/lib/theme/ y se ve correcta en 360, 768 y 1440 px."*
