import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../constants/app_strings.dart';
import '../../models/categoria.dart';
import '../../models/pedido.dart';
import '../../models/producto.dart';
import '../../models/tienda.dart';
import '../../providers/tienda_provider.dart';
import '../../providers/vitrina_provider.dart';
import '../../services/pedido_service.dart';
import '../../widgets/common_widgets.dart';

// P15: Mi Tienda (Panel del Vendedor)
class MiTiendaScreen extends StatefulWidget {
  const MiTiendaScreen({super.key});

  @override
  State<MiTiendaScreen> createState() => _MiTiendaScreenState();
}

class _MiTiendaScreenState extends State<MiTiendaScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TiendaProvider>(context, listen: false).cargarMiTienda();
    });
  }

  @override
  Widget build(BuildContext context) {
    final tiendaProv = Provider.of<TiendaProvider>(context);

    if (tiendaProv.cargando) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mi Tienda')),
        body: const Padding(
          padding: EdgeInsets.all(16.0),
          child: Column(children: [LoadingSkeleton(height: 120), LoadingSkeleton(height: 120)]),
        ),
      );
    }

    if (!tiendaProv.tieneTienda) {
      // P14 Invitación a crear tienda
      return Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(title: const Text('Mi Tienda')),
        body: EmptyStateWidget(
          icon: '🏪',
          message: '¿Vendes comida, snacks, accesorios o servicios en la UTB? Crea tu tienda y llega a miles de estudiantes.',
          buttonText: AppStrings.btnCrearTienda,
          onButtonPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const FormTiendaScreen()),
            );
          },
        ),
      );
    }

    final t = tiendaProv.miTienda!;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(t.nombre),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit_outlined),
            tooltip: 'Editar tienda',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => FormTiendaScreen(tienda: t)),
              );
            },
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const FormProductoScreen()),
          );
        },
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Nuevo Producto', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: () => tiendaProv.cargarMiTienda(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Tarjeta Resumen
              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(t.nombre, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: t.esPremium ? AppColors.accentLight : AppColors.primaryLight,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              t.esPremium ? '⭐ Sacó 10' : 'Plan Básico',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: t.esPremium ? AppColors.accent : AppColors.primary,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      if (t.descripcion != null) Text(t.descripcion!, style: const TextStyle(color: AppColors.text)),
                      const SizedBox(height: 8),
                      if (t.puntoEntrega != null)
                        Text('📍 Entrega: ${t.puntoEntrega!.nombre}', style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
                      if (t.horario != null)
                        Text('⏰ Horario: ${t.horario}', style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Botón Pedidos Recibidos
              ListTile(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                  side: const BorderSide(color: AppColors.border),
                ),
                tileColor: AppColors.surface,
                leading: const Icon(Icons.inbox_outlined, color: AppColors.primary),
                title: const Text('Pedidos de mi tienda', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Gestionar pedidos entrantes y despachos'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const PedidosTiendaScreen()),
                  );
                },
              ),

              const SizedBox(height: 24),

              // Lista de Productos de la Tienda
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Mis Productos (${t.productos.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                ],
              ),
              const SizedBox(height: 12),

              if (t.productos.isEmpty)
                const EmptyStateWidget(
                  icon: '🏷️',
                  message: AppStrings.emptyMisProductos,
                )
              else
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: t.productos.length,
                  itemBuilder: (context, index) {
                    final prod = t.productos[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 10),
                      child: ListTile(
                        leading: Container(
                          width: 48,
                          height: 48,
                          decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(8)),
                          child: const Icon(Icons.fastfood, color: AppColors.primary),
                        ),
                        title: Text(prod.nombre, style: const TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: Text('\$${prod.precio.toStringAsFixed(2)} • ${prod.disponible ? "Disponible" : "Agotado"}'),
                        trailing: IconButton(
                          icon: const Icon(Icons.delete_outline, color: AppColors.error),
                          onPressed: () {
                            showDialog(
                              context: context,
                              builder: (ctx) => AlertDialog(
                                title: const Text('¿Eliminar producto?'),
                                content: Text('¿Deseas eliminar "${prod.nombre}" de tu catálogo?'),
                                actions: [
                                  TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancelar')),
                                  ElevatedButton(
                                    onPressed: () {
                                      tiendaProv.eliminarProducto(prod.id);
                                      Navigator.pop(ctx);
                                    },
                                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white),
                                    child: const Text('Eliminar'),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
                    );
                  },
                ),

              const SizedBox(height: 60),
            ],
          ),
        ),
      ),
    );
  }
}

// P14: Formulario de Tienda (Crear o Editar)
class FormTiendaScreen extends StatefulWidget {
  final Tienda? tienda;
  const FormTiendaScreen({super.key, this.tienda});

  @override
  State<FormTiendaScreen> createState() => _FormTiendaScreenState();
}

class _FormTiendaScreenState extends State<FormTiendaScreen> {
  final _nombreController = TextEditingController();
  final _descripcionController = TextEditingController();
  final _horarioController = TextEditingController();
  final _whatsappController = TextEditingController();
  int? _cat1Id;
  int? _puntoId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<VitrinaProvider>(context, listen: false).cargarInicial();
    });

    if (widget.tienda != null) {
      _nombreController.text = widget.tienda!.nombre;
      _descripcionController.text = widget.tienda!.descripcion ?? '';
      _horarioController.text = widget.tienda!.horario ?? '';
      _whatsappController.text = widget.tienda!.whatsapp ?? '';
      _cat1Id = widget.tienda!.categoria1?.id;
      _puntoId = widget.tienda!.puntoEntrega?.id;
    }
  }

  void _guardar() async {
    final vitrina = Provider.of<VitrinaProvider>(context, listen: false);
    final tiendaProv = Provider.of<TiendaProvider>(context, listen: false);

    final catId = _cat1Id ?? (vitrina.categorias.isNotEmpty ? vitrina.categorias.first.id : 1);
    final puntoId = _puntoId ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first.id : 1);

    final ok = await tiendaProv.guardarTienda(
      nombre: _nombreController.text.trim(),
      descripcion: _descripcionController.text.trim(),
      categoria1Id: catId,
      puntoEntregaId: puntoId,
      horario: _horarioController.text.trim(),
      whatsapp: _whatsappController.text.trim(),
    );

    if (!mounted) return;
    if (ok) {
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(tiendaProv.error ?? 'Error al guardar.'), backgroundColor: AppColors.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final vitrina = Provider.of<VitrinaProvider>(context);

    // Si aún no hay selección y hay opciones, asignar la primera
    final selectedCatId = _cat1Id ?? (vitrina.categorias.isNotEmpty ? vitrina.categorias.first.id : null);
    final selectedPuntoId = _puntoId ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first.id : null);

    return Scaffold(
      appBar: AppBar(title: Text(widget.tienda == null ? 'Crear mi tienda' : 'Editar tienda')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(
              controller: _nombreController,
              decoration: const InputDecoration(labelText: 'Nombre de la tienda *', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _descripcionController,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'Descripción *', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<int>(
              value: vitrina.categorias.any((c) => c.id == selectedCatId) ? selectedCatId : null,
              decoration: const InputDecoration(labelText: 'Categoría principal *', border: OutlineInputBorder()),
              items: vitrina.categorias
                  .map((c) => DropdownMenuItem<int>(value: c.id, child: Text(c.nombre)))
                  .toList(),
              onChanged: (val) => setState(() => _cat1Id = val),
            ),
            const SizedBox(height: 14),
            DropdownButtonFormField<int>(
              value: vitrina.puntosEntrega.any((p) => p.id == selectedPuntoId) ? selectedPuntoId : null,
              decoration: const InputDecoration(labelText: 'Punto de entrega preferido *', border: OutlineInputBorder()),
              items: vitrina.puntosEntrega
                  .map((p) => DropdownMenuItem<int>(value: p.id, child: Text(p.nombre)))
                  .toList(),
              onChanged: (val) => setState(() => _puntoId = val),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _horarioController,
              decoration: const InputDecoration(labelText: 'Horario disponible *', hintText: 'ej: Lun a Vie 09:00 - 14:00', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _whatsappController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'WhatsApp para pedidos', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _guardar,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 50),
              ),
              child: Text(widget.tienda == null ? AppStrings.btnCrearTienda : AppStrings.btnGuardarCambios),
            ),
          ],
        ),
      ),
    );
  }
}

// P16: Formulario de Producto Nuevo
class FormProductoScreen extends StatefulWidget {
  const FormProductoScreen({super.key});

  @override
  State<FormProductoScreen> createState() => _FormProductoScreenState();
}

class _FormProductoScreenState extends State<FormProductoScreen> {
  final _nombreController = TextEditingController();
  final _descController = TextEditingController();
  final _precioController = TextEditingController();

  void _guardar() async {
    final precio = double.tryParse(_precioController.text.trim()) ?? 0.0;
    if (_nombreController.text.trim().isEmpty || precio <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ingresa nombre y precio válido.')));
      return;
    }

    final tiendaProv = Provider.of<TiendaProvider>(context, listen: false);
    final ok = await tiendaProv.crearProducto(
      nombre: _nombreController.text.trim(),
      descripcion: _descController.text.trim(),
      precio: precio,
    );

    if (!mounted) return;
    if (ok) {
      Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nuevo Producto')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(
              controller: _nombreController,
              decoration: const InputDecoration(labelText: 'Nombre del producto *', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _precioController,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(labelText: 'Precio en dólares (\$)*', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _descController,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Descripción del producto', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _guardar,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 50),
              ),
              child: const Text('Guardar Producto'),
            ),
          ],
        ),
      ),
    );
  }
}

// P17: Pedidos de mi Tienda (Vendedor)
class PedidosTiendaScreen extends StatefulWidget {
  const PedidosTiendaScreen({super.key});

  @override
  State<PedidosTiendaScreen> createState() => _PedidosTiendaScreenState();
}

class _PedidosTiendaScreenState extends State<PedidosTiendaScreen> {
  List<Pedido> _pedidos = [];
  bool _cargando = true;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  void _cargar() async {
    setState(() => _cargando = true);
    try {
      final list = await PedidoService.getPedidosTienda();
      setState(() {
        _pedidos = list;
        _cargando = false;
      });
    } catch (_) {
      setState(() => _cargando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pedidos Recibidos')),
      body: RefreshIndicator(
        onRefresh: () async => _cargar(),
        child: _cargando
            ? const Center(child: CircularProgressIndicator())
            : _pedidos.isEmpty
                ? const EmptyStateWidget(icon: '📥', message: AppStrings.emptyPedidosTienda)
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _pedidos.length,
                    itemBuilder: (context, index) {
                      final p = _pedidos[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('${p.codigo} • Comprador: ${p.nombreComprador ?? "Estudiante"}',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                              const SizedBox(height: 4),
                              Text('Total: \$${p.total.toStringAsFixed(2)} • Pago: ${p.formaPago}'),
                              if (p.puntoEntrega != null) Text('📍 Entrega: ${p.puntoEntrega}'),
                              const SizedBox(height: 12),
                              if (p.esPendiente)
                                Row(
                                  children: [
                                    ElevatedButton(
                                      onPressed: () async {
                                        await PedidoService.marcarEntregado(p.id);
                                        _cargar();
                                      },
                                      style: ElevatedButton.styleFrom(backgroundColor: AppColors.success, foregroundColor: Colors.white),
                                      child: const Text('Entregado'),
                                    ),
                                    const SizedBox(width: 8),
                                    OutlinedButton(
                                      onPressed: () async {
                                        await PedidoService.cancelarPedidoVendedor(p.id);
                                        _cargar();
                                      },
                                      child: const Text('Cancelar'),
                                    ),
                                  ],
                                ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
