import 'package:flutter/material.dart';
import '../../theme/theme.dart';
import '../../models/tienda.dart';
import '../../services/tienda_service.dart';
import '../../widgets/pk_components.dart';

// ─── P19: Reportar Tienda ─────────────────────────────────────────────────────
// Permite a los estudiantes reportar una tienda por faltas a las normas del campus.
class ReportarTiendaScreen extends StatefulWidget {
  final Tienda tienda;
  const ReportarTiendaScreen({super.key, required this.tienda});

  @override
  State<ReportarTiendaScreen> createState() => _ReportarTiendaScreenState();
}

class _ReportarTiendaScreenState extends State<ReportarTiendaScreen> {
  String _motivoSeleccionado = 'prohibido';
  final _detalleController = TextEditingController();
  bool _enviando = false;

  final Map<String, String> _motivos = {
    'prohibido': 'Venta de productos no permitidos en el campus',
    'no_entrego': 'El vendedor no acudió ni entregó el pedido',
    'informacion_falsa': 'Información falsa sobre productos o precios',
    'trato_inadecuado': 'Trato irrespetuoso o inapropiado',
    'otro': 'Otro motivo (especificar en detalle)',
  };

  @override
  void dispose() {
    _detalleController.dispose();
    super.dispose();
  }

  Future<void> _enviarReporte() async {
    final detalle = _detalleController.text.trim();

    if (_motivoSeleccionado == 'otro' && detalle.length < 10) {
      pkSnackError(context, 'Si eliges "Otro motivo", escribe al menos 10 caracteres explicando la razón.');
      return;
    }

    setState(() => _enviando = true);
    try {
      await TiendaService.reportarTienda(
        tiendaId: widget.tienda.id,
        motivo: _motivoSeleccionado,
        detalle: detalle.isNotEmpty ? detalle : null,
      );

      if (!mounted) return;
      pkSnackOk(context, 'Gracias. El equipo lo revisará en 24 horas.');
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      pkSnackError(context, e.toString());
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Reportar Tienda')),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: PKBotonPrimario(
          texto: 'Enviar Reporte',
          icono: Icons.flag_outlined,
          cargando: _enviando,
          onPressed: _enviando ? null : _enviarReporte,
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Resumen de la tienda
            PKTarjeta(
              padding: const EdgeInsets.all(PKSpacing.base),
              child: Row(
                children: [
                  const Icon(Icons.storefront_outlined, color: PKColors.primario, size: 28),
                  const SizedBox(width: PKSpacing.base),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Tienda a reportar:', style: PKTextStyles.etiqueta),
                        Text(widget.tienda.nombre, style: PKTextStyles.tituloTarjeta),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Selector de Motivo
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Motivo del reporte *', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.sm),
                  Text(
                    'Selecciona la opción que mejor describa lo ocurrido.',
                    style: PKTextStyles.cuerpoSecundario,
                  ),
                  const SizedBox(height: PKSpacing.base),
                  ..._motivos.entries.map((entry) {
                    final isSelected = _motivoSeleccionado == entry.key;
                    return InkWell(
                      onTap: () => setState(() => _motivoSeleccionado = entry.key),
                      borderRadius: PKRadii.botonAll,
                      child: Container(
                        margin: const EdgeInsets.only(bottom: PKSpacing.sm),
                        padding: const EdgeInsets.all(PKSpacing.md),
                        decoration: BoxDecoration(
                          color: isSelected ? PKColors.primarioSuave : PKColors.tarjeta,
                          borderRadius: PKRadii.botonAll,
                          border: Border.all(
                            color: isSelected ? PKColors.primario : PKColors.borde,
                            width: isSelected ? 1.5 : 1,
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                              color: isSelected ? PKColors.primario : PKColors.borde,
                              size: 20,
                            ),
                            const SizedBox(width: PKSpacing.sm),
                            Expanded(
                              child: Text(
                                entry.value,
                                style: PKTextStyles.cuerpo.copyWith(
                                  fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
                                  color: isSelected ? PKColors.primarioSuaveTexto : PKColors.titulo,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  }),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Detalle adicional
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Detalles adicionales', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.xs),
                  Text(
                    _motivoSeleccionado == 'otro'
                        ? 'Obligatorio: describe lo sucedido (mínimo 10 caracteres).'
                        : 'Opcional: puedes brindar más información para ayudarnos a investigar.',
                    style: PKTextStyles.cuerpoSecundario,
                  ),
                  const SizedBox(height: PKSpacing.base),
                  PKCampoEtiquetado(
                    etiqueta: _motivoSeleccionado == 'otro' ? 'Explicación del reporte *' : 'Detalles (opcional)',
                    hint: 'Describe lo que sucedió con fechas o detalles...',
                    maxLines: 4,
                    controller: _detalleController,
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.xxl),
          ],
        ),
      ),
    );
  }
}
