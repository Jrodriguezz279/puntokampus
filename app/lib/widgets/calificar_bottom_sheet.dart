import 'package:flutter/material.dart';
import '../theme/theme.dart';
import 'pk_components.dart';

class CalificarBottomSheet extends StatefulWidget {
  final String nombre;
  final Future<void> Function(int estrellas) onEnviar;

  const CalificarBottomSheet({
    super.key,
    required this.nombre,
    required this.onEnviar,
  });

  static Future<bool?> mostrar(
    BuildContext context, {
    required String nombre,
    required Future<void> Function(int estrellas) onEnviar,
  }) {
    return showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: PKColors.tarjeta,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => CalificarBottomSheet(
        nombre: nombre,
        onEnviar: onEnviar,
      ),
    );
  }

  @override
  State<CalificarBottomSheet> createState() => _CalificarBottomSheetState();
}

class _CalificarBottomSheetState extends State<CalificarBottomSheet> {
  int _estrellas = 5;
  bool _enviando = false;
  String? _error;

  void _enviar() async {
    setState(() {
      _enviando = true;
      _error = null;
    });

    try {
      await widget.onEnviar(_estrellas);
      if (!mounted) return;
      Navigator.pop(context, true);
      pkSnackOk(
        context,
        'Gracias. Tu calificación se verá cuando la otra parte también califique o en 3 días.',
      );
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _enviando = false;
        final msg = e.toString().toLowerCase();
        if (msg.contains('ya no se puede calificar')) {
          _error = 'Este pedido ya no se puede calificar.';
        } else {
          _error = e.toString().replaceFirst('Exception: ', '');
        }
      });
      pkSnackError(context, _error!);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: PKSpacing.base,
        right: PKSpacing.base,
        top: PKSpacing.lg,
        bottom: MediaQuery.of(context).viewInsets.bottom + PKSpacing.xl,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: PKColors.borde,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: PKSpacing.base),
          Text(
            '¿Cómo te fue con ${widget.nombre}?',
            style: PKTextStyles.tituloSeccion,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: PKSpacing.sm),
          const Text(
            'Tu opinión ayuda a mantener la confianza en la vitrina universitaria.',
            style: PKTextStyles.etiqueta,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: PKSpacing.lg),

          // 5 Estrellas interactivas
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (index) {
              final valor = index + 1;
              final seleccionada = valor <= _estrellas;
              return Semantics(
                label: '$valor ${valor == 1 ? "estrella" : "estrellas"}',
                button: true,
                selected: seleccionada,
                child: SizedBox(
                  width: 48,
                  height: 48,
                  child: IconButton(
                    padding: EdgeInsets.zero,
                    iconSize: 36,
                    icon: Icon(
                      seleccionada ? Icons.star_rounded : Icons.star_outline_rounded,
                      color: seleccionada ? const Color(0xFFF59E0B) : PKColors.borde,
                    ),
                    onPressed: _enviando
                        ? null
                        : () {
                            setState(() {
                              _estrellas = valor;
                            });
                          },
                  ),
                ),
              );
            }),
          ),
          const SizedBox(height: PKSpacing.sm),
          Text(
            '$_estrellas de 5 estrellas',
            style: PKTextStyles.cuerpo.copyWith(
              fontWeight: FontWeight.w600,
              color: PKColors.primario,
            ),
          ),
          const SizedBox(height: PKSpacing.base),

          // Aclaración
          Container(
            padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
            decoration: BoxDecoration(
              color: PKColors.fondo,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: PKColors.borde),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline, size: 18, color: PKColors.secundario),
                const SizedBox(width: PKSpacing.xs),
                Expanded(
                  child: Text(
                    'Se verá cuando califiquen los dos o en 3 días.',
                    style: PKTextStyles.etiqueta.copyWith(
                      color: PKColors.secundario,
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: PKSpacing.lg),

          // Botón Enviar calificación
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _enviando ? null : _enviar,
              style: ElevatedButton.styleFrom(
                backgroundColor: PKColors.primario,
                foregroundColor: PKColors.sobrePrimario,
                shape: RoundedRectangleBorder(
                  borderRadius: PKRadii.botonAll,
                ),
              ),
              child: _enviando
                  ? const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color: Colors.white,
                      ),
                    )
                  : const Text(
                      'Enviar calificación',
                      style: TextStyle(
                        fontFamily: 'DMSans',
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }
}
