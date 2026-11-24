import 'producto.dart';

class PedidoItem {
  final int id;
  final int productoId;
  final String nombreProducto;
  final double precioUnitario;
  final int cantidad;
  final double subtotal;

  PedidoItem({
    required this.id,
    required this.productoId,
    required this.nombreProducto,
    required this.precioUnitario,
    required this.cantidad,
    required this.subtotal,
  });

  factory PedidoItem.fromJson(Map<String, dynamic> json) {
    return PedidoItem(
      id: json['id'] as int? ?? 0,
      productoId: json['producto_id'] as int? ?? 0,
      nombreProducto: json['nombre_producto'] as String? ?? json['producto']?['nombre'] as String? ?? '',
      precioUnitario: double.tryParse(json['precio_unitario']?.toString() ?? '0') ?? 0.0,
      cantidad: json['cantidad'] as int? ?? 1,
      subtotal: double.tryParse(json['subtotal']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class Pedido {
  final int id;
  final String codigo;
  final int tiendaId;
  final String? nombreTienda;
  final String? whatsappTienda;
  final int compradorId;
  final String? nombreComprador;
  final String? puntoEntrega;
  final String? horaEntrega;
  final String formaPago;
  final String? nota;
  final double total;
  final String estado; // 'pendiente', 'entregado', 'cancelado', 'vencido'
  final String? whatsappUrl;
  final List<PedidoItem> items;
  final String? creadoEn;
  /// Solo visible para el comprador dueño del pedido cuando está en estado "pendiente".
  final String? codigoEntrega;
  final bool codigoBloqueado;
  // Tramo 2: QR
  final String? qrUrl;
  final String? qrPayload;
  // Fase 3: Calificaciones
  final bool puedeCalificar;
  final int? calificacionRecibida;
  final int? calificacionDada;
  final Map<String, dynamic>? compradorEstrellas;
  // Fase 4: Deuna QR y entregado con
  final String? deunaQrUrl;
  final String? entregadoCon;

  Pedido({
    required this.id,
    required this.codigo,
    required this.tiendaId,
    this.nombreTienda,
    this.whatsappTienda,
    required this.compradorId,
    this.nombreComprador,
    this.puntoEntrega,
    this.horaEntrega,
    required this.formaPago,
    this.nota,
    required this.total,
    required this.estado,
    this.whatsappUrl,
    this.items = const [],
    this.creadoEn,
    this.codigoEntrega,
    this.codigoBloqueado = false,
    this.qrUrl,
    this.qrPayload,
    this.puedeCalificar = false,
    this.calificacionRecibida,
    this.calificacionDada,
    this.compradorEstrellas,
    this.deunaQrUrl,
    this.entregadoCon,
  });

  bool get esPendiente => estado.trim().toLowerCase() == 'pendiente';
  bool get esEntregado => estado.trim().toLowerCase() == 'entregado';
  bool get esCancelado => estado.trim().toLowerCase() == 'cancelado';

  factory Pedido.fromJson(Map<String, dynamic> json) {
    var rawItems = json['items'] as List<dynamic>? ?? [];
    List<PedidoItem> itemsList = rawItems.map((i) => PedidoItem.fromJson(i as Map<String, dynamic>)).toList();

    return Pedido(
      id: json['id'] as int,
      codigo: json['codigo'] as String? ?? '',
      tiendaId: json['tienda_id'] as int? ?? 0,
      nombreTienda: json['tienda']?['nombre'] as String?,
      whatsappTienda: json['tienda']?['whatsapp'] as String?,
      compradorId: json['comprador_id'] as int? ?? 0,
      nombreComprador: json['comprador']?['nombres'] as String?,
      puntoEntrega: json['punto_entrega']?['nombre'] as String?,
      horaEntrega: json['hora_entrega'] as String?,
      formaPago: json['forma_pago'] as String? ?? 'efectivo',
      nota: json['nota'] as String?,
      total: double.tryParse(json['total']?.toString() ?? '0') ?? 0.0,
      estado: json['estado'] as String? ?? 'pendiente',
      whatsappUrl: json['whatsapp_url'] as String?,
      items: itemsList,
      creadoEn: json['created_at'] as String?,
      codigoEntrega: json['codigo_entrega'] as String?,
      codigoBloqueado: json['codigo_bloqueado'] == true || json['codigo_bloqueado'] == 1,
      qrUrl: json['qr_url']?.toString(),
      qrPayload: json['qr_payload']?.toString(),
      puedeCalificar: json['puede_calificar'] == true || json['puede_calificar'] == 1,
      calificacionRecibida: json['calificacion_recibida'] is int
          ? json['calificacion_recibida'] as int
          : int.tryParse(json['calificacion_recibida']?.toString() ?? ''),
      calificacionDada: json['calificacion_dada'] is int
          ? json['calificacion_dada'] as int
          : int.tryParse(json['calificacion_dada']?.toString() ?? ''),
      compradorEstrellas: json['comprador_estrellas'] is Map<String, dynamic>
          ? json['comprador_estrellas'] as Map<String, dynamic>
          : null,
      deunaQrUrl: json['deuna_qr_url']?.toString(),
      entregadoCon: json['entregado_con']?.toString(),
    );
  }
}

class CartItem {
  final Producto producto;
  int cantidad;

  CartItem({
    required this.producto,
    this.cantidad = 1,
  });

  double get subtotal => producto.precioEfectivo * cantidad;
}
