import 'package:flutter/material.dart';
import '../theme/pk_colors.dart';

/// AppColors actúa como puente compatible hacia el sistema visual oficial (PKColors).
/// Fuente de verdad: docs/DISENO-VISUAL.md
class AppColors {
  // Paleta oficial Warm Minimalism PuntoKampus
  static const Color primary = PKColors.primario;
  static const Color primaryDark = PKColors.primarioPresionado;
  static const Color primaryLight = PKColors.primarioSuave;
  static const Color accent = PKColors.primario;
  static const Color accentLight = PKColors.primarioSuave;

  // Estados
  static const Color success = PKColors.okTexto;
  static const Color successLight = PKColors.okFondo;
  static const Color warning = PKColors.avisoTexto;
  static const Color warningLight = PKColors.avisoFondo;
  static const Color error = PKColors.error;
  static const Color errorLight = PKColors.errorFondo;

  // Superficies y textos
  static const Color background = PKColors.fondo;
  static const Color surface = PKColors.tarjeta;
  static const Color text = PKColors.titulo;
  static const Color textMuted = PKColors.secundario;
  static const Color border = PKColors.borde;
}
