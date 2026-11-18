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
    bool? recibiendoPedidos,
    List<int>? diasAtencion,
    String? horaApertura,
    String? horaCierre,
    bool esCreacion = true,
  }) async {
    final Map<String, dynamic> body = {
      'nombre': nombre,
      'descripcion': descripcion,
      'categoria_1_id': categoria1Id,
      'categoria_2_id': categoria2Id,
      'punto_entrega_id': puntoEntregaId,
      'horario': horario,
      'whatsapp': whatsapp,
    };
    if (recibiendoPedidos != null) body['recibiendo_pedidos'] = recibiendoPedidos;
    if (diasAtencion != null) body['dias_atencion'] = diasAtencion;
    if (horaApertura != null) body['hora_apertura'] = horaApertura;
    if (horaCierre != null) body['hora_cierre'] = horaCierre;

    final response = esCreacion
        ? await ApiService.post('/mi-tienda', body: body)
        : await ApiService.put('/mi-tienda', body: body);

    final data = (response is Map<String, dynamic> && response.containsKey('tienda')) ? response['tienda'] : response;
    return Tienda.fromJson(data as Map<String, dynamic>);
  }

  // Tramo 2: Toggle recibir pedidos
  static Future<bool> toggleRecibiendoPedidos() async {
    final response = await ApiService.post('/mi-tienda/toggle-pedidos');
    return response['recibiendo_pedidos'] == true;
  }

  // P16: Crear producto
  static Future<Producto> crearProducto({
    required String nombre,
    required String descripcion,
    required double precio,
    double? precioDescuento,
    bool? descuentoActivo,
    bool disponible = true,
  }) async {
    final Map<String, dynamic> body = {
      'nombre': nombre,
      'descripcion': descripcion,
      'precio': precio,
      'disponible': disponible,
    };
    if (precioDescuento != null) body['precio_descuento'] = precioDescuento;
    if (descuentoActivo != null) body['descuento_activo'] = descuentoActivo;

    final response = await ApiService.post('/mi-tienda/productos', body: body);

    final data = (response is Map<String, dynamic> && response.containsKey('producto')) ? response['producto'] : response;
    return Producto.fromJson(data as Map<String, dynamic>);
  }

  // P16: Actualizar producto
  static Future<Producto> actualizarProducto(int productoId, {
    String? nombre,
    String? descripcion,
    double? precio,
    double? precioDescuento,
    bool? descuentoActivo,
    bool? disponible,
  }) async {
    final Map<String, dynamic> body = {};
    if (nombre != null) body['nombre'] = nombre;
    if (descripcion != null) body['descripcion'] = descripcion;
    if (precio != null) body['precio'] = precio;
    if (precioDescuento != null) body['precio_descuento'] = precioDescuento;
    if (descuentoActivo != null) body['descuento_activo'] = descuentoActivo;
    if (disponible != null) body['disponible'] = disponible;

    final response = await ApiService.put('/mi-tienda/productos/$productoId', body: body);
    final data = (response is Map<String, dynamic> && response.containsKey('producto')) ? response['producto'] : response;
    return Producto.fromJson(data as Map<String, dynamic>);
  }
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

  // P14: Subir logo de la tienda
  static Future<String> subirLogo({
    required List<int> bytes,
    required String fileName,
  }) async {
    final response = await ApiService.postMultipart(
      '/mi-tienda/logo',
      fileField: 'foto',
      fileBytes: bytes,
      fileName: fileName,
    );
    return response['logo_url'] as String? ?? '';
  }

  // P16: Subir foto a producto
  static Future<void> subirFotoProducto(int productoId, {
    required List<int> bytes,
    required String fileName,
    bool reemplazar = false,
  }) async {
    final Map<String, String> fields = {};
    if (reemplazar) {
      fields['reemplazar'] = '1';
    }
    await ApiService.postMultipart(
      '/mi-tienda/productos/$productoId/fotos',
      fields: fields.isNotEmpty ? fields : null,
      fileField: 'foto',
      fileBytes: bytes,
      fileName: fileName,
    );
  }

  // P18: Obtener información y estado Premium
  static Future<Map<String, dynamic>> getEstadoPremium() async {
    final response = await ApiService.get('/mi-tienda/premium');
    return response as Map<String, dynamic>;
  }

  // P18: Enviar solicitud Premium (Sacó 10) con foto del comprobante
  static Future<void> solicitarPremium({
    required String numeroComprobante,
    required double monto,
    required List<int> bytes,
    required String fileName,
  }) async {
    await ApiService.postMultipart(
      '/mi-tienda/premium',
      fields: {
        'numero_comprobante': numeroComprobante,
        'monto': monto.toStringAsFixed(2),
      },
      fileField: 'foto_comprobante',
      fileBytes: bytes,
      fileName: fileName,
    );
  }

  // P19: Reportar una tienda
  static Future<void> reportarTienda({
    required int tiendaId,
    required String motivo,
    String? detalle,
  }) async {
    await ApiService.post('/tiendas/$tiendaId/reportes', body: {
      'motivo': motivo,
      'detalle': detalle,
    });
  }
}
