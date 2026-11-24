import 'package:flutter/material.dart';
import '../../theme/theme.dart';
import '../../constants/app_strings.dart';
import '../../models/pedido.dart';
import '../../services/pedido_service.dart';
import '../../services/api_service.dart';
import '../../widgets/pk_components.dart';
import '../../widgets/calificar_bottom_sheet.dart';
// ══════════════════════════════════════════════════════════════════════════════
// MIS PEDIDOS — P12 Historial de Mis Pedidos (Comprador)
// Cumple con docs/DISENO-VISUAL.md
// ══════════════════════════════════════════════════════════════════════════════

class MisPedidosScreen extends StatefulWidget {
  const MisPedidosScreen({super.key});

  @override
  State<MisPedidosScreen> createState() => _MisPedidosScreenState();
}

class _MisPedidosScreenState extends State<MisPedidosScreen> with RouteAware {
  List<Pedido> _pedidos = [];
  bool _cargando = true;
  String? _error;
  // Para detectar regreso desde otra pantalla
  static final RouteObserver<ModalRoute<void>> _routeObserver = RouteObserver<ModalRoute<void>>();

  @override
  void initState() {
    super.initState();
    // Esperar a que el contexto esté listo para garantizar que el token esté disponible
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _cargar();
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Recargar al volver a esta pantalla (ej. tras calificar o cancelar)
    final route = ModalRoute.of(context);
    if (route is PageRoute) {
      _routeObserver.subscribe(this, route);
    }
  }

  @override
  void dispose() {
    _routeObserver.unsubscribe(this);
    super.dispose();
  }

  @override
  void didPopNext() {
    // Se regresó a esta pantalla desde otra
    _cargar();
  }

  Future<void> _cargar({int intentos = 0}) async {
    if (!mounted) return;
    setState(() {
      _cargando = true;
      _error = null;
    });

    try {
      final list = await PedidoService.getMisPedidos();
      if (!mounted) return;
      setState(() {
        _pedidos = list;
        _cargando = false;
      });
    } catch (e) {
      if (!mounted) return;
      // Reintento automático una vez (condición de carrera al iniciar sesión)
      if (intentos == 0) {
        await Future.delayed(const Duration(milliseconds: 800));
        return _cargar(intentos: 1);
      }
      setState(() {
        _error = e.toString();
        _cargando = false;
      });
    }
  }

  void _cancelarPedido(Pedido p) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('¿Cancelar pedido?'),
        content: Text('¿Deseas cancelar el pedido #${p.codigo}? El vendedor verá que lo cancelaste.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Volver')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: PKColors.error,
              foregroundColor: PKColors.sobrePrimario,
            ),
            child: const Text('Cancelar pedido'),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

    try {
      await PedidoService.cancelarPedidoComprador(p.codigo);
      _cargar(intentos: 0);
    } catch (e) {
      if (!mounted) return;
      pkSnackError(context, e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Mis Pedidos')),
      body: RefreshIndicator(
        color: PKColors.primario,
        onRefresh: () => _cargar(intentos: 0),
        child: _cargando
            ? const Padding(
                padding: EdgeInsets.all(PKSpacing.base),
                child: Column(
                  children: [
                    PKEsqueleto(height: 100),
                    PKEsqueleto(height: 100),
                  ],
                ),
              )
            : _error != null
                ? PKEstadoError(mensaje: _error!, onReintentar: _cargar)
                : _pedidos.isEmpty
                    ? const PKEstadoVacio(
                        icono: Icons.receipt_long_outlined,
                        titulo: 'Sin pedidos activos',
                        subtitulo: AppStrings.emptyMisPedidos,
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(PKSpacing.base),
                        itemCount: _pedidos.length,
                        itemBuilder: (context, index) {
                          final p = _pedidos[index];
                          return Container(
                            margin: const EdgeInsets.only(bottom: PKSpacing.md),
                            child: PKTarjeta(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        '#${p.codigo}',
                                        style: PKTextStyles.tituloTarjeta,
                                      ),
                                      PKPastillaEstado(estado: estadoFromString(p.estado)),
                                    ],
                                  ),
                                  if (p.pasoHora) ...[
                                    const SizedBox(height: PKSpacing.xs),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFFEF3C7),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: const Color(0xFFF59E0B)),
                                      ),
                                      child: const Text(
                                        'Pasó la hora de entrega',
                                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF92400E), fontFamily: 'DMSans'),
                                      ),
                                    ),
                                  ],
                                  if (p.esVencido) ...[
                                    const SizedBox(height: PKSpacing.xs),
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(PKSpacing.sm),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFFEE2E2),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: const Color(0xFFEF4444)),
                                      ),
                                      child: const Text(
                                        'Este pedido venció porque no se confirmó la entrega.',
                                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF991B1B), fontFamily: 'DMSans'),
                                      ),
                                    ),
                                  ],
                                  const SizedBox(height: PKSpacing.xs),
                                  Text(
                                    'Tienda: ${p.nombreTienda ?? "Tienda"}',
                                    style: PKTextStyles.cuerpo.copyWith(fontWeight: FontWeight.w600),
                                  ),
                                  if (p.creadoEn != null && p.creadoEn!.isNotEmpty) ...[
                                    const SizedBox(height: 2),
                                    Text('🗓️ Realizado el: ${p.fechaHoraCreacionFormateada}', style: PKTextStyles.etiqueta),
                                  ],
                                  if (p.horaEntrega != null && p.horaEntrega!.isNotEmpty) ...[
                                    const SizedBox(height: 2),
                                    Text('⏰ Entrega: ${p.fechaHoraEntregaFormateada}', style: PKTextStyles.etiqueta),
                                  ],
                                  if (p.puntoEntrega != null) ...[
                                    const SizedBox(height: 2),
                                    Text('📍 Entrega: ${p.puntoEntrega}', style: PKTextStyles.etiqueta),
                                  ],
                                  const SizedBox(height: 2),
                                  Text('💳 Pago: ${p.formaPago}', style: PKTextStyles.etiqueta),
                                  if (p.formaPago.toLowerCase() == 'deuna') ...[
                                    const SizedBox(height: PKSpacing.xs),
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(PKSpacing.sm),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF3F4F6),
                                        borderRadius: PKRadii.botonAll,
                                        border: Border.all(color: PKColors.borde),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          if (p.deunaQrUrl != null) ...[
                                            const Text(
                                              'Paga con Deuna escaneando este QR. PuntoKampus no recibe tu dinero.',
                                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: PKColors.titulo, fontFamily: 'DMSans'),
                                            ),
                                            const SizedBox(height: PKSpacing.sm),
                                            Center(
                                              child: ClipRRect(
                                                borderRadius: BorderRadius.circular(8),
                                                child: Image.network(
                                                  p.deunaQrUrl!,
                                                  height: 180,
                                                  width: 180,
                                                  fit: BoxFit.contain,
                                                  errorBuilder: (_, __, ___) => const Text(
                                                    'No se pudo cargar la imagen del QR.',
                                                    style: TextStyle(fontSize: 12, color: PKColors.error, fontFamily: 'DMSans'),
                                                  ),
                                                ),
                                              ),
                                            ),
                                          ] else ...[
                                            const Text(
                                              'Coordina el pago con el vendedor por WhatsApp.',
                                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: PKColors.secundario, fontFamily: 'DMSans'),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                  ],
                                  // R17: Mostrar código de entrega solo cuando está pendiente
                                  if (p.esPendiente && p.codigoEntrega != null)
                                    _buildCodigoEntrega(context, p),
                                  // Fase 3: Calificaciones para pedidos entregados
                                  if (p.esEntregado) ...[
                                    const SizedBox(height: PKSpacing.xs),
                                    if (p.calificacionDada != null)
                                      Text(
                                        '⭐ Calificaste con ${p.calificacionDada} estrellas',
                                        style: PKTextStyles.etiqueta.copyWith(color: const Color(0xFFB45309), fontWeight: FontWeight.w600),
                                      ),
                                    if (p.calificacionRecibida != null)
                                      Text(
                                        '⭐ Calificación recibida: ${p.calificacionRecibida} estrellas',
                                        style: PKTextStyles.etiqueta.copyWith(color: PKColors.primario, fontWeight: FontWeight.w600),
                                      ),
                                    if (p.puedeCalificar) ...[
                                      const SizedBox(height: PKSpacing.xs),
                                      OutlinedButton.icon(
                                        onPressed: () async {
                                          final calificado = await CalificarBottomSheet.mostrar(
                                            context,
                                            nombre: p.nombreTienda ?? 'la tienda',
                                            onEnviar: (estrellas) => PedidoService.calificarTienda(p.codigo, estrellas),
                                          );
                                          if (calificado == true) _cargar();
                                        },
                                        icon: const Icon(Icons.star_outline_rounded, size: 18),
                                        label: const Text('Calificar tienda'),
                                        style: OutlinedButton.styleFrom(
                                          foregroundColor: const Color(0xFFB45309),
                                          side: const BorderSide(color: Color(0xFFB45309)),
                                          minimumSize: const Size(120, 48),
                                        ),
                                      ),
                                    ],
                                  ],
                                  const SizedBox(height: PKSpacing.sm),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        'Total: \$${p.total.toStringAsFixed(2)}',
                                        style: PKTextStyles.precio,
                                      ),
                                      if (p.esPendiente)
                                        TextButton(
                                          onPressed: () => _cancelarPedido(p),
                                          style: TextButton.styleFrom(
                                            foregroundColor: PKColors.error,
                                          ),
                                          child: const Text('Cancelar pedido'),
                                        ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
      ),
    );
  }

  Widget _buildCodigoEntrega(BuildContext context, Pedido pedido) {
    final codigo = pedido.codigoEntrega!;
    final qrUrl = PedidoService.getQrUrl(pedido.codigo);

    return Container(
      margin: const EdgeInsets.only(top: PKSpacing.sm, bottom: PKSpacing.xs),
      padding: const EdgeInsets.all(PKSpacing.base),
      decoration: BoxDecoration(
        color: PKColors.primarioSuave,
        borderRadius: PKRadii.botonAll,
        border: Border.all(color: PKColors.primario, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.key_outlined, color: PKColors.primario, size: 18),
            const SizedBox(width: PKSpacing.xs),
            Text(
              'Código de entrega',
              style: PKTextStyles.etiqueta.copyWith(
                color: PKColors.primarioSuaveTexto,
                fontWeight: FontWeight.w700,
              ),
            ),
          ]),
          const SizedBox(height: PKSpacing.xs),
          Text(
            codigo,
            style: PKTextStyles.tituloPantalla.copyWith(
              letterSpacing: 8,
              fontSize: 32,
              color: PKColors.primario,
            ),
          ),
          const SizedBox(height: PKSpacing.sm),
          // Tramo 2: Botón ver QR
          OutlinedButton.icon(
            onPressed: () => _mostrarQr(context, codigo, qrUrl),
            icon: const Icon(Icons.qr_code_2, size: 18),
            label: const Text('Ver código QR'),
            style: OutlinedButton.styleFrom(
              foregroundColor: PKColors.primario,
              side: const BorderSide(color: PKColors.primario),
              textStyle: const TextStyle(fontFamily: 'DMSans', fontWeight: FontWeight.w600, fontSize: 13),
              padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: 6),
            ),
          ),
          const SizedBox(height: PKSpacing.xs),
          Text(
            'Muéstrale este código al vendedor cuando recibas tu pedido.',
            style: PKTextStyles.etiqueta.copyWith(color: PKColors.primarioSuaveTexto),
          ),
        ],
      ),
    );
  }

  void _mostrarQr(BuildContext context, String codigo, String qrUrl) {
    showModalBottomSheet(
      context: context,
      backgroundColor: PKColors.tarjeta,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => Padding(
        padding: const EdgeInsets.all(PKSpacing.xl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Tu código QR de entrega', style: PKTextStyles.tituloSeccion),
            const SizedBox(height: PKSpacing.base),
            Container(
              width: 220,
              height: 220,
              decoration: BoxDecoration(
                color: PKColors.fondo,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: PKColors.borde),
              ),
              clipBehavior: Clip.antiAlias,
              child: FutureBuilder<Map<String, String>>(
                future: ApiService.getAuthHeaders(),
                builder: (ctx, snap) {
                  if (!snap.hasData) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  return Image.network(
                    qrUrl,
                    fit: BoxFit.contain,
                    headers: snap.data!,
                    loadingBuilder: (_, child, loadingProgress) {
                      if (loadingProgress == null) return child;
                      return const Center(child: CircularProgressIndicator());
                    },
                    errorBuilder: (_, __, ___) => const Center(
                      child: Icon(Icons.qr_code_2, size: 80, color: PKColors.secundario),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: PKSpacing.base),
            Text(
              codigo,
              style: PKTextStyles.tituloPantalla.copyWith(
                letterSpacing: 8,
                fontSize: 36,
                color: PKColors.primario,
              ),
            ),
            const SizedBox(height: PKSpacing.sm),
            Text(
              'Muéstrale este código QR al vendedor para que lo escanee con la cámara de su celular, o dile el código de 4 dígitos.',
              textAlign: TextAlign.center,
              style: PKTextStyles.etiqueta,
            ),
            const SizedBox(height: PKSpacing.xl),
          ],
        ),
      ),
    );
  }
}
