import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../theme/theme.dart';
import '../../constants/app_strings.dart';
import '../../models/pedido.dart';
import '../../models/tienda.dart';
import '../../providers/tienda_provider.dart';
import '../../providers/vitrina_provider.dart';
import '../../services/pedido_service.dart';
import '../../services/tienda_service.dart';
import '../../widgets/pk_components.dart';
import 'premium_screen.dart';

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

              const SizedBox(height: PKSpacing.base),

              // P18: Tarjeta Plan Premium (Sacó 10)
              PKTarjeta(
                padding: EdgeInsets.zero,
                child: ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(PKSpacing.sm),
                    decoration: BoxDecoration(
                      color: t.esPremium ? PKColors.okFondo : PKColors.primarioSuave,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      Icons.star_rounded,
                      color: t.esPremium ? PKColors.okTexto : PKColors.primario,
                    ),
                  ),
                  title: Text(
                    t.esPremium ? 'Plan Premium Activo (Sacó 10)' : 'Mejorar a Plan Premium',
                    style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15),
                  ),
                  subtitle: Text(
                    t.esPremium ? 'Tu tienda cuenta con insignia y portada' : 'Insignia dorada y máxima visibilidad',
                    style: PKTextStyles.etiqueta,
                  ),
                  trailing: const Icon(Icons.chevron_right, color: PKColors.secundario),
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const PremiumScreen()),
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
  Uint8List? _logoBytes;
  String? _logoNombre;
  bool _guardando = false;

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

    setState(() => _guardando = true);
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
      if (_logoBytes != null && _logoNombre != null) {
        try {
          await TiendaService.subirLogo(bytes: _logoBytes!, fileName: _logoNombre!);
          await tiendaProv.cargarMiTienda();
        } catch (_) {}
      }
      if (!mounted) return;
      setState(() => _guardando = false);
      Navigator.pop(context);
    } else {
      setState(() => _guardando = false);
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
                  Center(
                    child: PKSelectorImagen(
                      etiqueta: 'Logo de la tienda (opcional)',
                      urlActual: widget.tienda?.logoUrl,
                      bytesIniciales: _logoBytes,
                      nombreArchivoInicial: _logoNombre,
                      altura: 110,
                      circular: true,
                      onImagenSeleccionada: (bytes, nombre) {
                        setState(() {
                          _logoBytes = bytes;
                          _logoNombre = nombre;
                        });
                      },
                    ),
                  ),
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
  Uint8List? _fotoBytes;
  String? _fotoNombre;
  bool _guardando = false;

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

    setState(() => _guardando = true);
    try {
      final prod = await TiendaService.crearProducto(
        nombre: _nombreController.text.trim(),
        descripcion: _descController.text.trim(),
        precio: precio,
      );

      if (_fotoBytes != null && _fotoNombre != null) {
        try {
          await TiendaService.subirFotoProducto(prod.id, bytes: _fotoBytes!, fileName: _fotoNombre!);
        } catch (_) {}
      }

      final tiendaProv = Provider.of<TiendaProvider>(context, listen: false);
      await tiendaProv.cargarMiTienda();

      if (!mounted) return;
      setState(() => _guardando = false);
      Navigator.pop(context);
    } catch (e) {
      if (!mounted) return;
      setState(() => _guardando = false);
      pkSnackError(context, e.toString());
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
          cargando: _guardando,
          onPressed: _guardando ? null : _guardar,
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
              PKSelectorImagen(
                etiqueta: 'Foto del producto (opcional)',
                bytesIniciales: _fotoBytes,
                nombreArchivoInicial: _fotoNombre,
                altura: 140,
                onImagenSeleccionada: (bytes, nombre) {
                  setState(() {
                    _fotoBytes = bytes;
                    _fotoNombre = nombre;
                  });
                },
              ),
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
  // Controladores de texto: uno por código de pedido (para el campo de ingreso del código de entrega)
  final Map<String, TextEditingController> _codigoControllers = {};
  final Map<String, bool> _confirmando = {};

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  @override
  void dispose() {
    for (final c in _codigoControllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  void _cargar() async {
    setState(() => _cargando = true);
    try {
      final list = await PedidoService.getPedidosTienda();
      // Crear controladores para pedidos pendientes nuevos
      for (final p in list) {
        if (p.esPendiente && !_codigoControllers.containsKey(p.codigo)) {
          _codigoControllers[p.codigo] = TextEditingController();
        }
      }
      setState(() {
        _pedidos = list;
        _cargando = false;
      });
    } catch (_) {
      setState(() => _cargando = false);
    }
  }

  Future<void> _confirmarEntrega(Pedido p) async {
    final controller = _codigoControllers[p.codigo];
    final codigo = controller?.text.trim() ?? '';
    if (codigo.length != 4) {
      pkSnackError(context, 'El código debe tener exactamente 4 dígitos.');
      return;
    }
    setState(() => _confirmando[p.codigo] = true);
    try {
      await PedidoService.confirmarEntrega(p.codigo, codigo);
      if (!mounted) return;
      pkSnackOk(context, '¡Pedido marcado como entregado!');
      _cargar();
    } catch (e) {
      if (!mounted) return;
      pkSnackError(context, e.toString());
    } finally {
      if (mounted) setState(() => _confirmando[p.codigo] = false);
    }
  }

  Future<void> _cancelarPedido(Pedido p) async {
    try {
      await PedidoService.cancelarPedidoVendedor(p.codigo);
      if (!mounted) return;
      pkSnackOk(context, 'Pedido cancelado.');
      _cargar();
    } catch (e) {
      if (!mounted) return;
      pkSnackError(context, e.toString());
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
                      final controller = _codigoControllers[p.codigo];
                      final confirmando = _confirmando[p.codigo] ?? false;
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
                              if (p.esPendiente && controller != null) ...[
                                const SizedBox(height: PKSpacing.base),
                                // R17: Campo para ingresar código de confirmación
                                Text(
                                  'Confirmar entrega',
                                  style: PKTextStyles.etiqueta.copyWith(fontWeight: FontWeight.w700),
                                ),
                                const SizedBox(height: PKSpacing.xs),
                                Text(
                                  'Pide el código de 4 dígitos al comprador e ingrésalo aquí.',
                                  style: PKTextStyles.etiqueta,
                                ),
                                const SizedBox(height: PKSpacing.sm),
                                Row(
                                  children: [
                                    Expanded(
                                      child: TextField(
                                        controller: controller,
                                        keyboardType: TextInputType.number,
                                        maxLength: 4,
                                        textAlign: TextAlign.center,
                                        style: PKTextStyles.tituloPantalla.copyWith(
                                          fontSize: 24,
                                          letterSpacing: 6,
                                        ),
                                        decoration: InputDecoration(
                                          hintText: '0000',
                                          counterText: '',
                                          contentPadding: const EdgeInsets.symmetric(
                                            vertical: PKSpacing.sm,
                                            horizontal: PKSpacing.base,
                                          ),
                                          border: OutlineInputBorder(
                                            borderRadius: PKRadii.botonAll,
                                            borderSide: const BorderSide(color: PKColors.borde),
                                          ),
                                          focusedBorder: OutlineInputBorder(
                                            borderRadius: PKRadii.botonAll,
                                            borderSide: const BorderSide(color: PKColors.primario, width: 2),
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: PKSpacing.sm),
                                    ElevatedButton(
                                      onPressed: confirmando ? null : () => _confirmarEntrega(p),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: PKColors.okTexto,
                                        foregroundColor: PKColors.sobrePrimario,
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: PKSpacing.base,
                                          vertical: PKSpacing.md,
                                        ),
                                      ),
                                      child: confirmando
                                          ? const SizedBox(
                                              width: 20,
                                              height: 20,
                                              child: CircularProgressIndicator(
                                                strokeWidth: 2,
                                                color: PKColors.sobrePrimario,
                                              ),
                                            )
                                          : const Text('Confirmar\nentrega', textAlign: TextAlign.center),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: PKSpacing.sm),
                                OutlinedButton(
                                  onPressed: () => _cancelarPedido(p),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: PKColors.error,
                                    side: const BorderSide(color: PKColors.error),
                                  ),
                                  child: const Text('Cancelar pedido'),
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
