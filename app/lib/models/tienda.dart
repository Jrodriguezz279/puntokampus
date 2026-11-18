import 'dart:convert';
import '../constants/api_constants.dart';
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
  // Tramo 2: Horario por días/franja e interruptor de pedidos
  final bool recibiendoPedidos;
  final List<int>? diasAtencion;
  final String? horaApertura;
  final String? horaCierre;
  final bool estaAbierto;
  final String? siguienteApertura;

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
    this.recibiendoPedidos = true,
    this.diasAtencion,
    this.horaApertura,
    this.horaCierre,
    this.estaAbierto = true,
    this.siguienteApertura,
  });

  bool get esPremium => plan.toLowerCase() == 'premium';
  bool get estaActiva => estado.toLowerCase() == 'activa';
  bool get puedeRecibirPedidos => estaActiva && recibiendoPedidos;

  factory Tienda.fromJson(Map<String, dynamic> json) {
    var rawProds = json['productos'] as List<dynamic>? ?? [];
    List<Producto> prodsList = rawProds
        .where((p) => p is Map<String, dynamic>)
        .map((p) => Producto.fromJson(p as Map<String, dynamic>))
        .toList();

    return Tienda(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      usuarioId: json['usuario_id'] is int ? json['usuario_id'] as int : int.tryParse(json['usuario_id']?.toString() ?? '0') ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      slug: json['slug']?.toString() ?? '',
      descripcion: json['descripcion']?.toString(),
      logoUrl: ApiConstants.formatearUrlImagen(json['logo_url']?.toString()),
      portadaUrl: ApiConstants.formatearUrlImagen(json['portada_url']?.toString()),
      horario: json['horario']?.toString(),
      whatsapp: json['whatsapp']?.toString(),
      plan: json['plan']?.toString() ?? 'gratis',
      estado: json['estado']?.toString() ?? 'activa',
      categoria1: (json['categoria_1'] is Map<String, dynamic> && json['categoria_1']['id'] != null)
          ? Categoria.fromJson(json['categoria_1'] as Map<String, dynamic>)
          : null,
      categoria2: (json['categoria_2'] is Map<String, dynamic> && json['categoria_2']['id'] != null)
          ? Categoria.fromJson(json['categoria_2'] as Map<String, dynamic>)
          : null,
      puntoEntrega: (json['punto_entrega'] is Map<String, dynamic> && json['punto_entrega']['id'] != null)
          ? PuntoEntrega.fromJson(json['punto_entrega'] as Map<String, dynamic>)
          : null,
      productos: prodsList,
      // Tramo 2
      recibiendoPedidos: json['recibiendo_pedidos'] == true || json['recibiendo_pedidos'] == 1 || json['recibiendo_pedidos']?.toString() == 'true',
      diasAtencion: (json['dias_atencion'] is List)
          ? (json['dias_atencion'] as List)
              .map((d) => int.tryParse(d.toString()) ?? 0)
              .where((d) => d > 0)
              .toList()
          : (json['dias_atencion'] != null && json['dias_atencion'].toString().startsWith('[')
              ? (jsonDecode(json['dias_atencion'].toString()) as List<dynamic>?)
                  ?.map((d) => int.tryParse(d.toString()) ?? 0)
                  .where((d) => d > 0)
                  .toList()
              : null),
      horaApertura: json['hora_apertura']?.toString(),
      horaCierre: json['hora_cierre']?.toString(),
      estaAbierto: json['esta_abierto'] == true || json['esta_abierto'] == 1 || json['esta_abierto']?.toString() == 'true',
      siguienteApertura: json['siguiente_apertura']?.toString(),
    );
  }
}
