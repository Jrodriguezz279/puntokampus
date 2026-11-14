import '../models/tienda.dart';
import '../models/producto.dart';
import 'api_service.dart';

class TiendaService {
  // P15: Obtener datos de mi tienda
  static Future<Tienda?> getMiTienda() async {
    try {
      final response = await ApiService.get('/mi-tienda');
      if (response == null) return null;
      final data = (response is Map<String, dynamic> && response.containsKey('tienda')) ? response['tienda'] : response;
      if (data == null) return null;
      return Tienda.fromJson(data as Map<String, dynamic>);
    } catch (e) {
      if (e is ApiException && e.statusCode == 404) {
        return null;
      }
      rethrow;
    }
  }

  // P14: Crear o actualizar tienda
  static Future<Tienda> guardarTienda({
    required String nombre,
    required String descripcion,
    required int categoria1Id,
    int? categoria2Id,
    required int puntoEntregaId,
    required String horario,
    String? whatsapp,
    bool esCreacion = true,
  }) async {
    final body = {
      'nombre': nombre,
      'descripcion': descripcion,
      'categoria_1_id': categoria1Id,
      'categoria_2_id': categoria2Id,
      'punto_entrega_id': puntoEntregaId,
      'horario': horario,
      'whatsapp': whatsapp,
    };

    final response = esCreacion
        ? await ApiService.post('/mi-tienda', body: body)
        : await ApiService.put('/mi-tienda', body: body);

    final data = (response is Map<String, dynamic> && response.containsKey('tienda')) ? response['tienda'] : response;
    return Tienda.fromJson(data as Map<String, dynamic>);
  }

  // P16: Crear producto
  static Future<Producto> crearProducto({
    required String nombre,
    required String descripcion,
    required double precio,
    bool disponible = true,
  }) async {
    final response = await ApiService.post('/mi-tienda/productos', body: {
      'nombre': nombre,
      'descripcion': descripcion,
      'precio': precio,
      'disponible': disponible,
    });

    final data = (response is Map<String, dynamic> && response.containsKey('producto')) ? response['producto'] : response;
    return Producto.fromJson(data as Map<String, dynamic>);
  }

  // P16: Actualizar producto
  static Future<Producto> actualizarProducto(int productoId, {
    String? nombre,
    String? descripcion,
    double? precio,
    bool? disponible,
  }) async {
    final Map<String, dynamic> body = {};
    if (nombre != null) body['nombre'] = nombre;
    if (descripcion != null) body['descripcion'] = descripcion;
    if (precio != null) body['precio'] = precio;
    if (disponible != null) body['disponible'] = disponible;

    final response = await ApiService.put('/mi-tienda/productos/$productoId', body: body);
    final data = (response is Map<String, dynamic> && response.containsKey('producto')) ? response['producto'] : response;
    return Producto.fromJson(data as Map<String, dynamic>);
  }

  // P16: Eliminar producto
  static Future<void> eliminarProducto(int productoId) async {
    await ApiService.delete('/mi-tienda/productos/$productoId');
  }

  // P18: Enviar solicitud Premium (Sacó 10)
  static Future<void> solicitarPremium({
    required String comprobanteUrl,
    required String referencia,
    required double monto,
  }) async {
    await ApiService.post('/premium/solicitud', body: {
      'comprobante_url': comprobanteUrl,
      'referencia': referencia,
      'monto': monto,
    });
  }

  // P19: Reportar una tienda
  static Future<void> reportarTienda({
    required int tiendaId,
    required String motivo,
    required String detalle,
  }) async {
    await ApiService.post('/reportes', body: {
      'tienda_id': tiendaId,
      'motivo': motivo,
      'detalle': detalle,
    });
  }
}
