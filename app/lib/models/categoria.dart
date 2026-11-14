class Categoria {
  final int id;
  final String nombre;
  final String slug;
  final String? icono;
  final int orden;

  Categoria({
    required this.id,
    required this.nombre,
    required this.slug,
    this.icono,
    this.orden = 0,
  });

  factory Categoria.fromJson(Map<String, dynamic> json) {
    return Categoria(
      id: json['id'] as int,
      nombre: json['nombre'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      icono: json['icono'] as String?,
      orden: json['orden'] is int ? json['orden'] : int.tryParse(json['orden']?.toString() ?? '0') ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'nombre': nombre,
    'slug': slug,
    'icono': icono,
    'orden': orden,
  };
}

class PuntoEntrega {
  final int id;
  final String nombre;
  final String? descripcion;
  final String? ubicacionReferencia;
  final bool activa;

  PuntoEntrega({
    required this.id,
    required this.nombre,
    this.descripcion,
    this.ubicacionReferencia,
    this.activa = true,
  });

  factory PuntoEntrega.fromJson(Map<String, dynamic> json) {
    return PuntoEntrega(
      id: json['id'] as int,
      nombre: json['nombre'] as String? ?? '',
      descripcion: json['descripcion'] as String?,
      ubicacionReferencia: json['ubicacion_referencia'] as String?,
      activa: json['activa'] == true || json['activa'] == 1,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'nombre': nombre,
    'descripcion': descripcion,
    'ubicacion_referencia': ubicacionReferencia,
    'activa': activa,
  };
}
