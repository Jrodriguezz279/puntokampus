import 'package:flutter/material.dart';

/// ──────────────────────────────────────────────────────────────────────────
/// PK_RADII — Radios de borde centralizados
/// ──────────────────────────────────────────────────────────────────────────
class PKRadii {
  PKRadii._();
  static const double tarjeta    = 16;
  static const double boton      = 12;
  static const double campo      = 12;
  static const double chip       = 999;
  static const double imagen     = 12;
  static const double modal      = 16;

  static BorderRadius get tarjetaAll => BorderRadius.circular(tarjeta);
  static BorderRadius get botonAll   => BorderRadius.circular(boton);
  static BorderRadius get campoAll   => BorderRadius.circular(campo);
  static BorderRadius get chipAll    => BorderRadius.circular(chip);
  static BorderRadius get imagenAll  => BorderRadius.circular(imagen);
  static BorderRadius get modalTop   => const BorderRadius.vertical(top: Radius.circular(modal));
}

/// ──────────────────────────────────────────────────────────────────────────
/// PK_SHADOWS — Sombra suave única
/// ──────────────────────────────────────────────────────────────────────────
class PKShadows {
  PKShadows._();
  static const List<BoxShadow> tarjeta = [
    BoxShadow(
      color: Color(0x0F171717), // negro 6%
      offset: Offset(0, 2),
      blurRadius: 12,
    ),
  ];
}

/// ──────────────────────────────────────────────────────────────────────────
/// PK_SPACING — Espaciado en múltiplos de 4
/// ──────────────────────────────────────────────────────────────────────────
class PKSpacing {
  PKSpacing._();
  static const double xs   = 4;
  static const double sm   = 8;
  static const double md   = 12;
  static const double base = 16;
  static const double lg   = 24;
  static const double xl   = 32;
  static const double xxl  = 48;

  /// Margen lateral de pantalla
  static const double screenH = 16;
  /// Separación entre secciones
  static const double section = 24;
}

/// ──────────────────────────────────────────────────────────────────────────
/// PK_BREAKPOINTS — Anchos para diseño responsivo (LayoutBuilder / MediaQuery)
/// ──────────────────────────────────────────────────────────────────────────
class PKBreakpoints {
  PKBreakpoints._();
  /// < 600: móvil
  static const double mobile  = 600;
  /// 600–1024: tableta
  static const double tablet  = 1024;
  /// > 1024: escritorio
}
