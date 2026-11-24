import '../constants/api_constants.dart';

class ProductoFoto {
  final int id;
  final String url;
  final int orden;

  ProductoFoto({
    required this.id,
    required this.url,
    this.orden = 0,
  });

  factory ProductoFoto.fromJson(Map<String, dynamic> json) {
    final rawUrl = json['url']?.toString() ?? json['foto_url']?.toString() ?? '';
    return ProductoFoto(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      url: ApiConstants.formatearUrlImagen(rawUrl) ?? rawUrl,
      orden: json['orden'] is int ? json['orden'] as int : int.tryParse(json['orden']?.toString() ?? '0') ?? 0,
    );
  }
}

class Producto {
  final int id;
  final int tiendaId;
  final String nombre;
  final String? descripcion;
  final double precio;
  final double? precioDescuento;
  final bool descuentoActivo;
  final bool disponible;
  final List<ProductoFoto> fotos;

  Producto({
    required this.id,
    required this.tiendaId,
    required this.nombre,
    this.descripcion,
    required this.precio,
    this.precioDescuento,
    this.descuentoActivo = false,
    this.disponible = true,
    this.fotos = const [],
  });

  // --- Tramo 2: Descuentos ---
  bool get tieneDescuento =>
      (descuentoActivo || (precioDescuento != null && precioDescuento! > 0 && precioDescuento! < precio)) &&
      precioDescuento != null &&
      precioDescuento! > 0 &&
      precioDescuento! < precio;

  double get precioEfectivo => tieneDescuento ? precioDescuento! : precio;

  double get porcentajeDescuento =>
      tieneDescuento && precio > 0 ? ((precio - precioDescuento!) / precio * 100) : 0.0;

  factory Producto.fromJson(Map<String, dynamic> json) {
    var rawFotos = json['fotos'] as List<dynamic>? ?? [];
    List<ProductoFoto> fotosList = rawFotos
        .where((f) => f is Map<String, dynamic>)
        .map((f) => ProductoFoto.fromJson(f as Map<String, dynamic>))
        .toList();

    return Producto(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      tiendaId: json['tienda_id'] is int ? json['tienda_id'] as int : int.tryParse(json['tienda_id']?.toString() ?? '0') ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      descripcion: json['descripcion']?.toString(),
      precio: (json['precio'] != null) ? double.tryParse(json['precio'].toString()) ?? 0.0 : 0.0,
      precioDescuento: json['precio_descuento'] != null
          ? double.tryParse(json['precio_descuento'].toString())
          : null,
      descuentoActivo: json['descuento_activo'] == true ||
          json['descuento_activo'] == 1 ||
          json['descuento_activo']?.toString() == 'true' ||
          json['tiene_descuento'] == true,
      disponible: json['disponible'] == true || json['disponible'] == 1 || json['disponible']?.toString() == 'true',
      fotos: fotosList,
    );
  }

  String? get primeraFotoUrl {
    for (final f in fotos) {
      if (f.url.trim().isNotEmpty) {
        return f.url;
      }
    }
    return null;
  }
}
