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
    return ProductoFoto(
      id: json['id'] as int,
      url: json['url'] as String? ?? json['foto_url'] as String? ?? '',
      orden: json['orden'] is int ? json['orden'] : int.tryParse(json['orden']?.toString() ?? '0') ?? 0,
    );
  }
}

class Producto {
  final int id;
  final int tiendaId;
  final String nombre;
  final String? descripcion;
  final double precio;
  final bool disponible;
  final List<ProductoFoto> fotos;

  Producto({
    required this.id,
    required this.tiendaId,
    required this.nombre,
    this.descripcion,
    required this.precio,
    this.disponible = true,
    this.fotos = const [],
  });

  factory Producto.fromJson(Map<String, dynamic> json) {
    var rawFotos = json['fotos'] as List<dynamic>? ?? [];
    List<ProductoFoto> fotosList = rawFotos.map((f) => ProductoFoto.fromJson(f as Map<String, dynamic>)).toList();

    return Producto(
      id: json['id'] as int,
      tiendaId: json['tienda_id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      descripcion: json['descripcion'] as String?,
      precio: (json['precio'] != null) ? double.tryParse(json['precio'].toString()) ?? 0.0 : 0.0,
      disponible: json['disponible'] == true || json['disponible'] == 1,
      fotos: fotosList,
    );
  }

  String? get primeraFotoUrl {
    if (fotos.isNotEmpty) {
      return fotos.first.url;
    }
    return null;
  }
}
