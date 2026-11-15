import 'package:flutter/material.dart';
import 'pk_colors.dart';
import 'pk_dimensions.dart';

/// ──────────────────────────────────────────────────────────────────────────
/// PK_TEXT_STYLES — Estilos tipográficos centralizados
/// Fuente: DM Sans (google_fonts) con fallback al sistema
/// ──────────────────────────────────────────────────────────────────────────
class PKTextStyles {
  PKTextStyles._();

  // ── Títulos ──────────────────────────────────────────────────────────────
  static const TextStyle tituloPantalla = TextStyle(
    fontSize: 28,
    fontWeight: FontWeight.w700,
    color: PKColors.titulo,
    height: 1.2,
  );

  static const TextStyle tituloSeccion = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w700,
    color: PKColors.titulo,
    height: 1.3,
  );

  static const TextStyle tituloTarjeta = TextStyle(
    fontSize: 17,
    fontWeight: FontWeight.w600,
    color: PKColors.titulo,
    height: 1.3,
  );

  // ── Cuerpo ───────────────────────────────────────────────────────────────
  static const TextStyle cuerpo = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w400,
    color: PKColors.titulo,
    height: 1.5,
  );

  static const TextStyle cuerpoSecundario = TextStyle(
    fontSize: 14,
    fontWeight: FontWeight.w400,
    color: PKColors.secundario,
    height: 1.4,
  );

  // ── Etiquetas ─────────────────────────────────────────────────────────────
  static const TextStyle etiqueta = TextStyle(
    fontSize: 13,
    fontWeight: FontWeight.w500,
    color: PKColors.secundario,
    height: 1.3,
  );

  // ── Botón ─────────────────────────────────────────────────────────────────
  static const TextStyle boton = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    height: 1.0,
  );

  // ── Precio ────────────────────────────────────────────────────────────────
  static const TextStyle precio = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w700,
    color: PKColors.primario,
    height: 1.1,
  );

  static const TextStyle precioGrande = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w800,
    color: PKColors.primario,
    height: 1.1,
  );

  // ── Logo PuntoKampus ──────────────────────────────────────────────────────
  static TextStyle get logoPunto => const TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w800,
    color: PKColors.primario,
  );
  static TextStyle get logoKampus => const TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w800,
    color: PKColors.primarioSuaveTexto,
  );
}

/// ──────────────────────────────────────────────────────────────────────────
/// PK_THEME — ThemeData Material 3 completo
/// ──────────────────────────────────────────────────────────────────────────
class PKTheme {
  PKTheme._();

  static ThemeData get light {
    final colorScheme = ColorScheme(
      brightness: Brightness.light,
      primary: PKColors.primario,
      onPrimary: PKColors.sobrePrimario,
      primaryContainer: PKColors.primarioSuave,
      onPrimaryContainer: PKColors.primarioSuaveTexto,
      secondary: PKColors.secundario,
      onSecondary: PKColors.sobrePrimario,
      secondaryContainer: PKColors.neutroFondo,
      onSecondaryContainer: PKColors.titulo,
      tertiary: PKColors.avisoTexto,
      onTertiary: PKColors.sobrePrimario,
      tertiaryContainer: PKColors.avisoFondo,
      onTertiaryContainer: PKColors.avisoTexto,
      error: PKColors.error,
      onError: PKColors.sobrePrimario,
      errorContainer: PKColors.errorFondo,
      onErrorContainer: PKColors.error,
      surface: PKColors.tarjeta,
      onSurface: PKColors.titulo,
      surfaceContainerHighest: PKColors.fondo,
      onSurfaceVariant: PKColors.secundario,
      outline: PKColors.borde,
      outlineVariant: PKColors.borde,
      shadow: PKColors.titulo,
      scrim: PKColors.titulo,
      inverseSurface: PKColors.titulo,
      onInverseSurface: PKColors.fondo,
      inversePrimary: PKColors.primarioSuave,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: PKColors.fondo,
      fontFamily: 'DMSans',

      // ── AppBar ────────────────────────────────────────────────────────────
      appBarTheme: AppBarTheme(
        backgroundColor: PKColors.tarjeta,
        foregroundColor: PKColors.titulo,
        elevation: 0,
        scrolledUnderElevation: 1,
        shadowColor: const Color(0x14171717),
        centerTitle: false,
        titleTextStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: PKColors.titulo,
        ),
        iconTheme: const IconThemeData(color: PKColors.titulo),
        actionsIconTheme: const IconThemeData(color: PKColors.titulo),
        surfaceTintColor: Colors.transparent,
      ),

      // ── Tarjetas ─────────────────────────────────────────────────────────
      cardTheme: CardThemeData(
        color: PKColors.tarjeta,
        elevation: 0,
        shadowColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: PKRadii.tarjetaAll,
          side: const BorderSide(color: PKColors.borde, width: 1),
        ),
        margin: EdgeInsets.zero,
      ),

      // ── Botón Primario (ElevatedButton) ───────────────────────────────────
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ButtonStyle(
          backgroundColor: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.disabled)) return PKColors.deshabilitado;
            if (states.contains(WidgetState.pressed)) return PKColors.primarioPresionado;
            return PKColors.primario;
          }),
          foregroundColor: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.disabled)) return PKColors.deshabilitadoTexto;
            return PKColors.sobrePrimario;
          }),
          minimumSize: const WidgetStatePropertyAll(Size(double.infinity, 48)),
          shape: const WidgetStatePropertyAll(RoundedRectangleBorder(
            borderRadius: BorderRadius.all(Radius.circular(PKRadii.boton)),
          )),
          elevation: const WidgetStatePropertyAll(0),
          textStyle: const WidgetStatePropertyAll(TextStyle(
            fontFamily: 'DMSans',
            fontSize: 16,
            fontWeight: FontWeight.w600,
          )),
        ),
      ),

      // ── Botón Secundario (OutlinedButton) ─────────────────────────────────
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: ButtonStyle(
          backgroundColor: const WidgetStatePropertyAll(PKColors.tarjeta),
          foregroundColor: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.disabled)) return PKColors.deshabilitadoTexto;
            return PKColors.titulo;
          }),
          side: WidgetStateProperty.resolveWith((states) {
            if (states.contains(WidgetState.disabled)) {
              return const BorderSide(color: PKColors.deshabilitado);
            }
            return const BorderSide(color: PKColors.borde);
          }),
          minimumSize: const WidgetStatePropertyAll(Size(double.infinity, 48)),
          shape: const WidgetStatePropertyAll(RoundedRectangleBorder(
            borderRadius: BorderRadius.all(Radius.circular(PKRadii.boton)),
          )),
          elevation: const WidgetStatePropertyAll(0),
          textStyle: const WidgetStatePropertyAll(TextStyle(
            fontFamily: 'DMSans',
            fontSize: 16,
            fontWeight: FontWeight.w600,
          )),
        ),
      ),

      // ── Botón de Texto ────────────────────────────────────────────────────
      textButtonTheme: TextButtonThemeData(
        style: ButtonStyle(
          foregroundColor: const WidgetStatePropertyAll(PKColors.primarioSuaveTexto),
          textStyle: const WidgetStatePropertyAll(TextStyle(
            fontFamily: 'DMSans',
            fontSize: 15,
            fontWeight: FontWeight.w600,
          )),
        ),
      ),

      // ── Campos de texto ───────────────────────────────────────────────────
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: PKColors.tarjeta,
        labelStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 14,
          color: PKColors.secundario,
        ),
        hintStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 15,
          color: PKColors.secundario,
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: PKRadii.campoAll,
          borderSide: const BorderSide(color: PKColors.borde),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: PKRadii.campoAll,
          borderSide: const BorderSide(color: PKColors.borde),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: PKRadii.campoAll,
          borderSide: const BorderSide(color: PKColors.primario, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: PKRadii.campoAll,
          borderSide: const BorderSide(color: PKColors.error),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: PKRadii.campoAll,
          borderSide: const BorderSide(color: PKColors.error, width: 2),
        ),
        errorStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 12,
          color: PKColors.error,
        ),
      ),

      // ── Chips ─────────────────────────────────────────────────────────────
      chipTheme: ChipThemeData(
        backgroundColor: PKColors.tarjeta,
        selectedColor: PKColors.primarioSuave,
        disabledColor: PKColors.deshabilitado,
        labelStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 13,
          fontWeight: FontWeight.w500,
          color: PKColors.titulo,
        ),
        secondaryLabelStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: PKColors.primarioSuaveTexto,
        ),
        side: const BorderSide(color: PKColors.borde),
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(PKRadii.chip)),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        showCheckmark: false,
      ),

      // ── Barra de navegación inferior ──────────────────────────────────────
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: PKColors.tarjeta,
        indicatorColor: PKColors.primarioSuave,
        iconTheme: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const IconThemeData(color: PKColors.primarioSuaveTexto, size: 24);
          }
          return const IconThemeData(color: PKColors.secundario, size: 24);
        }),
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(
              fontFamily: 'DMSans',
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: PKColors.primarioSuaveTexto,
            );
          }
          return const TextStyle(
            fontFamily: 'DMSans',
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: PKColors.secundario,
          );
        }),
        elevation: 0,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
      ),

      // ── Dialogs / Modales ──────────────────────────────────────────────────
      dialogTheme: DialogThemeData(
        backgroundColor: PKColors.tarjeta,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: PKRadii.tarjetaAll),
        titleTextStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 18,
          fontWeight: FontWeight.w700,
          color: PKColors.titulo,
        ),
        contentTextStyle: const TextStyle(
          fontFamily: 'DMSans',
          fontSize: 15,
          color: PKColors.titulo,
          height: 1.5,
        ),
      ),

      // ── Snackbars ─────────────────────────────────────────────────────────
      snackBarTheme: const SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(PKRadii.boton)),
        ),
        contentTextStyle: TextStyle(
          fontFamily: 'DMSans',
          fontSize: 14,
          fontWeight: FontWeight.w500,
          color: PKColors.titulo,
        ),
      ),

      // ── Switch ────────────────────────────────────────────────────────────
      switchTheme: SwitchThemeData(
        thumbColor: const WidgetStatePropertyAll(PKColors.sobrePrimario),
        trackColor: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) return PKColors.primario;
          return PKColors.borde;
        }),
      ),

      // ── Checkbox ─────────────────────────────────────────────────────────
      checkboxTheme: CheckboxThemeData(
        fillColor: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) return PKColors.primario;
          return Colors.transparent;
        }),
        checkColor: const WidgetStatePropertyAll(PKColors.sobrePrimario),
        side: const BorderSide(color: PKColors.borde, width: 1.5),
      ),

      // ── Divisores ─────────────────────────────────────────────────────────
      dividerTheme: const DividerThemeData(
        color: PKColors.borde,
        space: 1,
        thickness: 1,
      ),

      // ── Bottom sheet ──────────────────────────────────────────────────────
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: PKColors.tarjeta,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(PKRadii.modal)),
        ),
        modalBarrierColor: PKColors.modalOverlay,
      ),
    );
  }
}
