# docs/registro-ia.md

Registro de todas las sesiones en las que se usó inteligencia artificial para construir PuntoKampus.
Una fila por sesión de trabajo. Llenar al terminar cada fase.

| Fecha | Quién | Herramienta | Para qué | Qué se revisó a mano |
|---|---|---|---|---|
| 2026-11-12 | Agente | Antigravity + Claude Sonnet 4.6 (Thinking) | F0: leer PRD, crear estructura de repositorio y validar entorno | Verificar que la estructura de carpetas y archivos es correcta, completa y coincide con el PRD |
| 2026-11-12 | Agente | Antigravity + Gemini 3.8 Flash | F1: instalación de Laravel, Sanctum, Filament, PostgreSQL, 12 migraciones, modelos, seeders y endpoint /api/v1/salud | Verificación de tests automáticos en verde contra PostgreSQL real, validación de unaccent y aislamiento Filament |
| 2026-11-13 | Agente | Antigravity + Gemini 3.8 Flash | F2: Backend de autenticación sin contraseña (código 6 dígitos, throttling, UTB domain validator, Sanctum tokens, R1–R5, H01–H03) | 20 pruebas automáticas en verde en Feature tests, validación de rate limiting, 5 intentos máximos y revocación de tokens |
| 2026-11-13 | Agente | Antigravity + Gemini 3.8 Flash | F3: Tiendas, productos con fotos (máx 3, 1080px), vitrina, búsqueda unaccent y panel Filament (H05–H12, R6–R10) | 35 pruebas automáticas en verde (123 aserciones) en Feature tests, validación de aislamiento, portadas premium y búsqueda insensible a mayúsculas y acentos |
| 2026-11-13 | Agente | Antigravity + Gemini 3.8 Flash | F4: Pedidos, ciclo de vida completo (comprador, vendedor, comando vencer-pedidos, R11–R19, H13–H18) | 52 pruebas automáticas en verde (192 aserciones), validación de código PK-XXXXXX, no autoregalo, total calculado en servidor y cancelación por roles |
| 2026-11-13 | Agente | Antigravity + Gemini 3.8 Flash | F5: Reportes, suscripciones Premium, panel Filament completo y comandos vencer-premium/purgar-comprobantes (H19–H26, R20–R28) | 62 pruebas automáticas en verde (233 aserciones), validación de límites de reportes, duplicidad de comprobantes, baja con cancelación en cascada y purga |
| 2026-11-13 | Agente | Antigravity + Gemini 3.8 Flash | F6: Privacidad, borrado de cuenta y páginas públicas (H04, H27, R29–R31) | 74 pruebas automáticas en verde (266 aserciones), validación de código de borrado por correo, anonimización R31, cancelación de pedidos y páginas públicas /privacidad y /borrar-cuenta |
| 2026-11-13 | Agente | Antigravity + Gemini 3.8 Flash | F7: Endurecimiento y entrega (auditoría de aislamiento 🔒, cobertura R1–R31 y H01–H27, docs de despliegue, manual de piloto y scripts de backup) | 82 pruebas automáticas en verde (275 aserciones), validación exhaustiva de aislamiento multiusuario, guía de despliegue con HTTPS, manual del piloto para UTB y scripts de respaldo |
