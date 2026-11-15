import 'package:flutter/material.dart';
import 'pk_colors.dart';

/// ──────────────────────────────────────────────────────────────────────────
/// PK_TEXT_STYLES — Estilos tipográficos centralizados
/// Fuente: DM Sans con respaldo a la fuente del sistema
/// ──────────────────────────────────────────────────────────────────────────
class PKTextStyles {
  PKTextStyles._();

  // ── Títulos ──────────────────────────────────────────────────────────────
  static const TextStyle tituloPantalla = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 28,
    fontWeight: FontWeight.w700,
    color: PKColors.titulo,
    height: 1.2,
  );

  static const TextStyle tituloSeccion = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 20,
    fontWeight: FontWeight.w700,
    color: PKColors.titulo,
    height: 1.3,
  );

  static const TextStyle tituloTarjeta = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 17,
    fontWeight: FontWeight.w600,
    color: PKColors.titulo,
    height: 1.3,
  );

  // ── Cuerpo ───────────────────────────────────────────────────────────────
  static const TextStyle cuerpo = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 16,
    fontWeight: FontWeight.w400,
    color: PKColors.titulo,
    height: 1.5,
  );

  static const TextStyle cuerpoSecundario = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 14,
    fontWeight: FontWeight.w400,
    color: PKColors.secundario,
    height: 1.4,
  );

  // ── Etiquetas ─────────────────────────────────────────────────────────────
  static const TextStyle etiqueta = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 13,
    fontWeight: FontWeight.w500,
    color: PKColors.secundario,
    height: 1.3,
  );

  // ── Botón ─────────────────────────────────────────────────────────────────
  static const TextStyle boton = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 16,
    fontWeight: FontWeight.w600,
    height: 1.0,
  );

  // ── Precio ────────────────────────────────────────────────────────────────
  static const TextStyle precio = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 20,
    fontWeight: FontWeight.w700,
    color: PKColors.primario,
    height: 1.1,
  );

  static const TextStyle precioGrande = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 24,
    fontWeight: FontWeight.w800,
    color: PKColors.primario,
    height: 1.1,
  );

  // ── Logo PuntoKampus ──────────────────────────────────────────────────────
  static const TextStyle logoPunto = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 20,
    fontWeight: FontWeight.w800,
    color: PKColors.primario,
  );

  static const TextStyle logoKampus = TextStyle(
    fontFamily: 'DMSans',
    fontSize: 20,
    fontWeight: FontWeight.w800,
    color: PKColors.primarioSuaveTexto,
  );
}
