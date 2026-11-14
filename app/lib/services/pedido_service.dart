import '../models/pedido.dart';
import 'api_service.dart';

class PedidoService {
  // P10: Crear pedido
  static Future<Map<String, dynamic>> crearPedido({
    required int tiendaId,
    required int puntoEntregaId,
    required String horaEntrega,
    required String formaPago,
    String? nota,
    required List<Map<String, dynamic>> items,
  }) async {
    final response = await ApiService.post('/pedidos', body: {
      'tienda_id': tiendaId,
      'punto_entrega_id': puntoEntregaId,
      'hora_entrega': horaEntrega,
      'forma_pago': formaPago,
      'nota': nota,
      'items': items,
    });

    return response as Map<String, dynamic>;
  }

  // P12: Mis pedidos como comprador
  static Future<List<Pedido>> getMisPedidos() async {
    final response = await ApiService.get('/mis-pedidos');
    final list = (response is List) ? response : (response['pedidos'] as List<dynamic>? ?? response['data'] as List<dynamic>? ?? []);
    return list.map((p) => Pedido.fromJson(p as Map<String, dynamic>)).toList();
  }

  // P12: Cancelar pedido de comprador
  static Future<void> cancelarPedidoComprador(int pedidoId) async {
    await ApiService.post('/pedidos/$pedidoId/cancelar');
  }

  // P17: Pedidos recibidos en mi tienda (vendedor)
  static Future<List<Pedido>> getPedidosTienda({String? estado}) async {
    final Map<String, String> query = {};
    if (estado != null && estado.isNotEmpty && estado != 'todos') {
      query['estado'] = estado;
    }

    final response = await ApiService.get('/mi-tienda/pedidos', queryParams: query);
    final list = (response is List) ? response : (response['pedidos'] as List<dynamic>? ?? response['data'] as List<dynamic>? ?? []);
    return list.map((p) => Pedido.fromJson(p as Map<String, dynamic>)).toList();
  }

  // P17: Marcar pedido como entregado (vendedor)
  static Future<void> marcarEntregado(int pedidoId) async {
    await ApiService.post('/mi-tienda/pedidos/$pedidoId/entregado');
  }

  // P17: Cancelar pedido (vendedor)
  static Future<void> cancelarPedidoVendedor(int pedidoId) async {
    await ApiService.post('/mi-tienda/pedidos/$pedidoId/cancelar');
  }
}
