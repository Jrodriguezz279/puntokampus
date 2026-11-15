import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../theme/theme.dart';
import '../../constants/app_strings.dart';
import '../../models/pedido.dart';
import '../../models/tienda.dart';
import '../../providers/tienda_provider.dart';
import '../../providers/vitrina_provider.dart';
import '../../services/pedido_service.dart';
import '../../widgets/pk_components.dart';

// ══════════════════════════════════════════════════════════════════════════════
// MI TIENDA — P14 Crear/Editar Tienda / P15 Panel Mi Tienda / P16 Nuevo Producto / P17 Pedidos
// Cumple con docs/DISENO-VISUAL.md
// ══════════════════════════════════════════════════════════════════════════════

// ─── P15: Mi Tienda (Panel del Vendedor) ───────────────────────────────────────
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
        backgroundColor: PKColors.fondo,
        appBar: AppBar(title: const Text('Mi Tienda')),
        body: const Padding(
          padding: EdgeInsets.all(PKSpacing.base),
          child: Column(
            children: [
              PKEsqueleto(height: 120),
              SizedBox(height: PKSpacing.base),
              PKEsqueleto(height: 120),
            ],
          ),
        ),
      );
    }

    if (!tiendaProv.tieneTienda) {
      // P14 Invitación a crear tienda
      return Scaffold(
        backgroundColor: PKColors.fondo,
        appBar: AppBar(
          title: const Text('Mi Tienda'),
          actions: [
            IconButton(
              icon: const Icon(Icons.refresh),
              tooltip: 'Actualizar',
              onPressed: () => tiendaProv.cargarMiTienda(),
            ),
          ],
        ),
        body: RefreshIndicator(
          color: PKColors.primario,
          onRefresh: () => tiendaProv.cargarMiTienda(),
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: SizedBox(
              height: MediaQuery.of(context).size.height * 0.7,
              child: PKEstadoVacio(
                icono: Icons.storefront_outlined,
                titulo: 'Crea tu tienda en la UTB',
                subtitulo: '¿Vendes comida, snacks, accesorios o servicios en el campus? Llega a miles de estudiantes.',
                botonTexto: AppStrings.btnCrearTienda,
                onBoton: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const FormTiendaScreen()),
                  );
                },
              ),
            ),
          ),
        ),
      );
    }

    final t = tiendaProv.miTienda!;

    return Scaffold(
      backgroundColor: PKColors.fondo,
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
        backgroundColor: PKColors.primario,
        foregroundColor: PKColors.sobrePrimario,
        elevation: 2,
        icon: const Icon(Icons.add),
        label: const Text('Nuevo Producto', style: TextStyle(fontWeight: FontWeight.bold, fontFamily: 'DMSans')),
      ),
      body: RefreshIndicator(
        color: PKColors.primario,
        onRefresh: () => tiendaProv.cargarMiTienda(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PKSpacing.base),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Tarjeta Resumen
              PKTarjeta(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(t.nombre, style: PKTextStyles.tituloTarjeta),
                        ),
                        if (t.esPremium)
                          const PKInsigniaSaco10()
                        else
                          const PKInsigniaMatriculado(),
                      ],
                    ),
                    if (t.descripcion != null && t.descripcion!.isNotEmpty) ...[
                      const SizedBox(height: PKSpacing.sm),
                      Text(t.descripcion!, style: PKTextStyles.cuerpo),
                    ],
                    const SizedBox(height: PKSpacing.sm),
                    if (t.puntoEntrega != null)
                      Text('📍 Entrega: ${t.puntoEntrega!.nombre}', style: PKTextStyles.etiqueta),
                    if (t.horario != null)
                      Text('⏰ Horario: ${t.horario}', style: PKTextStyles.etiqueta),
                  ],
                ),
              ),

              const SizedBox(height: PKSpacing.base),

              // Botón Pedidos Recibidos
              PKTarjeta(
                padding: EdgeInsets.zero,
                child: ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(PKSpacing.sm),
                    decoration: const BoxDecoration(
                      color: PKColors.primarioSuave,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.inbox_outlined, color: PKColors.primario),
                  ),
                  title: Text('Pedidos de mi tienda', style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15)),
                  subtitle: Text('Gestionar pedidos entrantes y entregas', style: PKTextStyles.etiqueta),
                  trailing: const Icon(Icons.chevron_right, color: PKColors.secundario),
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const PedidosTiendaScreen()),
                    );
                  },
                ),
              ),

              const SizedBox(height: PKSpacing.lg),

              // Lista de Productos de la Tienda
              Text('Mis Productos (${t.productos.length})', style: PKTextStyles.tituloSeccion.copyWith(fontSize: 16)),
              const SizedBox(height: PKSpacing.sm),

              if (t.productos.isEmpty)
                const PKEstadoVacio(
                  icono: Icons.inventory_2_outlined,
                  titulo: 'Sin productos aún',
                  subtitulo: AppStrings.emptyMisProductos,
                )
              else
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: t.productos.length,
                  itemBuilder: (context, index) {
                    final prod = t.productos[index];
                    return PKTarjeta(
                      padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
                      child: Row(
                        children: [
                          Container(
                            width: 50,
                            height: 50,
                            decoration: BoxDecoration(
                              color: PKColors.neutroFondo,
                              borderRadius: PKRadii.imagenAll,
                              border: Border.all(color: PKColors.borde),
                            ),
                            child: const Icon(Icons.fastfood_outlined, color: PKColors.secundario),
                          ),
                          const SizedBox(width: PKSpacing.md),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(prod.nombre, style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15)),
                                const SizedBox(height: 2),
                                Text(
                                  '\$${prod.precio.toStringAsFixed(2)} • ${prod.disponible ? "Disponible" : "Agotado"}',
                                  style: PKTextStyles.etiqueta.copyWith(
                                    color: prod.disponible ? PKColors.primarioSuaveTexto : PKColors.secundario,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.delete_outline, color: PKColors.error),
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
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: PKColors.error,
                                        foregroundColor: PKColors.sobrePrimario,
                                      ),
                                      child: const Text('Eliminar'),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                        ],
                      ),
                    );
                  },
                ),

              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── P14: Formulario de Tienda (Crear o Editar) ─────────────────────────────────
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

  @override
  void dispose() {
    _nombreController.dispose();
    _descripcionController.dispose();
    _horarioController.dispose();
    _whatsappController.dispose();
    super.dispose();
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
      pkSnackError(context, tiendaProv.error ?? 'Error al guardar.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final vitrina = Provider.of<VitrinaProvider>(context);

    final selectedCatId = _cat1Id ?? (vitrina.categorias.isNotEmpty ? vitrina.categorias.first.id : null);
    final selectedPuntoId = _puntoId ?? (vitrina.puntosEntrega.isNotEmpty ? vitrina.puntosEntrega.first.id : null);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: Text(widget.tienda == null ? 'Crear mi tienda' : 'Editar tienda')),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: PKBotonPrimario(
          texto: widget.tienda == null ? AppStrings.btnCrearTienda : AppStrings.btnGuardarCambios,
          onPressed: _guardar,
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          children: [
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Información básica', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),
                  PKCampoEtiquetado(
                    etiqueta: 'Nombre de la tienda *',
                    hint: 'Ej. Snacks & Dulces UTB',
                    controller: _nombreController,
                  ),
                  const SizedBox(height: PKSpacing.base),
                  PKCampoEtiquetado(
                    etiqueta: 'Descripción *',
                    hint: '¿Qué vendes o ofreces?',
                    maxLines: 2,
                    controller: _descripcionController,
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Ubicación y categoría', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),
                  Text('Categoría principal *', style: PKTextStyles.etiqueta),
                  const SizedBox(height: PKSpacing.xs),
                  DropdownButtonFormField<int>(
                    value: vitrina.categorias.any((c) => c.id == selectedCatId) ? selectedCatId : null,
                    items: vitrina.categorias
                        .map((c) => DropdownMenuItem<int>(value: c.id, child: Text(c.nombre, style: PKTextStyles.cuerpo)))
                        .toList(),
                    onChanged: (val) => setState(() => _cat1Id = val),
                  ),
                  const SizedBox(height: PKSpacing.base),
                  Text('Punto de entrega preferido *', style: PKTextStyles.etiqueta),
                  const SizedBox(height: PKSpacing.xs),
                  if (vitrina.cargando && vitrina.puntosEntrega.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8),
                      child: Text('Cargando puntos de entrega...', style: TextStyle(color: PKColors.secundario)),
                    )
                  else
                    DropdownButtonFormField<int>(
                      value: vitrina.puntosEntrega.any((p) => p.id == selectedPuntoId) ? selectedPuntoId : null,
                      hint: const Text('Selecciona un punto de entrega'),
                      items: vitrina.puntosEntrega
                          .map((p) => DropdownMenuItem<int>(value: p.id, child: Text('📍 ${p.nombre}', style: PKTextStyles.cuerpo)))
                          .toList(),
                      onChanged: (val) => setState(() => _puntoId = val),
                    ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Contacto y horarios', style: PKTextStyles.tituloTarjeta),
                  const SizedBox(height: PKSpacing.base),
                  PKCampoEtiquetado(
                    etiqueta: 'Horario disponible *',
                    hint: 'Ej. Lun a Vie 09:00 - 14:00',
                    controller: _horarioController,
                  ),
                  const SizedBox(height: PKSpacing.base),
                  PKCampoEtiquetado(
                    etiqueta: 'WhatsApp para pedidos',
                    hint: 'Ej. 0987654321',
                    keyboardType: TextInputType.phone,
                    controller: _whatsappController,
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
}

// ─── P16: Formulario de Producto Nuevo ─────────────────────────────────────────
class FormProductoScreen extends StatefulWidget {
  const FormProductoScreen({super.key});

  @override
  State<FormProductoScreen> createState() => _FormProductoScreenState();
}

class _FormProductoScreenState extends State<FormProductoScreen> {
  final _nombreController = TextEditingController();
  final _descController = TextEditingController();
  final _precioController = TextEditingController();

  @override
  void dispose() {
    _nombreController.dispose();
    _descController.dispose();
    _precioController.dispose();
    super.dispose();
  }

  void _guardar() async {
    final precio = double.tryParse(_precioController.text.trim()) ?? 0.0;
    if (_nombreController.text.trim().isEmpty || precio <= 0) {
      pkSnackError(context, 'Ingresa un nombre y precio válido.');
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
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Nuevo Producto')),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: PKBotonPrimario(
          texto: 'Guardar Producto',
          onPressed: _guardar,
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: PKTarjeta(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Detalles del producto', style: PKTextStyles.tituloTarjeta),
              const SizedBox(height: PKSpacing.base),
              PKCampoEtiquetado(
                etiqueta: 'Nombre del producto *',
                hint: 'Ej. Brownie de chocolate',
                controller: _nombreController,
              ),
              const SizedBox(height: PKSpacing.base),
              PKCampoEtiquetado(
                etiqueta: 'Precio en dólares (\$)*',
                hint: '1.25',
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                controller: _precioController,
              ),
              const SizedBox(height: PKSpacing.base),
              PKCampoEtiquetado(
                etiqueta: 'Descripción (opcional)',
                hint: 'Ingredientes, tamaño o detalles',
                maxLines: 3,
                controller: _descController,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── P17: Pedidos de mi Tienda (Vendedor) ──────────────────────────────────────
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
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Pedidos Recibidos')),
      body: RefreshIndicator(
        color: PKColors.primario,
        onRefresh: () async => _cargar(),
        child: _cargando
            ? const Center(child: CircularProgressIndicator(color: PKColors.primario))
            : _pedidos.isEmpty
                ? const PKEstadoVacio(
                    icono: Icons.inbox_outlined,
                    titulo: 'Sin pedidos recibidos',
                    subtitulo: AppStrings.emptyPedidosTienda,
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(PKSpacing.base),
                    itemCount: _pedidos.length,
                    itemBuilder: (context, index) {
                      final p = _pedidos[index];
                      return Container(
                        margin: const EdgeInsets.only(bottom: PKSpacing.md),
                        child: PKTarjeta(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '#${p.codigo}',
                                    style: PKTextStyles.tituloTarjeta,
                                  ),
                                  PKPastillaEstado(estado: estadoFromString(p.estado)),
                                ],
                              ),
                              const SizedBox(height: PKSpacing.xs),
                              Text('Comprador: ${p.nombreComprador ?? "Estudiante"}', style: PKTextStyles.etiqueta),
                              Text('Total: \$${p.total.toStringAsFixed(2)} • Pago: ${p.formaPago}', style: PKTextStyles.cuerpo),
                              if (p.puntoEntrega != null) ...[
                                const SizedBox(height: 2),
                                Text('📍 Entrega: ${p.puntoEntrega}', style: PKTextStyles.etiqueta),
                              ],
                              if (p.esPendiente) ...[
                                const SizedBox(height: PKSpacing.base),
                                Row(
                                  children: [
                                    Expanded(
                                      child: ElevatedButton(
                                        onPressed: () async {
                                          await PedidoService.marcarEntregado(p.id);
                                          _cargar();
                                        },
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: PKColors.okTexto,
                                          foregroundColor: PKColors.sobrePrimario,
                                        ),
                                        child: const Text('Marcar Entregado'),
                                      ),
                                    ),
                                    const SizedBox(width: PKSpacing.sm),
                                    OutlinedButton(
                                      onPressed: () async {
                                        await PedidoService.cancelarPedidoVendedor(p.id);
                                        _cargar();
                                      },
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: PKColors.error,
                                        side: const BorderSide(color: PKColors.error),
                                      ),
                                      child: const Text('Cancelar'),
                                    ),
                                  ],
                                ),
                              ],
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
