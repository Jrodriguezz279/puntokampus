import 'package:flutter/material.dart';

/// ──────────────────────────────────────────────────────────────────────────
/// PK_COLORS — Tokens de color centralizados de PuntoKampus
/// Fuente de verdad: docs/DISENO-VISUAL.md
/// PROHIBIDO usar colores hexadecimales fuera de este archivo.
/// ──────────────────────────────────────────────────────────────────────────
class PKColors {
  PKColors._();

  // ── Paleta principal ────────────────────────────────────────────────────
  static const Color primario         = Color(0xFFCC4E01);
  static const Color primarioPresionado = Color(0xFFA83F00);
  static const Color primarioSuave    = Color(0xFFF7E3D6);
  static const Color primarioSuaveTexto = Color(0xFF8A3500);
  static const Color sobrePrimario    = Color(0xFFFFFFFF); // único uso de blanco puro

  // ── Superficies ─────────────────────────────────────────────────────────
  static const Color fondo            = Color(0xFFFAF5F2);
  static const Color tarjeta          = Color(0xFFFEFAF8);

  // ── Texto ───────────────────────────────────────────────────────────────
  static const Color titulo           = Color(0xFF171717);
  static const Color secundario       = Color(0xFF77716D);
  static const Color secundarioFuerte = Color(0xFF6B655F); // para texto esencial en gris

  // ── Bordes ──────────────────────────────────────────────────────────────
  static const Color borde            = Color(0xFFD9D2CC);

  // ── Estados positivos ────────────────────────────────────────────────────
  static const Color okFondo          = Color(0xFFD8F0D5);
  static const Color okTexto          = Color(0xFF176B27);

  // ── Errores ─────────────────────────────────────────────────────────────
  static const Color error            = Color(0xFFB71C1C);
  static const Color errorFondo       = Color(0xFFF8DCDC);

  // ── Avisos ──────────────────────────────────────────────────────────────
  static const Color avisoFondo       = Color(0xFFF6E7C8);
  static const Color avisoTexto       = Color(0xFF7A4F00);

  // ── Neutro / deshabilitado ───────────────────────────────────────────────
  static const Color neutroFondo      = Color(0xFFECE6E1);
  static const Color deshabilitado    = Color(0xFFE4DDD7);
  static const Color deshabilitadoTexto = Color(0xFFA39C97);

  // ── Overlay de modal ─────────────────────────────────────────────────────
  static const Color modalOverlay     = Color(0x66171717); // titulo al 40%
}
