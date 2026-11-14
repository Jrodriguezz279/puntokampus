import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../constants/app_colors.dart';
import '../../constants/app_strings.dart';
import '../../models/categoria.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/vitrina_provider.dart';
import '../../services/pedido_service.dart';
import '../../widgets/common_widgets.dart';
import '../auth/auth_screens.dart';

// P09: Pantalla del Carrito
class CarritoScreen extends StatelessWidget {
  const CarritoScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final auth = Provider.of<AuthProvider>(context);

    if (cart.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mi Carrito')),
        body: EmptyStateWidget(
          icon: '🛒',
          message: 'Tu carrito está vacío. Explora la vitrina y agrega tus productos favoritos.',
          buttonText: 'Ir a la Vitrina',
          onButtonPressed: () => Navigator.pop(context),
        ),
      );
    }

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Mi Carrito'),
        actions: [
          IconButton(
            icon: const Icon(Icons.delete_outline),
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
                      style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white),
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
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.surface,
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, -2))],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Total a pagar:', style: TextStyle(fontSize: 16, color: AppColors.textMuted)),
                Text(
                  '\$${cart.total.toStringAsFixed(2)}',
                  style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.primary),
                ),
              ],
            ),
            const SizedBox(height: 14),
            ElevatedButton(
              onPressed: () {
                if (!auth.tieneSesion) {
                  // Si no tiene sesión, redirigir a login y luego volver
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const BienvenidaScreen()),
                  );
                } else {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: Text(
                auth.tieneSesion ? AppStrings.btnContinuar : 'Iniciar sesión para continuar',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Tienda
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.primaryLight,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.storefront, color: AppColors.primary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Pedido para: ${cart.tiendaNombre}',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Items
            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: cart.items.values.length,
              itemBuilder: (context, index) {
                final item = cart.items.values.elementAt(index);
                return Card(
                  margin: const EdgeInsets.only(bottom: 10),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: const BorderSide(color: AppColors.border),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(item.producto.nombre, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                              const SizedBox(height: 4),
                              Text('\$${item.producto.precio.toStringAsFixed(2)} c/u',
                                  style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
                            ],
                          ),
                        ),
                        // Controles de cantidad
                        Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.remove_circle_outline, color: AppColors.primary),
                              onPressed: () => cart.actualizarCantidad(item.producto.id, item.cantidad - 1),
                            ),
                            Text('${item.cantidad}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            IconButton(
                              icon: const Icon(Icons.add_circle_outline, color: AppColors.primary),
                              onPressed: () => cart.actualizarCantidad(item.producto.id, item.cantidad + 1),
                            ),
                          ],
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '\$${item.subtotal.toStringAsFixed(2)}',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.text),
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

// P10: Pantalla de Entrega y Pago (Checkout)
class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  PuntoEntrega? _puntoSeleccionado;
  String _formaPago = 'efectivo'; // 'efectivo', 'transferencia', 'deuna'
  final _notaController = TextEditingController();
  TimeOfDay _horaEntrega = TimeOfDay.now();
  bool _enviando = false;

  @override
  void initState() {
    super.initState();
    // Ajustar hora al menos 30 minutos después
    final now = DateTime.now().add(const Duration(minutes: 35));
    _horaEntrega = TimeOfDay(hour: now.hour, minute: now.minute);
  }

  @override
  void dispose() {
    _notaController.dispose();
    super.dispose();
  }

  void _enviarPedido() async {
    final cart = Provider.of<CartProvider>(context, listen: false);
    final vitrina = Provider.of<VitrinaProvider>(context, listen: false);

    final punto = _puntoSeleccionado ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first : null);
    if (punto == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Por favor selecciona un punto de entrega.')),
      );
      return;
    }

    setState(() => _enviando = true);

    try {
      final now = DateTime.now();
      final fechaHora = DateTime(now.year, now.month, now.day, _horaEntrega.hour, _horaEntrega.minute);
      final horaString = fechaHora.toIso8601String();

      final res = await PedidoService.crearPedido(
        tiendaId: cart.tiendaId!,
        puntoEntregaId: punto.id,
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
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final vitrina = Provider.of<VitrinaProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Confirmar Entrega y Pago')),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        color: AppColors.surface,
        child: ElevatedButton(
          onPressed: _enviando ? null : _enviarPedido,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: Colors.white,
            minimumSize: const Size(double.infinity, 50),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          child: _enviando
              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : Text('${AppStrings.btnEnviarPedido} (\$${cart.total.toStringAsFixed(2)})',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Punto de Entrega
            const Text('Punto de encuentro en el campus *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<PuntoEntrega>(
                  isExpanded: true,
                  hint: const Text('Seleccionar punto de entrega'),
                  value: _puntoSeleccionado ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first : null),
                  items: vitrina.puntosEntrega.map((p) {
                    return DropdownMenuItem(value: p, child: Text('📍 ${p.nombre}'));
                  }).toList(),
                  onChanged: (val) => setState(() => _puntoSeleccionado = val),
                ),
              ),
            ),

            const SizedBox(height: 20),

            // Hora estimada
            const Text('Hora acordada de entrega *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 8),
            InkWell(
              onTap: () async {
                final picked = await showTimePicker(context: context, initialTime: _horaEntrega);
                if (picked != null) setState(() => _horaEntrega = picked);
              },
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.access_time, color: AppColors.primary),
                    const SizedBox(width: 10),
                    Text(_horaEntrega.format(context), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    const Spacer(),
                    const Text('Cambiar', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 20),

            // Forma de Pago
            const Text('Forma de pago *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 8),
            Row(
              children: [
                _buildMetodoPago('efectivo', '💵 Efectivo'),
                const SizedBox(width: 8),
                _buildMetodoPago('transferencia', '🏦 Transferencia'),
                const SizedBox(width: 8),
                _buildMetodoPago('deuna', '📱 Deuna'),
              ],
            ),

            const SizedBox(height: 20),

            // Nota adicional
            const Text('Nota opcional para el vendedor', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 8),
            TextField(
              controller: _notaController,
              decoration: InputDecoration(
                hintText: 'Ej: Sin cebolla, llevar cambio de \$5',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                filled: true,
                fillColor: AppColors.surface,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetodoPago(String valor, String etiqueta) {
    final isSelected = _formaPago == valor;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _formaPago = valor),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primaryLight : AppColors.surface,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: isSelected ? AppColors.primary : AppColors.border, width: isSelected ? 2 : 1),
          ),
          child: Center(
            child: Text(
              etiqueta,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                color: isSelected ? AppColors.primary : AppColors.text,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// P11: Pedido Enviado y Enlace WhatsApp
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
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No pudimos abrir WhatsApp directamente.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const Text('🎉', style: TextStyle(fontSize: 60)),
              const SizedBox(height: 16),
              const Text(
                '¡Tu pedido quedó registrado!',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'Código de pedido: $codigo',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.primary),
              ),
              const SizedBox(height: 16),
              const Text(
                'Ahora abre WhatsApp para coordinar los últimos detalles y entrega directamente con el vendedor.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.textMuted, fontSize: 14),
              ),
              const SizedBox(height: 32),
              if (whatsappUrl != null)
                ElevatedButton.icon(
                  onPressed: () => _abrirWhatsapp(context),
                  icon: const Icon(Icons.chat),
                  label: const Text(AppStrings.btnAbrirWhatsapp, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF25D366), // Color WhatsApp
                    foregroundColor: Colors.white,
                    minimumSize: const Size(double.infinity, 50),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              const SizedBox(height: 14),
              OutlinedButton(
                onPressed: () {
                  Navigator.popUntil(context, (route) => route.isFirst);
                },
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Volver a la Vitrina'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
