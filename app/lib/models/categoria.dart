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
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      slug: json['slug']?.toString() ?? '',
      icono: json['icono']?.toString(),
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
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      descripcion: json['descripcion']?.toString(),
      ubicacionReferencia: json['ubicacion_referencia']?.toString(),
      activa: json['activa'] == true || json['activa'] == 1 || json['activa']?.toString() == 'true',
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'nombre': nombre,
    'descripcion': descripcion,
    'ubicacion_referencia': ubicacionReferencia,
    'activa': activa,
  };

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is PuntoEntrega && runtimeType == other.runtimeType && id == other.id;

  @override
  int get hashCode => id.hashCode;
}
