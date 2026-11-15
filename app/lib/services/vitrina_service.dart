import '../models/categoria.dart';
import '../models/tienda.dart';
import '../models/producto.dart';
import 'api_service.dart';

class VitrinaService {
  static Future<List<Categoria>> getCategorias() async {
    final response = await ApiService.get('/categorias', requiresAuth: false);
    final list = (response is List) ? response : (response['categorias'] as List<dynamic>? ?? []);
    return list.map((c) => Categoria.fromJson(c as Map<String, dynamic>)).toList();
  }

  static Future<List<PuntoEntrega>> getPuntosEntrega() async {
    final response = await ApiService.get('/puntos-entrega', requiresAuth: false);
    final list = (response is List)
        ? response
        : (response['puntos_entrega'] as List<dynamic>? ??
            response['puntos'] as List<dynamic>? ??
            response['data'] as List<dynamic>? ??
            []);
    return list.map((p) => PuntoEntrega.fromJson(p as Map<String, dynamic>)).toList();
  }

  static Future<List<Tienda>> getTiendas({String? busqueda, int? categoriaId, int pagina = 1}) async {
    final Map<String, String> query = {'page': pagina.toString()};
    if (busqueda != null && busqueda.trim().isNotEmpty) {
      query['q'] = busqueda.trim();
    }
    if (categoriaId != null) {
      query['categoria'] = categoriaId.toString();
    }

    final response = await ApiService.get('/tiendas', queryParams: query, requiresAuth: false);
    final list = (response is List) ? response : (response['data'] as List<dynamic>? ?? response['tiendas'] as List<dynamic>? ?? []);
    return list.map((t) => Tienda.fromJson(t as Map<String, dynamic>)).toList();
  }

  static Future<Tienda> getTiendaDetalle(String slug) async {
    final response = await ApiService.get('/tiendas/$slug', requiresAuth: false);
    final data = (response is Map<String, dynamic> && response.containsKey('tienda')) ? response['tienda'] : response;
    return Tienda.fromJson(data as Map<String, dynamic>);
  }

  static Future<List<Producto>> getProductos({String? busqueda, int pagina = 1}) async {
    final Map<String, String> query = {'page': pagina.toString()};
    if (busqueda != null && busqueda.trim().isNotEmpty) {
      query['q'] = busqueda.trim();
    }

    final response = await ApiService.get('/productos', queryParams: query, requiresAuth: false);
    final list = (response is List) ? response : (response['data'] as List<dynamic>? ?? response['productos'] as List<dynamic>? ?? []);
    return list.map((p) => Producto.fromJson(p as Map<String, dynamic>)).toList();
  }
}
