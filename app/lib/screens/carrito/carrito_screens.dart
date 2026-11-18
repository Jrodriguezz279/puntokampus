import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../theme/theme.dart';
import '../../constants/app_strings.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/vitrina_provider.dart';
import '../../services/pedido_service.dart';
import '../../widgets/pk_components.dart';
import '../auth/auth_screens.dart';

// ══════════════════════════════════════════════════════════════════════════════
// CARRITO & CHECKOUT — P09 Carrito / P10 Checkout / P11 Confirmación
// Cumple con docs/DISENO-VISUAL.md
// ══════════════════════════════════════════════════════════════════════════════

// ─── P09: Carrito de Compras ───────────────────────────────────────────────────
class CarritoScreen extends StatelessWidget {
  const CarritoScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final auth = Provider.of<AuthProvider>(context);

    if (cart.isEmpty) {
      return Scaffold(
        backgroundColor: PKColors.fondo,
        appBar: AppBar(title: const Text('Mi Carrito')),
        body: PKEstadoVacio(
          icono: Icons.shopping_bag_outlined,
          titulo: 'Tu carrito está vacío',
          subtitulo: 'Explora la vitrina y agrega tus productos favoritos de los puestos del campus.',
          botonTexto: 'Ir a la Vitrina',
          onBoton: () => Navigator.pop(context),
        ),
      );
    }

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Mi Carrito'),
        actions: [
          IconButton(
            icon: const Icon(Icons.delete_outline, color: PKColors.error),
            tooltip: 'Vaciar carrito',
            onPressed: () {
              showDialog(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('¿Vaciar carrito?'),
                  content: const Text('Se quitarán todos los productos de tu lista.'),
                  actions: [
                    TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancelar')),
                    ElevatedButton(
                      onPressed: () {
                        cart.vaciar();
                        Navigator.pop(ctx);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: PKColors.error,
                        foregroundColor: PKColors.sobrePrimario,
                      ),
                      child: const Text('Vaciar'),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Total a pagar:', style: PKTextStyles.cuerpoSecundario),
                Text(
                  '\$${cart.total.toStringAsFixed(2)}',
                  style: PKTextStyles.precioGrande,
                ),
              ],
            ),
            const SizedBox(height: PKSpacing.md),
            PKBotonPrimario(
              texto: auth.tieneSesion ? AppStrings.btnContinuar : 'Iniciar sesión para continuar',
              onPressed: () {
                if (!auth.tieneSesion) {
                  Navigator.push(context, MaterialPageRoute(builder: (_) => const BienvenidaScreen()));
                } else {
                  Navigator.push(context, MaterialPageRoute(builder: (_) => const CheckoutScreen()));
                }
              },
            ),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Banner Tienda
            PKTarjeta(
              padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
              child: Row(
                children: [
                  const Icon(Icons.storefront_outlined, color: PKColors.primario),
                  const SizedBox(width: PKSpacing.sm),
                  Expanded(
                    child: Text(
                      'Pedido para: ${cart.tiendaNombre}',
                      style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15, color: PKColors.primarioSuaveTexto),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: PKSpacing.base),

            // Items
            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: cart.items.values.length,
              itemBuilder: (context, index) {
                final item = cart.items.values.elementAt(index);
                return Container(
                  margin: const EdgeInsets.only(bottom: PKSpacing.sm),
                  child: PKTarjeta(
                    padding: const EdgeInsets.all(PKSpacing.md),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(item.producto.nombre, style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15)),
                              const SizedBox(height: 2),
                              Row(
                                children: [
                                  Text(
                                    '\$${item.producto.precioEfectivo.toStringAsFixed(2)} c/u',
                                    style: PKTextStyles.etiqueta.copyWith(
                                      color: item.producto.tieneDescuento ? const Color(0xFFCC4E01) : null,
                                      fontWeight: item.producto.tieneDescuento ? FontWeight.bold : null,
                                    ),
                                  ),
                                  if (item.producto.tieneDescuento) ...[
                                    const SizedBox(width: 4),
                                    Text(
                                      '\$${item.producto.precio.toStringAsFixed(2)}',
                                      style: PKTextStyles.etiqueta.copyWith(
                                        decoration: TextDecoration.lineThrough,
                                        fontSize: 10,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ],
                          ),
                        ),
                        // Controles de cantidad
                        Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.remove_circle_outline, color: PKColors.primario, size: 20),
                              onPressed: () => cart.actualizarCantidad(item.producto.id, item.cantidad - 1),
                            ),
                            Text('${item.cantidad}', style: PKTextStyles.tituloTarjeta),
                            IconButton(
                              icon: const Icon(Icons.add_circle_outline, color: PKColors.primario, size: 20),
                              onPressed: () => cart.actualizarCantidad(item.producto.id, item.cantidad + 1),
                            ),
                          ],
                        ),
                        const SizedBox(width: PKSpacing.sm),
                        Text(
                          '\$${item.subtotal.toStringAsFixed(2)}',
                          style: PKTextStyles.precio.copyWith(fontSize: 16),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

// ─── P10: Confirmar Entrega y Pago (Checkout) ─────────────────────────────────
class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  int? _puntoSeleccionadoId;
  String _formaPago = 'efectivo'; // 'efectivo', 'transferencia', 'deuna'
  final _notaController = TextEditingController();
  DateTime _fechaEntrega = DateTime.now();
  TimeOfDay _horaEntrega = TimeOfDay.now();
  bool _enviando = false;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now().add(const Duration(minutes: 35));
    _fechaEntrega = DateTime(now.year, now.month, now.day);
    _horaEntrega = TimeOfDay(hour: now.hour, minute: now.minute);

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final vitrina = Provider.of<VitrinaProvider>(context, listen: false);
      if (vitrina.puntosEntrega.isEmpty) {
        vitrina.cargarInicial();
      }
    });
  }

  @override
  void dispose() {
    _notaController.dispose();
    super.dispose();
  }

  Future<void> _seleccionarFecha(BuildContext context) async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _fechaEntrega.isBefore(now) ? now : _fechaEntrega,
      firstDate: DateTime(now.year, now.month, now.day),
      lastDate: now.add(const Duration(days: 7)),
      helpText: 'Selecciona la fecha de entrega',
      confirmText: 'Aceptar',
      cancelText: 'Cancelar',
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: PKColors.primario,
              onPrimary: PKColors.sobrePrimario,
              surface: PKColors.tarjeta,
              onSurface: PKColors.titulo,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _fechaEntrega = picked);
    }
  }

  Future<void> _seleccionarHora(BuildContext context) async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _horaEntrega,
      helpText: 'Selecciona la hora de entrega',
      confirmText: 'Aceptar',
      cancelText: 'Cancelar',
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: PKColors.primario,
              onPrimary: PKColors.sobrePrimario,
              surface: PKColors.tarjeta,
              onSurface: PKColors.titulo,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _horaEntrega = picked);
    }
  }

  String _formatoFecha(DateTime fecha) {
    final now = DateTime.now();
    final hoy = DateTime(now.year, now.month, now.day);
    final manana = hoy.add(const Duration(days: 1));
    final fechaComp = DateTime(fecha.year, fecha.month, fecha.day);

    final fechaStr = '${fecha.day.toString().padLeft(2, '0')}/${fecha.month.toString().padLeft(2, '0')}/${fecha.year}';
    if (fechaComp == hoy) {
      return 'Hoy ($fechaStr)';
    } else if (fechaComp == manana) {
      return 'Mañana ($fechaStr)';
    }
    return fechaStr;
  }

  void _enviarPedido() async {
    final cart = Provider.of<CartProvider>(context, listen: false);
    final vitrina = Provider.of<VitrinaProvider>(context, listen: false);

    final selectedId = _puntoSeleccionadoId ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first.id : null);
    if (selectedId == null) {
      pkSnackError(context, 'Por favor selecciona un punto de encuentro.');
      return;
    }

    // R17/A4: Validar rango de fecha/hora (30 min mínimo, 7 días máximo)
    final fechaHora = DateTime(
      _fechaEntrega.year,
      _fechaEntrega.month,
      _fechaEntrega.day,
      _horaEntrega.hour,
      _horaEntrega.minute,
    );
    final minima = DateTime.now().add(const Duration(minutes: 30));
    final maxima = DateTime.now().add(const Duration(days: 7));
    if (fechaHora.isBefore(minima)) {
      pkSnackError(context, 'La hora de entrega debe ser al menos 30 minutos en el futuro.');
      return;
    }
    if (fechaHora.isAfter(maxima)) {
      pkSnackError(context, 'La hora de entrega no puede ser más de 7 días en el futuro.');
      return;
    }

    setState(() => _enviando = true);

    try {
      final horaString = fechaHora.toIso8601String();

      final res = await PedidoService.crearPedido(
        tiendaId: cart.tiendaId!,
        puntoEntregaId: selectedId,
        horaEntrega: horaString,
        formaPago: _formaPago,
        nota: _notaController.text.trim().isNotEmpty ? _notaController.text.trim() : null,
        items: cart.toOrderItems(),
      );

      final codigo = res['codigo'] as String? ?? 'PK-EXITO';
      final whatsappUrl = res['whatsapp_url'] as String?;

      cart.vaciar();

      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => PedidoEnviadoScreen(codigo: codigo, whatsappUrl: whatsappUrl),
        ),
      );
    } catch (e) {
      setState(() => _enviando = false);
      pkSnackError(context, e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final vitrina = Provider.of<VitrinaProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Confirmar Pedido')),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: PKBotonPrimario(
          texto: '${AppStrings.btnEnviarPedido} (\$${cart.total.toStringAsFixed(2)})',
          onPressed: _enviando ? null : _enviarPedido,
          cargando: _enviando,
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          children: [
            // Sección 1: Lugar de encuentro
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Lugar de encuentro en el campus', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),
                  Text('Punto de encuentro *', style: PKTextStyles.etiqueta),
                  const SizedBox(height: PKSpacing.xs),
                  Builder(
                    builder: (context) {
                      final selectedPuntoId = _puntoSeleccionadoId ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first.id : null);
                      return DropdownButtonFormField<int>(
                        value: vitrina.puntosEntrega.any((p) => p.id == selectedPuntoId) ? selectedPuntoId : null,
                        items: vitrina.puntosEntrega
                            .map((p) => DropdownMenuItem<int>(value: p.id, child: Text('📍 ${p.nombre}', style: PKTextStyles.cuerpo)))
                            .toList(),
                        onChanged: (val) => setState(() => _puntoSeleccionadoId = val),
                      );
                    },
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Sección 2: Día y hora de entrega
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Día y hora de entrega', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),

                  // Selector de Fecha
                  Text('Fecha de entrega *', style: PKTextStyles.etiqueta),
                  const SizedBox(height: PKSpacing.xs),
                  InkWell(
                    onTap: () => _seleccionarFecha(context),
                    borderRadius: PKRadii.botonAll,
                    child: Container(
                      padding: const EdgeInsets.all(PKSpacing.base),
                      decoration: BoxDecoration(
                        color: PKColors.tarjeta,
                        borderRadius: PKRadii.botonAll,
                        border: Border.all(color: PKColors.borde),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today_outlined, color: PKColors.primario, size: 20),
                          const SizedBox(width: PKSpacing.sm),
                          Text(_formatoFecha(_fechaEntrega), style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15)),
                          const Spacer(),
                          Text('Elegir fecha', style: PKTextStyles.boton.copyWith(color: PKColors.primarioSuaveTexto, fontSize: 13)),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: PKSpacing.base),

                  // Selector de Hora
                  Text('Hora estimada de entrega *', style: PKTextStyles.etiqueta),
                  const SizedBox(height: PKSpacing.xs),
                  InkWell(
                    onTap: () => _seleccionarHora(context),
                    borderRadius: PKRadii.botonAll,
                    child: Container(
                      padding: const EdgeInsets.all(PKSpacing.base),
                      decoration: BoxDecoration(
                        color: PKColors.tarjeta,
                        borderRadius: PKRadii.botonAll,
                        border: Border.all(color: PKColors.borde),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.access_time_outlined, color: PKColors.primario, size: 20),
                          const SizedBox(width: PKSpacing.sm),
                          Text(_horaEntrega.format(context), style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15)),
                          const Spacer(),
                          Text('Elegir hora', style: PKTextStyles.boton.copyWith(color: PKColors.primarioSuaveTexto, fontSize: 13)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Sección 3: Forma de pago
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Forma de pago', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),
                  Row(
                    children: [
                      _buildMetodoPago('efectivo', '💵 Efectivo'),
                      const SizedBox(width: PKSpacing.sm),
                      _buildMetodoPago('transferencia', '🏦 Transfer.'),
                      const SizedBox(width: PKSpacing.sm),
                      _buildMetodoPago('deuna', '📱 Deuna'),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Sección 4: Nota adicional
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Indicaciones para el vendedor', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),
                  PKCampoEtiquetado(
                    etiqueta: 'Nota opcional',
                    hint: 'Ej: Sin hielo, llevar cambio de \$5',
                    controller: _notaController,
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.xxl),
          ],
        ),
      ),
    );
  }

  Widget _buildMetodoPago(String valor, String etiqueta) {
    final isSelected = _formaPago == valor;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _formaPago = valor),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: PKSpacing.md),
          decoration: BoxDecoration(
            color: isSelected ? PKColors.primarioSuave : PKColors.tarjeta,
            borderRadius: PKRadii.botonAll,
            border: Border.all(
              color: isSelected ? PKColors.primario : PKColors.borde,
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Center(
            child: Text(
              etiqueta,
              style: PKTextStyles.etiqueta.copyWith(
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                color: isSelected ? PKColors.primarioSuaveTexto : PKColors.titulo,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── P11: Pedido Enviado y WhatsApp ───────────────────────────────────────────
class PedidoEnviadoScreen extends StatelessWidget {
  final String codigo;
  final String? whatsappUrl;

  const PedidoEnviadoScreen({super.key, required this.codigo, this.whatsappUrl});

  void _abrirWhatsapp(BuildContext context) async {
    if (whatsappUrl != null && whatsappUrl!.isNotEmpty) {
      final uri = Uri.parse(whatsappUrl!);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        pkSnackError(context, 'No pudimos abrir WhatsApp directamente.');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(PKSpacing.xl),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                width: 90,
                height: 90,
                decoration: const BoxDecoration(
                  color: PKColors.okFondo,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_outline_rounded, size: 54, color: PKColors.okTexto),
              ),
              const SizedBox(height: PKSpacing.lg),
              Text(
                '¡Pedido registrado!',
                style: PKTextStyles.tituloPantalla,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: PKSpacing.sm),
              PKPastillaEstado(estado: EstadoPedido.pendiente, textoPersonalizado: 'Código: #$codigo'),
              const SizedBox(height: PKSpacing.base),
              Text(
                'Abre WhatsApp para coordinar los últimos detalles y entrega directamente con el vendedor.',
                textAlign: TextAlign.center,
                style: PKTextStyles.cuerpoSecundario,
              ),
              const SizedBox(height: PKSpacing.xl),
              if (whatsappUrl != null) ...[
                PKBotonPrimario(
                  texto: AppStrings.btnAbrirWhatsapp,
                  icono: Icons.chat_outlined,
                  onPressed: () => _abrirWhatsapp(context),
                ),
                const SizedBox(height: PKSpacing.sm),
              ],
              PKBotonSecundario(
                texto: 'Volver a la Vitrina',
                icono: Icons.storefront_outlined,
                onPressed: () {
                  Navigator.popUntil(context, (route) => route.isFirst);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
