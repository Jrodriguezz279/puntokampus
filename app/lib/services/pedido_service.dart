import '../models/pedido.dart';
import '../constants/api_constants.dart';
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

  // P12: Cancelar pedido de comprador (usa codigo del pedido, no id)
  static Future<void> cancelarPedidoComprador(String codigo) async {
    await ApiService.delete('/mis-pedidos/$codigo');
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

  // P17: Confirmar entrega con código de 4 dígitos (R17)
  static Future<void> confirmarEntrega(String codigoPedido, String codigoEntrega) async {
    await ApiService.put('/mi-tienda/pedidos/$codigoPedido/confirmar-entrega', body: {
      'codigo_entrega': codigoEntrega,
    });
  }

  // P17: Cancelar pedido (vendedor)
  static Future<void> cancelarPedidoVendedor(String codigo) async {
    await ApiService.put('/mi-tienda/pedidos/$codigo/cancelar');
  }

  // Tramo 2: Obtener URL del QR de un pedido (para comprador)
  static String getQrUrl(String codigoPedido) {
    return '${ApiConstants.baseUrl}/pedidos/$codigoPedido/qr?formato=png';
  }
}
