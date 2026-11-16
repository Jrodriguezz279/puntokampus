import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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

  Future<void> _abrirFormProducto([Producto? prod]) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => FormProductoScreen(productoExistente: prod),
      ),
    );
    if (mounted) {
      Provider.of<TiendaProvider>(context, listen: false).cargarMiTienda();
    }
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
        onPressed: () => _abrirFormProducto(),
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
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 54,
                          height: 54,
                          decoration: BoxDecoration(
                            color: PKColors.primarioSuave,
                            borderRadius: PKRadii.imagenAll,
                            border: Border.all(color: PKColors.borde),
                          ),
                          clipBehavior: Clip.antiAlias,
                          child: (t.logoUrl != null && t.logoUrl!.isNotEmpty)
                              ? Image.network(
                                  t.logoUrl!,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Center(
                                    child: Text(
                                      t.nombre.isNotEmpty ? t.nombre[0].toUpperCase() : 'T',
                                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: PKColors.primario, fontFamily: 'DMSans'),
                                    ),
                                  ),
                                )
                              : Center(
                                  child: Text(
                                    t.nombre.isNotEmpty ? t.nombre[0].toUpperCase() : 'T',
                                    style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: PKColors.primario, fontFamily: 'DMSans'),
                                  ),
                                ),
                        ),
                        const SizedBox(width: PKSpacing.md),
                        Expanded(
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
                                const SizedBox(height: PKSpacing.xs),
                                Text(t.descripcion!, style: PKTextStyles.cuerpo),
                              ],
                            ],
                          ),
                        ),
                      ],
                    ),
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
                    return Container(
                      margin: const EdgeInsets.only(bottom: PKSpacing.sm),
                      child: Material(
                        color: Colors.transparent,
                        borderRadius: PKRadii.tarjetaAll,
                        child: InkWell(
                          borderRadius: PKRadii.tarjetaAll,
                          onTap: () => _abrirFormProducto(prod),
                          child: PKTarjeta(
                            padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
                            child: Row(
                              children: [
                                Container(
                                  width: 54,
                                  height: 54,
                                  decoration: BoxDecoration(
                                    color: PKColors.neutroFondo,
                                    borderRadius: PKRadii.imagenAll,
                                    border: Border.all(color: PKColors.borde),
                                  ),
                                  clipBehavior: Clip.antiAlias,
                                  child: (prod.primeraFotoUrl != null && prod.primeraFotoUrl!.isNotEmpty)
                                      ? Image.network(
                                          prod.primeraFotoUrl!,
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, __, ___) => const Icon(Icons.fastfood_outlined, color: PKColors.secundario),
                                        )
                                      : const Icon(Icons.fastfood_outlined, color: PKColors.secundario),
                                ),
                                const SizedBox(width: PKSpacing.md),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(prod.nombre, style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15)),
                                      const SizedBox(height: 3),
                                      Row(
                                        children: [
                                          Text(
                                            '\$${prod.precio.toStringAsFixed(2)}',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 14,
                                              color: PKColors.primario,
                                              fontFamily: 'DMSans',
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: prod.disponible ? PKColors.okFondo : PKColors.neutroFondo,
                                              borderRadius: BorderRadius.circular(4),
                                            ),
                                            child: Text(
                                              prod.disponible ? "Disponible" : "Agotado",
                                              style: TextStyle(
                                                fontSize: 11,
                                                fontWeight: FontWeight.w600,
                                                color: prod.disponible ? PKColors.okTexto : PKColors.secundario,
                                                fontFamily: 'DMSans',
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.edit_outlined, color: PKColors.primario),
                                  tooltip: 'Editar producto',
                                  onPressed: () => _abrirFormProducto(prod),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.delete_outline, color: PKColors.error),
                                  tooltip: 'Eliminar producto',
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
                          ),
                        ),
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

// ─── P16: Formulario de Producto (Crear o Editar) ──────────────────────────────
class FormProductoScreen extends StatefulWidget {
  /// Si se pasa [productoExistente], la pantalla funciona en modo edición.
  final Producto? productoExistente;
  const FormProductoScreen({super.key, this.productoExistente});

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
  bool _disponible = true;

  bool get _esEdicion => widget.productoExistente != null;

  @override
  void initState() {
    super.initState();
    if (_esEdicion) {
      final p = widget.productoExistente!;
      _nombreController.text = p.nombre;
      _descController.text = p.descripcion ?? '';
      _precioController.text = p.precio.toStringAsFixed(2);
      _disponible = p.disponible;
    }
  }

  @override
  void dispose() {
    _nombreController.dispose();
    _descController.dispose();
    _precioController.dispose();
    super.dispose();
  }

  void _guardar() async {
    final precio = double.tryParse(_precioController.text.trim().replaceAll(',', '.')) ?? 0.0;
    final nombre = _nombreController.text.trim();

    if (nombre.isEmpty) {
      pkSnackError(context, 'Ingresa el nombre del producto.');
      return;
    }
    if (nombre.length < 3) {
      pkSnackError(context, 'El nombre debe tener al menos 3 caracteres.');
      return;
    }
    if (precio <= 0) {
      pkSnackError(context, 'Ingresa un precio mayor a \$0.00.');
      return;
    }

    setState(() => _guardando = true);
    final tiendaProv = Provider.of<TiendaProvider>(context, listen: false);

    try {
      if (_esEdicion) {
        // Modo edición: actualizar datos y, si hay nueva foto, reemplazarla
        final prod = await TiendaService.actualizarProducto(
          widget.productoExistente!.id,
          nombre: nombre,
          descripcion: _descController.text.trim(),
          precio: precio,
          disponible: _disponible,
        );

        if (_fotoBytes != null && _fotoNombre != null) {
          try {
            await TiendaService.subirFotoProducto(
              prod.id,
              bytes: _fotoBytes!,
              fileName: _fotoNombre!,
              reemplazar: true,
            );
          } catch (e) {
            debugPrint('Error al subir foto: $e');
          }
        }
      } else {
        // Modo creación
        final prod = await TiendaService.crearProducto(
          nombre: nombre,
          descripcion: _descController.text.trim(),
          precio: precio,
          disponible: _disponible,
        );

        if (_fotoBytes != null && _fotoNombre != null) {
          try {
            await TiendaService.subirFotoProducto(
              prod.id,
              bytes: _fotoBytes!,
              fileName: _fotoNombre!,
            );
          } catch (e) {
            debugPrint('Error al subir foto: $e');
          }
        }
      }

      await tiendaProv.cargarMiTienda();

      if (!mounted) return;
      setState(() => _guardando = false);
      Navigator.pop(context, true);
      pkSnackOk(context, _esEdicion ? 'Producto actualizado correctamente.' : 'Producto publicado con éxito.');
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
      appBar: AppBar(title: Text(_esEdicion ? 'Editar Producto' : 'Nuevo Producto')),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: PKBotonPrimario(
          texto: _esEdicion ? 'Guardar Cambios' : 'Publicar Producto',
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
                etiqueta: _esEdicion
                    ? 'Reemplazar foto (opcional)'
                    : 'Foto del producto (opcional)',
                urlActual: _esEdicion ? widget.productoExistente!.primeraFotoUrl : null,
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
              const SizedBox(height: PKSpacing.base),
              Container(
                decoration: BoxDecoration(
                  color: PKColors.fondo,
                  borderRadius: PKRadii.botonAll,
                  border: Border.all(color: PKColors.borde),
                ),
                child: SwitchListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: PKSpacing.md, vertical: 2),
                  title: const Text(
                    'Disponible para venta',
                    style: TextStyle(fontWeight: FontWeight.w600, fontFamily: 'DMSans'),
                  ),
                  subtitle: Text(
                    _disponible
                        ? 'Los estudiantes podrán ver y ordenar este producto'
                        : 'Marcado temporalmente como agotado',
                    style: PKTextStyles.etiqueta,
                  ),
                  value: _disponible,
                  activeColor: PKColors.primario,
                  onChanged: (val) => setState(() => _disponible = val),
                ),
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
      for (final p in list) {
        _codigoControllers.putIfAbsent(p.codigo, () => TextEditingController());
      }
      setState(() {
        _pedidos = list;
        _cargando = false;
      });
    } catch (_) {
      setState(() => _cargando = false);
    }
  }

  Future<void> _confirmarEntrega(Pedido p, [String? codigoManual]) async {
    final controller = _codigoControllers.putIfAbsent(p.codigo, () => TextEditingController());
    final codigo = (codigoManual ?? controller.text).trim();

    if (codigo.length != 4 || !RegExp(r'^\d{4}$').hasMatch(codigo)) {
      pkSnackError(context, 'El código debe tener exactamente 4 dígitos numéricos.');
      return;
    }

    setState(() => _confirmando[p.codigo] = true);
    try {
      await PedidoService.confirmarEntrega(p.codigo, codigo);
      controller.clear();
      if (!mounted) return;
      pkSnackOk(context, '¡Pedido #${p.codigo} marcado como entregado!');
      _cargar();
    } catch (e) {
      if (!mounted) return;
      pkSnackError(context, e.toString());
    } finally {
      if (mounted) setState(() => _confirmando[p.codigo] = false);
    }
  }

  void _mostrarDialogoCodigo(Pedido p) {
    final dialogController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.pin_outlined, color: PKColors.primario),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'Confirmar #${p.codigo}',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17, fontFamily: 'DMSans'),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Ingresa el código de 4 dígitos dado por el comprador (${p.nombreComprador ?? "Estudiante"}):',
              style: PKTextStyles.cuerpo,
            ),
            const SizedBox(height: PKSpacing.base),
            TextField(
              controller: dialogController,
              autofocus: true,
              keyboardType: TextInputType.number,
              inputFormatters: [
                FilteringTextInputFormatter.digitsOnly,
                LengthLimitingTextInputFormatter(4),
              ],
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 28,
                letterSpacing: 8,
                fontWeight: FontWeight.w800,
                color: Color(0xFFA43D00),
                fontFamily: 'DMSans',
              ),
              decoration: InputDecoration(
                hintText: '0000',
                counterText: '',
                contentPadding: const EdgeInsets.symmetric(vertical: PKSpacing.md, horizontal: PKSpacing.base),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: PKColors.borde)),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: PKColors.primario, width: 2)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancelar', style: TextStyle(color: PKColors.secundario)),
          ),
          ElevatedButton(
            onPressed: () {
              final code = dialogController.text.trim();
              if (code.length != 4) {
                pkSnackError(context, 'Ingresa los 4 dígitos numéricos.');
                return;
              }
              Navigator.pop(ctx);
              _confirmarEntrega(p, code);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF16A34A),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            ),
            child: const Text('✓ Confirmar entrega', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Future<void> _cancelarPedido(Pedido p) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('¿Cancelar pedido?'),
        content: Text('¿Deseas cancelar el pedido #${p.codigo}? El comprador verá que fue cancelado.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Volver')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: PKColors.error,
              foregroundColor: PKColors.sobrePrimario,
            ),
            child: const Text('Cancelar pedido'),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

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
                      final controller = _codigoControllers.putIfAbsent(p.codigo, () => TextEditingController());
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
                              Text('Total: \$${p.total.toStringAsFixed(2)} • Pago: ${p.formaPago}', style: PKTextStyles.cuerpo.copyWith(fontWeight: FontWeight.w600)),
                              if (p.puntoEntrega != null) ...[
                                const SizedBox(height: 2),
                                Text('📍 Entrega: ${p.puntoEntrega}', style: PKTextStyles.etiqueta),
                              ],
                              if (p.items.isNotEmpty) ...[
                                const SizedBox(height: PKSpacing.xs),
                                ...p.items.map((item) => Text(
                                  '• ${item.cantidad}x ${item.nombreProducto} (\$${item.subtotal.toStringAsFixed(2)})',
                                  style: PKTextStyles.etiqueta.copyWith(color: PKColors.secundario),
                                )),
                              ],
                              if (p.esPendiente) ...[
                                const SizedBox(height: PKSpacing.base),
                                if (p.codigoBloqueado)
                                  Container(
                                    padding: const EdgeInsets.all(PKSpacing.sm),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFFDAD6),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: const Color(0xFFBA1A1A)),
                                    ),
                                    child: const Row(
                                      children: [
                                        Icon(Icons.lock_outline, color: Color(0xFFBA1A1A), size: 18),
                                        SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            'Código bloqueado tras 5 intentos fallidos. Contacta al administrador.',
                                            style: TextStyle(color: Color(0xFF93000A), fontSize: 12, fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                      ],
                                    ),
                                  )
                                else
                                  Container(
                                    padding: const EdgeInsets.all(PKSpacing.md),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFFF8F5),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: const Color(0xFFFED7AA), width: 1.5),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            const Text('🔑 ', style: TextStyle(fontSize: 16)),
                                            Text(
                                              'Confirmar entrega con código',
                                              style: PKTextStyles.etiqueta.copyWith(
                                                fontWeight: FontWeight.w800,
                                                color: const Color(0xFFA43D00),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          'Pide el código de 4 dígitos al comprador e ingrésalo aquí:',
                                          style: PKTextStyles.etiqueta.copyWith(color: PKColors.secundario),
                                        ),
                                        const SizedBox(height: PKSpacing.sm),
                                        Row(
                                          children: [
                                            Expanded(
                                              child: TextField(
                                                controller: controller,
                                                keyboardType: TextInputType.number,
                                                inputFormatters: [
                                                  FilteringTextInputFormatter.digitsOnly,
                                                  LengthLimitingTextInputFormatter(4),
                                                ],
                                                maxLength: 4,
                                                textAlign: TextAlign.center,
                                                textInputAction: TextInputAction.done,
                                                onSubmitted: (_) => _confirmarEntrega(p),
                                                style: const TextStyle(
                                                  fontSize: 22,
                                                  letterSpacing: 6,
                                                  fontWeight: FontWeight.w800,
                                                  color: Color(0xFFA43D00),
                                                  fontFamily: 'DMSans',
                                                ),
                                                decoration: InputDecoration(
                                                  hintText: '0000',
                                                  hintStyle: const TextStyle(letterSpacing: 6, color: Color(0xFFD9D2CC)),
                                                  counterText: '',
                                                  fillColor: Colors.white,
                                                  filled: true,
                                                  contentPadding: const EdgeInsets.symmetric(
                                                    vertical: PKSpacing.sm,
                                                    horizontal: PKSpacing.base,
                                                  ),
                                                  border: OutlineInputBorder(
                                                    borderRadius: BorderRadius.circular(10),
                                                    borderSide: const BorderSide(color: Color(0xFFD9D2CC)),
                                                  ),
                                                  focusedBorder: OutlineInputBorder(
                                                    borderRadius: BorderRadius.circular(10),
                                                    borderSide: const BorderSide(color: Color(0xFFA43D00), width: 2),
                                                  ),
                                                ),
                                              ),
                                            ),
                                            const SizedBox(width: PKSpacing.sm),
                                            ElevatedButton(
                                              onPressed: confirmando ? null : () => _confirmarEntrega(p),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF16A34A),
                                                foregroundColor: Colors.white,
                                                elevation: 1,
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
                                                        color: Colors.white,
                                                      ),
                                                    )
                                                  : const Text(
                                                      '✓ Confirmar\nentrega',
                                                      textAlign: TextAlign.center,
                                                      style: TextStyle(fontWeight: FontWeight.bold),
                                                    ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 6),
                                        Center(
                                          child: TextButton.icon(
                                            onPressed: () => _mostrarDialogoCodigo(p),
                                            icon: const Icon(Icons.open_in_full, size: 14, color: Color(0xFFA43D00)),
                                            label: const Text(
                                              '¿Problemas con el teclado? Toca aquí',
                                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFFA43D00)),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                const SizedBox(height: PKSpacing.sm),
                                OutlinedButton.icon(
                                  icon: const Icon(Icons.close, size: 16),
                                  label: const Text('Cancelar pedido'),
                                  onPressed: () => _cancelarPedido(p),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: PKColors.error,
                                    side: const BorderSide(color: PKColors.error),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                  ),
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
