import 'package:flutter/material.dart';
import '../../theme/theme.dart';
import '../../constants/app_strings.dart';
import '../../models/pedido.dart';
import '../../services/pedido_service.dart';
import '../../widgets/pk_components.dart';

// ══════════════════════════════════════════════════════════════════════════════
// MIS PEDIDOS — P12 Historial de Mis Pedidos (Comprador)
// Cumple con docs/DISENO-VISUAL.md
// ══════════════════════════════════════════════════════════════════════════════

class MisPedidosScreen extends StatefulWidget {
  const MisPedidosScreen({super.key});

  @override
  State<MisPedidosScreen> createState() => _MisPedidosScreenState();
}

class _MisPedidosScreenState extends State<MisPedidosScreen> {
  List<Pedido> _pedidos = [];
  bool _cargando = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  void _cargar() async {
    setState(() {
      _cargando = true;
      _error = null;
    });

    try {
      final list = await PedidoService.getMisPedidos();
      setState(() {
        _pedidos = list;
        _cargando = false;
      });
    } catch (e) {
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
      _cargar();
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
        onRefresh: () async => _cargar(),
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
                                  const SizedBox(height: PKSpacing.xs),
                                  Text(
                                    'Tienda: ${p.nombreTienda ?? "Tienda"}',
                                    style: PKTextStyles.cuerpo.copyWith(fontWeight: FontWeight.w600),
                                  ),
                                  if (p.puntoEntrega != null) ...[
                                    const SizedBox(height: 2),
                                    Text('📍 Entrega: ${p.puntoEntrega}', style: PKTextStyles.etiqueta),
                                  ],
                                  // R17: Mostrar código de entrega solo cuando está pendiente
                                  if (p.esPendiente && p.codigoEntrega != null)
                                    _buildCodigoEntrega(p.codigoEntrega!),
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

  Widget _buildCodigoEntrega(String codigo) {
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
          const SizedBox(height: PKSpacing.xs),
          Text(
            'Muéstrale este código al vendedor cuando recibas tu pedido.',
            style: PKTextStyles.etiqueta.copyWith(color: PKColors.primarioSuaveTexto),
          ),
        ],
      ),
    );
  }
}
