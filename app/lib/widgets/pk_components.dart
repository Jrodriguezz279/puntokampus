import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../constants/api_constants.dart';
import '../theme/theme.dart';

// ══════════════════════════════════════════════════════════════════════════════
// COMPONENTES REUTILIZABLES DE PUNTOKAMPUS
// Todos usan solo tokens de app/lib/theme/. Prohibido colores sueltos.
// ══════════════════════════════════════════════════════════════════════════════

// ─── Botón Primario ───────────────────────────────────────────────────────────
/// Botón principal: fondo primario, texto blanco, radio 12, alto mínimo 48.
class PKBotonPrimario extends StatelessWidget {
  final String texto;
  final VoidCallback? onPressed;
  final bool cargando;
  final IconData? icono;

  const PKBotonPrimario({
    super.key,
    required this.texto,
    required this.onPressed,
    this.cargando = false,
    this.icono,
  });

  @override
  Widget build(BuildContext context) {
    if (cargando) {
      return ElevatedButton(
        onPressed: null,
        child: const SizedBox(
          height: 20,
          width: 20,
          child: CircularProgressIndicator(
            color: PKColors.sobrePrimario,
            strokeWidth: 2.5,
          ),
        ),
      );
    }

    if (icono == null) {
      return ElevatedButton(
        onPressed: onPressed,
        child: Text(texto, maxLines: 1, overflow: TextOverflow.ellipsis),
      );
    }

    return ElevatedButton.icon(
      onPressed: onPressed,
      icon: Icon(icono, size: 20, color: PKColors.sobrePrimario),
      label: Text(texto, maxLines: 1, overflow: TextOverflow.ellipsis),
    );
  }
}

// ─── Botón Secundario ─────────────────────────────────────────────────────────
/// Botón secundario: fondo tarjeta, borde borde, texto titulo.
class PKBotonSecundario extends StatelessWidget {
  final String texto;
  final VoidCallback? onPressed;
  final IconData? icono;

  const PKBotonSecundario({
    super.key,
    required this.texto,
    required this.onPressed,
    this.icono,
  });

  @override
  Widget build(BuildContext context) {
    if (icono == null) {
      return OutlinedButton(
        onPressed: onPressed,
        child: Text(texto, maxLines: 1, overflow: TextOverflow.ellipsis),
      );
    }

    return OutlinedButton.icon(
      onPressed: onPressed,
      icon: Icon(icono, size: 20),
      label: Text(texto, maxLines: 1, overflow: TextOverflow.ellipsis),
    );
  }
}

// ─── Tarjeta base ─────────────────────────────────────────────────────────────
/// Tarjeta con fondo tarjeta, borde, radio 16 y sombra suave.
class PKTarjeta extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;

  const PKTarjeta({
    super.key,
    required this.child,
    this.padding,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final content = Container(
      padding: padding ?? const EdgeInsets.all(PKSpacing.base),
      decoration: BoxDecoration(
        color: PKColors.tarjeta,
        borderRadius: PKRadii.tarjetaAll,
        border: Border.all(color: PKColors.borde),
        boxShadow: PKShadows.tarjeta,
      ),
      child: child,
    );

    if (onTap != null) {
      return GestureDetector(onTap: onTap, child: content);
    }
    return content;
  }
}

// ─── Campo con etiqueta ───────────────────────────────────────────────────────
/// Campo de texto con etiqueta visible encima, estilo PuntoKampus.
class PKCampoEtiquetado extends StatelessWidget {
  final String etiqueta;
  final String? hint;
  final TextEditingController? controller;
  final TextInputType? keyboardType;
  final String? Function(String?)? validator;
  final bool obscureText;
  final int maxLines;
  final int? maxLength;
  final Widget? prefixIcon;
  final Widget? suffixIcon;
  final bool autofocus;
  final bool readOnly;
  final VoidCallback? onTap;
  final void Function(String)? onChanged;
  final TextStyle? style;
  final Iterable<String>? autofillHints;
  final TextInputAction? textInputAction;

  const PKCampoEtiquetado({
    super.key,
    required this.etiqueta,
    this.hint,
    this.controller,
    this.keyboardType,
    this.validator,
    this.obscureText = false,
    this.maxLines = 1,
    this.maxLength,
    this.prefixIcon,
    this.suffixIcon,
    this.autofocus = false,
    this.readOnly = false,
    this.onTap,
    this.onChanged,
    this.style,
    this.autofillHints,
    this.textInputAction,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(etiqueta, style: PKTextStyles.etiqueta),
        const SizedBox(height: PKSpacing.xs),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          obscureText: obscureText,
          maxLines: maxLines,
          maxLength: maxLength,
          autofocus: autofocus,
          readOnly: readOnly,
          onTap: onTap,
          onChanged: onChanged,
          style: style ?? PKTextStyles.cuerpo,
          validator: validator,
          autofillHints: autofillHints,
          textInputAction: textInputAction,
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon: prefixIcon,
            suffixIcon: suffixIcon,
            counterText: maxLength != null ? null : '',
          ),
        ),
      ],
    );
  }
}

// ─── Insignia "Matriculado" (plan gratis) ─────────────────────────────────────
class PKInsigniaMatriculado extends StatelessWidget {
  const PKInsigniaMatriculado({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: PKColors.tarjeta,
        borderRadius: PKRadii.chipAll,
        border: Border.all(color: PKColors.borde),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.school_outlined, size: 13, color: PKColors.secundario),
          const SizedBox(width: 4),
          Text('Matriculado', style: PKTextStyles.etiqueta.copyWith(color: PKColors.secundario)),
        ],
      ),
    );
  }
}

// ─── Insignia "Sacó 10" (Premium) ────────────────────────────────────────────
class PKInsigniaSaco10 extends StatelessWidget {
  const PKInsigniaSaco10({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: PKColors.primarioSuave,
        borderRadius: PKRadii.chipAll,
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.star_outline, size: 13, color: PKColors.primarioSuaveTexto),
          const SizedBox(width: 4),
          Text(
            'Sacó 10',
            style: PKTextStyles.etiqueta.copyWith(
              color: PKColors.primarioSuaveTexto,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Pastilla de estado de pedido ─────────────────────────────────────────────
enum EstadoPedido { pendiente, entregado, cancelado, vencido }

class PKPastillaEstado extends StatelessWidget {
  final EstadoPedido estado;
  final String? textoPersonalizado;

  const PKPastillaEstado({super.key, required this.estado, this.textoPersonalizado});

  @override
  Widget build(BuildContext context) {
    final config = _config();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: config.$1,
        borderRadius: PKRadii.chipAll,
      ),
      child: Text(
        textoPersonalizado ?? config.$3,
        style: PKTextStyles.etiqueta.copyWith(
          color: config.$2,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  (Color, Color, String) _config() => switch (estado) {
    EstadoPedido.pendiente  => (PKColors.avisoFondo,  PKColors.avisoTexto,  'Pendiente'),
    EstadoPedido.entregado  => (PKColors.okFondo,     PKColors.okTexto,     'Entregado'),
    EstadoPedido.cancelado  => (PKColors.neutroFondo, PKColors.secundario,  'Cancelado'),
    EstadoPedido.vencido    => (PKColors.errorFondo,  PKColors.error,       'Vencido'),
  };
}

/// Convierte string de BD a EstadoPedido
EstadoPedido estadoFromString(String? s) => switch (s?.toLowerCase()) {
  'entregado' || 'completado' => EstadoPedido.entregado,
  'cancelado'                  => EstadoPedido.cancelado,
  'vencido'                    => EstadoPedido.vencido,
  _                            => EstadoPedido.pendiente,
};

// ─── Estado vacío ─────────────────────────────────────────────────────────────
class PKEstadoVacio extends StatelessWidget {
  final IconData icono;
  final String titulo;
  final String? subtitulo;
  final String? botonTexto;
  final VoidCallback? onBoton;

  const PKEstadoVacio({
    super.key,
    required this.icono,
    required this.titulo,
    this.subtitulo,
    this.botonTexto,
    this.onBoton,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: PKSpacing.xl, vertical: PKSpacing.xxl),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: const BoxDecoration(
                color: PKColors.neutroFondo,
                shape: BoxShape.circle,
              ),
              child: Icon(icono, size: 36, color: PKColors.secundario),
            ),
            const SizedBox(height: PKSpacing.base),
            Text(titulo, textAlign: TextAlign.center, style: PKTextStyles.tituloSeccion),
            if (subtitulo != null) ...[
              const SizedBox(height: PKSpacing.sm),
              Text(subtitulo!, textAlign: TextAlign.center, style: PKTextStyles.cuerpoSecundario),
            ],
            if (botonTexto != null && onBoton != null) ...[
              const SizedBox(height: PKSpacing.lg),
              SizedBox(
                width: 200,
                child: PKBotonPrimario(texto: botonTexto!, onPressed: onBoton),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ─── Estado de error ──────────────────────────────────────────────────────────
class PKEstadoError extends StatelessWidget {
  final String mensaje;
  final VoidCallback onReintentar;

  const PKEstadoError({super.key, required this.mensaje, required this.onReintentar});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(PKSpacing.xl),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: const BoxDecoration(
                color: PKColors.errorFondo,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.warning_amber_outlined, size: 36, color: PKColors.error),
            ),
            const SizedBox(height: PKSpacing.base),
            Text(
              'Algo salió mal',
              style: PKTextStyles.tituloSeccion.copyWith(color: PKColors.error),
            ),
            const SizedBox(height: PKSpacing.sm),
            Text(mensaje, textAlign: TextAlign.center, style: PKTextStyles.cuerpoSecundario),
            const SizedBox(height: PKSpacing.lg),
            SizedBox(
              width: 200,
              child: PKBotonSecundario(texto: 'Reintentar', onPressed: onReintentar),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Esqueleto de carga ───────────────────────────────────────────────────────
class PKEsqueleto extends StatelessWidget {
  final double height;
  final double? width;
  final double borderRadius;

  const PKEsqueleto({
    super.key,
    this.height = 80,
    this.width,
    this.borderRadius = PKRadii.boton,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: height,
      width: width ?? double.infinity,
      margin: const EdgeInsets.only(bottom: PKSpacing.md),
      decoration: BoxDecoration(
        color: PKColors.neutroFondo,
        borderRadius: BorderRadius.circular(borderRadius),
      ),
    );
  }
}

// ─── Logo PuntoKampus ─────────────────────────────────────────────────────────
class PKLogo extends StatelessWidget {
  final double fontSize;
  const PKLogo({super.key, this.fontSize = 20});

  @override
  Widget build(BuildContext context) {
    return RichText(
      text: TextSpan(
        style: PKTextStyles.logoPunto.copyWith(fontSize: fontSize),
        children: [
          const TextSpan(text: 'Punto'),
          TextSpan(
            text: 'Kampus',
            style: PKTextStyles.logoKampus.copyWith(fontSize: fontSize),
          ),
        ],
      ),
    );
  }
}

// ─── Snackbar helpers ─────────────────────────────────────────────────────────
void pkSnackOk(BuildContext context, String mensaje) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text(mensaje, style: PKTextStyles.etiqueta.copyWith(color: PKColors.okTexto)),
      backgroundColor: PKColors.okFondo,
    ),
  );
}

void pkSnackError(BuildContext context, String mensaje) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text(mensaje, style: PKTextStyles.etiqueta.copyWith(color: PKColors.error)),
      backgroundColor: PKColors.errorFondo,
    ),
  );
}

void pkSnackInfo(BuildContext context, String mensaje) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text(mensaje, style: PKTextStyles.etiqueta.copyWith(color: PKColors.titulo)),
      backgroundColor: PKColors.tarjeta,
    ),
  );
}

// ─── Navegación adaptable ─────────────────────────────────────────────────────
/// Selector responsivo: NavigationBar en móvil, NavigationRail en tableta/escritorio.
class PKNavegacionAdaptable extends StatelessWidget {
  final int indiceActual;
  final ValueChanged<int> onSeleccion;
  final List<PKNavItem> items;
  final Widget body;

  const PKNavegacionAdaptable({
    super.key,
    required this.indiceActual,
    required this.onSeleccion,
    required this.items,
    required this.body,
  });

  @override
  Widget build(BuildContext context) {
    final ancho = MediaQuery.of(context).size.width;

    if (ancho >= PKBreakpoints.mobile) {
      // Tableta/escritorio: NavigationRail lateral
      return Scaffold(
        body: Row(
          children: [
            NavigationRail(
              selectedIndex: indiceActual,
              onDestinationSelected: onSeleccion,
              backgroundColor: PKColors.tarjeta,
              indicatorColor: PKColors.primarioSuave,
              selectedIconTheme: const IconThemeData(color: PKColors.primarioSuaveTexto),
              selectedLabelTextStyle: const TextStyle(
                fontFamily: 'DMSans',
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: PKColors.primarioSuaveTexto,
              ),
              unselectedIconTheme: const IconThemeData(color: PKColors.secundario),
              unselectedLabelTextStyle: const TextStyle(
                fontFamily: 'DMSans',
                fontSize: 12,
                color: PKColors.secundario,
              ),
              labelType: NavigationRailLabelType.all,
              destinations: items
                  .map((i) => NavigationRailDestination(
                        icon: Icon(i.icono),
                        selectedIcon: Icon(i.iconoActivo ?? i.icono),
                        label: Text(i.etiqueta),
                      ))
                  .toList(),
            ),
            const VerticalDivider(width: 1, color: PKColors.borde),
            Expanded(child: body),
          ],
        ),
      );
    }

    // Móvil: NavigationBar inferior
    return Scaffold(
      body: body,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          border: Border(top: BorderSide(color: PKColors.borde)),
        ),
        child: NavigationBar(
          selectedIndex: indiceActual,
          onDestinationSelected: onSeleccion,
          destinations: items
              .map((i) => NavigationDestination(
                    icon: Icon(i.icono),
                    selectedIcon: Icon(i.iconoActivo ?? i.icono),
                    label: i.etiqueta,
                  ))
              .toList(),
        ),
      ),
    );
  }
}

class PKNavItem {
  final IconData icono;
  final IconData? iconoActivo;
  final String etiqueta;
  const PKNavItem({required this.icono, this.iconoActivo, required this.etiqueta});
}

// ─── Selector de Imagen (Galería) ─────────────────────────────────────────────
/// Permite elegir fotos de la galería para productos, logos y comprobantes.
class PKSelectorImagen extends StatefulWidget {
  final String etiqueta;
  final String? urlActual;
  final Uint8List? bytesIniciales;
  final String? nombreArchivoInicial;
  final void Function(Uint8List bytes, String nombreArchivo) onImagenSeleccionada;
  final VoidCallback? onRemover;
  final double altura;
  final bool circular;

  const PKSelectorImagen({
    super.key,
    required this.etiqueta,
    this.urlActual,
    this.bytesIniciales,
    this.nombreArchivoInicial,
    required this.onImagenSeleccionada,
    this.onRemover,
    this.altura = 160,
    this.circular = false,
  });

  @override
  State<PKSelectorImagen> createState() => _PKSelectorImagenState();
}

class _PKSelectorImagenState extends State<PKSelectorImagen> {
  Uint8List? _bytes;
  String? _nombreArchivo;
  bool _seleccionando = false;

  @override
  void initState() {
    super.initState();
    _bytes = widget.bytesIniciales;
    _nombreArchivo = widget.nombreArchivoInicial;
  }

  Future<void> _elegirDeGaleria() async {
    setState(() => _seleccionando = true);
    try {
      final picker = ImagePicker();
      final picked = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1920,
        maxHeight: 1920,
        imageQuality: 85,
      );

      if (picked != null) {
        final bytes = await picked.readAsBytes();
        setState(() {
          _bytes = bytes;
          _nombreArchivo = picked.name;
        });
        widget.onImagenSeleccionada(bytes, picked.name);
      }
    } catch (e) {
      if (mounted) {
        pkSnackError(context, 'No se pudo abrir la galería. Revisa los permisos.');
      }
    } finally {
      if (mounted) setState(() => _seleccionando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tieneFoto = _bytes != null || (widget.urlActual != null && widget.urlActual!.isNotEmpty);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(widget.etiqueta, style: PKTextStyles.etiqueta),
        const SizedBox(height: PKSpacing.xs),
        InkWell(
          onTap: _seleccionando ? null : _elegirDeGaleria,
          borderRadius: widget.circular ? BorderRadius.circular(100) : PKRadii.tarjetaAll,
          child: Container(
            height: widget.altura,
            width: widget.circular ? widget.altura : double.infinity,
            decoration: BoxDecoration(
              color: PKColors.tarjeta,
              shape: widget.circular ? BoxShape.circle : BoxShape.rectangle,
              borderRadius: widget.circular ? null : PKRadii.tarjetaAll,
              border: Border.all(
                color: tieneFoto ? PKColors.primario : PKColors.borde,
                width: tieneFoto ? 1.5 : 1,
              ),
            ),
            child: ClipRRect(
              borderRadius: widget.circular ? BorderRadius.circular(100) : PKRadii.tarjetaAll,
              child: _seleccionando
                  ? const Center(child: CircularProgressIndicator(color: PKColors.primario))
                  : _bytes != null
                      ? Stack(
                          fit: StackFit.expand,
                          children: [
                            Image.memory(_bytes!, fit: BoxFit.cover),
                            Positioned(
                              bottom: PKSpacing.xs,
                              right: PKSpacing.xs,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.black.withOpacity(0.65),
                                  borderRadius: PKRadii.botonAll,
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.photo_library_outlined, size: 14, color: PKColors.sobrePrimario),
                                    const SizedBox(width: 4),
                                    Text('Cambiar', style: PKTextStyles.etiqueta.copyWith(color: PKColors.sobrePrimario, fontSize: 11)),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        )
                      : (widget.urlActual != null && widget.urlActual!.isNotEmpty)
                          ? Image.network(
                              ApiConstants.formatearUrlImagen(widget.urlActual) ?? widget.urlActual!,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => const Center(
                                child: Icon(Icons.broken_image_outlined, color: PKColors.secundario),
                              ),
                            )
                          : Center(
                              child: SingleChildScrollView(
                                physics: const NeverScrollableScrollPhysics(),
                                child: Padding(
                                  padding: const EdgeInsets.all(PKSpacing.xs),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        Icons.add_photo_alternate_outlined,
                                        size: widget.circular ? 28 : 38,
                                        color: PKColors.primario,
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        widget.circular ? 'Subir foto' : 'Elegir de la galería',
                                        textAlign: TextAlign.center,
                                        style: PKTextStyles.etiqueta.copyWith(
                                          color: PKColors.primario,
                                          fontWeight: FontWeight.w700,
                                          fontSize: widget.circular ? 11 : 13,
                                        ),
                                      ),
                                      if (!widget.circular) ...[
                                        const SizedBox(height: 2),
                                        Text(
                                          'PNG o JPG hasta 5MB',
                                          style: PKTextStyles.etiqueta.copyWith(fontSize: 11),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              ),
                            ),
            ),
          ),
        ),
      ],
    );
  }
}
