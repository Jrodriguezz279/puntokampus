import 'dart:typed_data';
import 'package:flutter/material.dart';
import '../../theme/theme.dart';
import '../../services/tienda_service.dart';
import '../../widgets/pk_components.dart';

// ─── P18: Solicitud de Plan Premium (Sacó 10) ──────────────────────────────────
// Permite al vendedor solicitar el plan Premium adjuntando comprobante de pago.
class PremiumScreen extends StatefulWidget {
  const PremiumScreen({super.key});

  @override
  State<PremiumScreen> createState() => _PremiumScreenState();
}

class _PremiumScreenState extends State<PremiumScreen> {
  bool _cargando = true;
  bool _enviando = false;
  String? _error;
  Map<String, dynamic>? _datos;

  final _comprobanteController = TextEditingController();
  final _montoController = TextEditingController();
  Uint8List? _fotoBytes;
  String? _fotoNombre;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  @override
  void dispose() {
    _comprobanteController.dispose();
    _montoController.dispose();
    super.dispose();
  }

  Future<void> _cargar() async {
    setState(() {
      _cargando = true;
      _error = null;
    });

    try {
      final res = await TiendaService.getEstadoPremium();
      setState(() {
        _datos = res;
        _cargando = false;
        final datosPago = res['datos_pago'] as Map<String, dynamic>?;
        if (datosPago != null && datosPago['precio_premium'] != null) {
          _montoController.text = (datosPago['precio_premium'] as num).toStringAsFixed(2);
        }
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _cargando = false;
      });
    }
  }

  Future<void> _enviar() async {
    final numComprobante = _comprobanteController.text.trim();
    final monto = double.tryParse(_montoController.text.trim()) ?? 0.0;

    if (numComprobante.length < 4) {
      pkSnackError(context, 'El número de comprobante debe tener al menos 4 caracteres.');
      return;
    }
    if (monto <= 0) {
      pkSnackError(context, 'Ingresa un monto válido.');
      return;
    }
    if (_fotoBytes == null || _fotoNombre == null) {
      pkSnackError(context, 'Debes adjuntar la foto del comprobante de pago.');
      return;
    }

    setState(() => _enviando = true);
    try {
      await TiendaService.solicitarPremium(
        numeroComprobante: numComprobante,
        monto: monto,
        bytes: _fotoBytes!,
        fileName: _fotoNombre!,
      );
      if (!mounted) return;
      pkSnackOk(context, '¡Solicitud enviada! El equipo la revisará pronto.');
      _comprobanteController.clear();
      _fotoBytes = null;
      _fotoNombre = null;
      _cargar();
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
      appBar: AppBar(title: const Text('Plan Premium (Sacó 10)')),
      body: _cargando
          ? const Center(child: CircularProgressIndicator(color: PKColors.primario))
          : _error != null
              ? PKEstadoError(mensaje: _error!, onReintentar: _cargar)
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(PKSpacing.base),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Banner informativo
                      PKTarjeta(
                        padding: const EdgeInsets.all(PKSpacing.base),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(PKSpacing.sm),
                              decoration: const BoxDecoration(
                                color: PKColors.primarioSuave,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.star_rounded, color: PKColors.primario, size: 28),
                            ),
                            const SizedBox(width: PKSpacing.base),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Beneficios de Sacó 10', style: PKTextStyles.tituloTarjeta),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Insignia dorada en tu tienda, portada personalizada y máxima visibilidad en la vitrina.',
                                    style: PKTextStyles.etiqueta,
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: PKSpacing.base),

                      // Estado actual
                      if (_datos != null && _datos!['ultima_solicitud'] != null) ...[
                        _buildEstadoUltimaSolicitud(_datos!['ultima_solicitud'] as Map<String, dynamic>),
                        const SizedBox(height: PKSpacing.base),
                      ],

                      // Datos bancarios
                      if (_datos != null && _datos!['datos_pago'] != null)
                        _buildDatosPago(_datos!['datos_pago'] as Map<String, dynamic>),

                      const SizedBox(height: PKSpacing.base),

                      // Formulario de Solicitud
                      PKTarjeta(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Registrar pago', style: PKTextStyles.tituloTarjeta),
                            const SizedBox(height: PKSpacing.base),
                            PKCampoEtiquetado(
                              etiqueta: 'Número de comprobante o referencia *',
                              hint: 'Ej. 00482910',
                              controller: _comprobanteController,
                            ),
                            const SizedBox(height: PKSpacing.base),
                            PKCampoEtiquetado(
                              etiqueta: 'Monto transferido (\$) *',
                              hint: '5.00',
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                              controller: _montoController,
                            ),
                            const SizedBox(height: PKSpacing.base),
                            PKSelectorImagen(
                              etiqueta: 'Foto del comprobante de pago *',
                              bytesIniciales: _fotoBytes,
                              nombreArchivoInicial: _fotoNombre,
                              altura: 150,
                              onImagenSeleccionada: (bytes, nombre) {
                                setState(() {
                                  _fotoBytes = bytes;
                                  _fotoNombre = nombre;
                                });
                              },
                            ),
                            const SizedBox(height: PKSpacing.lg),
                            PKBotonPrimario(
                              texto: 'Enviar Comprobante',
                              icono: Icons.upload_file_outlined,
                              cargando: _enviando,
                              onPressed: _enviando ? null : _enviar,
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

  Widget _buildEstadoUltimaSolicitud(Map<String, dynamic> sol) {
    final estado = sol['estado'] as String? ?? 'pendiente';
    final motivo = sol['motivo_rechazo'] as String?;

    Color colorBorde = PKColors.primario;
    Color colorFondo = PKColors.primarioSuave;
    String estadoTexto = 'En revisión';
    IconData icono = Icons.hourglass_top_outlined;

    if (estado == 'aprobada') {
      colorBorde = PKColors.okTexto;
      colorFondo = PKColors.okFondo;
      estadoTexto = '¡Solicitud Aprobada!';
      icono = Icons.check_circle_outline;
    } else if (estado == 'rechazada') {
      colorBorde = PKColors.error;
      colorFondo = PKColors.errorFondo;
      estadoTexto = 'Solicitud Rechazada';
      icono = Icons.cancel_outlined;
    }

    return Container(
      padding: const EdgeInsets.all(PKSpacing.base),
      decoration: BoxDecoration(
        color: colorFondo,
        borderRadius: PKRadii.tarjetaAll,
        border: Border.all(color: colorBorde),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icono, color: colorBorde, size: 20),
              const SizedBox(width: PKSpacing.xs),
              Text(
                'Última solicitud: $estadoTexto',
                style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 14, color: colorBorde),
              ),
            ],
          ),
          if (motivo != null && motivo.isNotEmpty) ...[
            const SizedBox(height: PKSpacing.xs),
            Text('Motivo: $motivo', style: PKTextStyles.etiqueta.copyWith(color: PKColors.error)),
          ],
        ],
      ),
    );
  }

  Widget _buildDatosPago(Map<String, dynamic> p) {
    return PKTarjeta(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Datos para realizar el pago', style: PKTextStyles.tituloTarjeta),
          const SizedBox(height: PKSpacing.sm),
          _filaDato('Banco:', p['banco']?.toString() ?? 'Banco Pichincha'),
          _filaDato('Tipo de cuenta:', p['tipo_cuenta']?.toString() ?? 'Ahorros'),
          _filaDato('Número de cuenta:', p['numero_cuenta']?.toString() ?? '2200112233'),
          _filaDato('Titular:', p['titular_cuenta']?.toString() ?? 'PuntoKampus UTB'),
          if (p['usuario_deuna'] != null && p['usuario_deuna'].toString().isNotEmpty)
            _filaDato('Deuna:', p['usuario_deuna'].toString()),
          const Divider(height: PKSpacing.lg, color: PKColors.borde),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Precio Plan Premium:', style: PKTextStyles.cuerpo),
              Text('\$${(p['precio_premium'] as num?)?.toStringAsFixed(2) ?? "5.00"}', style: PKTextStyles.precioGrande),
            ],
          ),
        ],
      ),
    );
  }

  Widget _filaDato(String clave, String valor) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(clave, style: PKTextStyles.etiqueta),
          Text(valor, style: PKTextStyles.cuerpo.copyWith(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
