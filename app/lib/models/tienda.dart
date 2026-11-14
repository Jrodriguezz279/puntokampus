import 'categoria.dart';
import 'producto.dart';

class Tienda {
  final int id;
  final int usuarioId;
  final String nombre;
  final String slug;
  final String? descripcion;
  final String? logoUrl;
  final String? portadaUrl;
  final String? horario;
  final String? whatsapp;
  final String plan; // 'gratis' o 'premium'
  final String estado; // 'activa', 'baja', 'suspendida'
  final Categoria? categoria1;
  final Categoria? categoria2;
  final PuntoEntrega? puntoEntrega;
  final List<Producto> productos;

  Tienda({
    required this.id,
    required this.usuarioId,
    required this.nombre,
    required this.slug,
    this.descripcion,
    this.logoUrl,
    this.portadaUrl,
    this.horario,
    this.whatsapp,
    this.plan = 'gratis',
    this.estado = 'activa',
    this.categoria1,
    this.categoria2,
    this.puntoEntrega,
    this.productos = const [],
  });

  bool get esPremium => plan.toLowerCase() == 'premium';
  bool get estaActiva => estado.toLowerCase() == 'activa';

  factory Tienda.fromJson(Map<String, dynamic> json) {
    var rawProds = json['productos'] as List<dynamic>? ?? [];
    List<Producto> prodsList = rawProds.map((p) => Producto.fromJson(p as Map<String, dynamic>)).toList();

    return Tienda(
      id: json['id'] as int,
      usuarioId: json['usuario_id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      descripcion: json['descripcion'] as String?,
      logoUrl: json['logo_url'] as String?,
      portadaUrl: json['portada_url'] as String?,
      horario: json['horario'] as String?,
      whatsapp: json['whatsapp'] as String?,
      plan: json['plan'] as String? ?? 'gratis',
      estado: json['estado'] as String? ?? 'activa',
      categoria1: json['categoria_1'] != null ? Categoria.fromJson(json['categoria_1'] as Map<String, dynamic>) : null,
      categoria2: json['categoria_2'] != null ? Categoria.fromJson(json['categoria_2'] as Map<String, dynamic>) : null,
      puntoEntrega: json['punto_entrega'] != null ? PuntoEntrega.fromJson(json['punto_entrega'] as Map<String, dynamic>) : null,
      productos: prodsList,
    );
  }
}
