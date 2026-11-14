import 'package:flutter/material.dart';
import '../../constants/app_colors.dart';
import '../../constants/app_strings.dart';
import '../../models/pedido.dart';
import '../../services/pedido_service.dart';
import '../../widgets/common_widgets.dart';

// P12: Historial de Mis Pedidos (Comprador)
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
        content: Text('¿Deseas cancelar el pedido ${p.codigo}? El vendedor verá que lo cancelaste.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Volver')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white),
            child: const Text('Cancelar pedido'),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

    try {
      await PedidoService.cancelarPedidoComprador(p.id);
      _cargar();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Mis Pedidos')),
      body: RefreshIndicator(
        onRefresh: () async => _cargar(),
        child: _cargando
            ? const Padding(
                padding: EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    LoadingSkeleton(height: 100),
                    LoadingSkeleton(height: 100),
                  ],
                ),
              )
            : _error != null
                ? ErrorViewWidget(message: _error!, onRetry: _cargar)
                : _pedidos.isEmpty
                    ? const EmptyStateWidget(
                        icon: '📦',
                        message: AppStrings.emptyMisPedidos,
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _pedidos.length,
                        itemBuilder: (context, index) {
                          final p = _pedidos[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: const BorderSide(color: AppColors.border),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(p.codigo, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                      _buildBadgeEstado(p.estado),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text('Tienda: ${p.nombreTienda ?? "Tienda"}',
                                      style: const TextStyle(color: AppColors.text, fontWeight: FontWeight.w600)),
                                  if (p.puntoEntrega != null)
                                    Text('Entrega: ${p.puntoEntrega}', style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
                                  const SizedBox(height: 8),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        'Total: \$${p.total.toStringAsFixed(2)}',
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: AppColors.primary),
                                      ),
                                      if (p.esPendiente)
                                        TextButton(
                                          onPressed: () => _cancelarPedido(p),
                                          style: TextButton.styleFrom(foregroundColor: AppColors.error),
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

  Widget _buildBadgeEstado(String estado) {
    Color bg = Colors.grey.shade100;
    Color text = Colors.grey.shade800;

    switch (estado.toLowerCase()) {
      case 'pendiente':
        bg = AppColors.accentLight;
        text = AppColors.accent;
        break;
      case 'entregado':
        bg = AppColors.successLight;
        text = AppColors.success;
        break;
      case 'cancelado':
      case 'vencido':
        bg = AppColors.errorLight;
        text = AppColors.error;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(6)),
      child: Text(
        estado.toUpperCase(),
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: text),
      ),
    );
  }
}
