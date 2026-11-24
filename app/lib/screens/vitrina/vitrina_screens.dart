import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../theme/theme.dart';
import '../../models/tienda.dart';
import '../../models/producto.dart';
import '../../providers/vitrina_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/vitrina_service.dart';
import '../../widgets/item_cards.dart';
import '../../widgets/pk_components.dart';
import '../auth/auth_screens.dart';
import '../carrito/carrito_screens.dart';
import 'reportar_tienda_screen.dart';

// ══════════════════════════════════════════════════════════════════════════════
// VITRINA & CATÁLOGO — P05 Vitrina / P07 Detalle Tienda / P08 Detalle Producto
// Cumple con docs/DISENO-VISUAL.md
// ══════════════════════════════════════════════════════════════════════════════

// ─── P05: Vitrina Principal ───────────────────────────────────────────────────
class VitrinaScreen extends StatefulWidget {
  const VitrinaScreen({super.key});

  @override
  State<VitrinaScreen> createState() => _VitrinaScreenState();
}

class _VitrinaScreenState extends State<VitrinaScreen> {
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<VitrinaProvider>(context, listen: false).cargarInicial();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final vitrina = Provider.of<VitrinaProvider>(context);
    final auth = Provider.of<AuthProvider>(context);
    final cart = Provider.of<CartProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const PKLogo(fontSize: 20),
        backgroundColor: PKColors.tarjeta,
        elevation: 0,
        actions: [
          // Botón Carrito con badge
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.shopping_bag_outlined, color: PKColors.primario),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CarritoScreen()),
                  );
                },
              ),
              if (cart.itemCount > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: PKColors.primario,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                    child: Text(
                      '${cart.itemCount}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: PKColors.sobrePrimario,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        fontFamily: 'DMSans',
                      ),
                    ),
                  ),
                ),
            ],
          ),
          if (!auth.tieneSesion)
            TextButton(
              onPressed: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const BienvenidaScreen()));
              },
              child: const Text('Entrar'),
            ),
          const SizedBox(width: PKSpacing.sm),
        ],
      ),
      body: RefreshIndicator(
        color: PKColors.primario,
        onRefresh: () => vitrina.cargarInicial(),
        child: CustomScrollView(
          slivers: [
            // Barra de búsqueda
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(PKSpacing.base),
                child: TextField(
                  controller: _searchController,
                  style: PKTextStyles.cuerpo,
                  decoration: InputDecoration(
                    hintText: 'Buscar comida, postres, impresiones...',
                    prefixIcon: const Icon(Icons.search, color: PKColors.secundario),
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: PKSpacing.base),
                  ),
                  onSubmitted: (val) => vitrina.buscar(val),
                ),
              ),
            ),

            // Fila de Categorías deslizables
            SliverToBoxAdapter(
              child: SizedBox(
                height: 40,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base),
                  itemCount: vitrina.categorias.length + 1,
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      final isSelected = vitrina.categoriaSeleccionadaId == null;
                      return Padding(
                        padding: const EdgeInsets.only(right: PKSpacing.sm),
                        child: ChoiceChip(
                          label: const Text('Todas'),
                          selected: isSelected,
                          onSelected: (_) => vitrina.filtrarPorCategoria(null),
                        ),
                      );
                    }
                    final cat = vitrina.categorias[index - 1];
                    final isSelected = vitrina.categoriaSeleccionadaId == cat.id;
                    return Padding(
                      padding: const EdgeInsets.only(right: PKSpacing.sm),
                      child: ChoiceChip(
                        label: Text(cat.nombre),
                        selected: isSelected,
                        onSelected: (_) => vitrina.filtrarPorCategoria(isSelected ? null : cat.id),
                      ),
                    );
                  },
                ),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: PKSpacing.base)),

            // --- Tramo 2: Carrusel de Destacados ---
            if (!vitrina.cargando && vitrina.tiendas.any((t) => t.esPremium && t.estaActiva && t.recibiendoPedidos && t.estaAbierto))
              SliverToBoxAdapter(
                child: _CarruselDestacados(tiendas: vitrina.tiendas),
              ),

            // Título de la sección
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
                child: Text(
                  'Tiendas en el campus UTB (${vitrina.tiendas.length})',
                  style: PKTextStyles.tituloSeccion.copyWith(fontSize: 16),
                ),
              ),
            ),

            // Lista de tiendas
            if (vitrina.cargando)
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (_, __) => const PKEsqueleto(height: 90),
                    childCount: 4,
                  ),
                ),
              )
            else if (vitrina.error != null)
              SliverFillRemaining(
                child: PKEstadoError(
                  mensaje: vitrina.error!,
                  onReintentar: () => vitrina.cargarInicial(),
                ),
              )
            else if (vitrina.tiendas.isEmpty)
              const SliverFillRemaining(
                child: PKEstadoVacio(
                  icono: Icons.storefront_outlined,
                  titulo: 'No encontramos tiendas',
                  subtitulo: 'Prueba buscando con otro término o revisa otra categoría.',
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final tienda = vitrina.tiendas[index];
                      return StoreCard(
                        tienda: tienda,
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => TiendaScreen(slug: tienda.slug)),
                          );
                        },
                      );
                    },
                    childCount: vitrina.tiendas.length,
                  ),
                ),
              ),

            const SliverToBoxAdapter(child: SizedBox(height: 80)),
          ],
        ),
      ),
    );
  }
}

// ─── Carrusel Destacados (Tramo 2) ──────────────────────────────────────────
class _CarruselDestacados extends StatelessWidget {
  final List<Tienda> tiendas;
  const _CarruselDestacados({required this.tiendas});

  @override
  Widget build(BuildContext context) {
    // Carrusel de la vitrina: tiendas Premium activas, recibiendo pedidos y abiertas en ese momento
    final destacadas = tiendas.where((t) => t.esPremium && t.estaActiva && t.recibiendoPedidos && t.estaAbierto).toList();
    if (destacadas.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFCC4E01),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  '⭐ DESTACADOS',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                    letterSpacing: 0.5,
                    fontFamily: 'DMSans',
                  ),
                ),
              ),
            ],
          ),
        ),
        SizedBox(
          height: 130,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base),
            itemCount: destacadas.length,
            itemBuilder: (context, index) {
              final t = destacadas[index];
              return GestureDetector(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => TiendaScreen(slug: t.slug)),
                ),
                child: Container(
                  width: 160,
                  margin: const EdgeInsets.only(right: PKSpacing.md, bottom: PKSpacing.sm),
                  decoration: BoxDecoration(
                    color: PKColors.tarjeta,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: t.esPremium ? const Color(0xFFCC4E01) : PKColors.borde,
                      width: t.esPremium ? 1.5 : 1,
                    ),
                    boxShadow: PKShadows.tarjeta,
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(PKSpacing.md),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                color: PKColors.primarioSuave,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: PKColors.borde),
                              ),
                              clipBehavior: Clip.antiAlias,
                              child: (t.logoUrl != null && t.logoUrl!.isNotEmpty)
                                  ? Image.network(t.logoUrl!, fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => _inicial(t.nombre))
                                  : _inicial(t.nombre),
                            ),
                            const SizedBox(width: 6),
                            if (t.esPremium)
                              const Icon(Icons.star_rounded, color: Color(0xFFCC4E01), size: 16),
                          ],
                        ),
                        const SizedBox(height: PKSpacing.sm),
                        Text(
                          t.nombre,
                          style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 13),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        if (!t.puedeRecibirPedidos)
                          const Text('⏸ Pausada',
                            style: TextStyle(fontSize: 11, color: Color(0xFFBA1A1A), fontFamily: 'DMSans'),
                          )
                        else if (t.estrellasPromedio != null && t.estrellasTotal >= 3)
                          Row(
                            children: [
                              const Icon(Icons.star_rounded, size: 11, color: Color(0xFFF59E0B)),
                              const SizedBox(width: 2),
                              Text(
                                '${t.estrellasPromedio!.toStringAsFixed(1)} (${t.estrellasTotal})',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF92400E),
                                  fontFamily: 'DMSans',
                                ),
                              ),
                            ],
                          )
                        else if (t.categoria1 != null)
                          Text(t.categoria1!.nombre,
                            style: PKTextStyles.etiqueta,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: PKSpacing.sm),
      ],
    );
  }

  Widget _inicial(String nombre) => Center(
    child: Text(
      nombre.isNotEmpty ? nombre[0].toUpperCase() : 'T',
      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: PKColors.primario, fontFamily: 'DMSans'),
    ),
  );
}

// ─── P07: Detalle de Tienda y Catálogo ─────────────────────────────────────────
class TiendaScreen extends StatefulWidget {
  final String slug;
  const TiendaScreen({super.key, required this.slug});

  @override
  State<TiendaScreen> createState() => _TiendaScreenState();
}

class _TiendaScreenState extends State<TiendaScreen> {
  Tienda? _tienda;
  bool _cargando = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  void _cargar() async {
    setState(() {
      _cargando = true;
      _error = null;
    });

    try {
      final t = await VitrinaService.getTiendaDetalle(widget.slug);
      setState(() {
        _tienda = t;
        _cargando = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _cargando = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);

    if (_cargando) {
      return Scaffold(
        backgroundColor: PKColors.fondo,
        appBar: AppBar(title: const Text('Cargando tienda...')),
        body: const Padding(
          padding: EdgeInsets.all(PKSpacing.base),
          child: Column(
            children: [
              PKEsqueleto(height: 120),
              SizedBox(height: PKSpacing.base),
              PKEsqueleto(height: 80),
              PKEsqueleto(height: 80),
            ],
          ),
        ),
      );
    }

    if (_error != null || _tienda == null) {
      return Scaffold(
        backgroundColor: PKColors.fondo,
        appBar: AppBar(title: const Text('Error')),
        body: PKEstadoError(mensaje: _error ?? 'Tienda no disponible.', onReintentar: _cargar),
      );
    }

    final t = _tienda!;

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: Text(t.nombre),
        actions: [
          IconButton(
            icon: const Icon(Icons.flag_outlined, color: PKColors.secundario),
            tooltip: 'Reportar tienda',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => ReportarTiendaScreen(tienda: t)),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.shopping_bag_outlined),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CarritoScreen())),
          ),
        ],
      ),
      bottomNavigationBar: cart.itemCount > 0 && cart.tiendaId == t.id
          ? Container(
              padding: const EdgeInsets.all(PKSpacing.base),
              decoration: const BoxDecoration(
                color: PKColors.tarjeta,
                border: Border(top: BorderSide(color: PKColors.borde)),
                boxShadow: PKShadows.tarjeta,
              ),
              child: PKBotonPrimario(
                texto: 'Ver carrito (${cart.itemCount}) · \$${cart.total.toStringAsFixed(2)}',
                icono: Icons.shopping_bag_outlined,
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CarritoScreen())),
              ),
            )
          : null,
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Cabecera de la Tienda
            PKTarjeta(
              padding: const EdgeInsets.all(PKSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 70,
                        height: 70,
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
                                    style: const TextStyle(
                                      fontSize: 28,
                                      fontWeight: FontWeight.bold,
                                      color: PKColors.primario,
                                      fontFamily: 'DMSans',
                                    ),
                                  ),
                                ),
                              )
                            : Center(
                                child: Text(
                                  t.nombre.isNotEmpty ? t.nombre[0].toUpperCase() : 'T',
                                  style: const TextStyle(
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                    color: PKColors.primario,
                                    fontFamily: 'DMSans',
                                  ),
                                ),
                              ),
                      ),
                      const SizedBox(width: PKSpacing.base),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(t.nombre, style: PKTextStyles.tituloTarjeta),
                            const SizedBox(height: PKSpacing.xs),
                            // ─── Calificación del vendedor (prominente) ───
                            if (t.estrellasPromedio != null && t.estrellasTotal >= 3)
                              Row(
                                children: [
                                  ...List.generate(5, (i) {
                                    final filled = i < t.estrellasPromedio!.round();
                                    return Icon(
                                      filled ? Icons.star_rounded : Icons.star_outline_rounded,
                                      size: 16,
                                      color: const Color(0xFFF59E0B),
                                    );
                                  }),
                                  const SizedBox(width: 6),
                                  Text(
                                    t.estrellasPromedio!.toStringAsFixed(1),
                                    style: const TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w800,
                                      color: Color(0xFF92400E),
                                      fontFamily: 'DMSans',
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    '(${t.estrellasTotal} reseñas)',
                                    style: const TextStyle(
                                      fontSize: 12,
                                      color: Color(0xFF9CA3AF),
                                      fontFamily: 'DMSans',
                                    ),
                                  ),
                                ],
                              )
                            else
                              const Row(
                                children: [
                                  Icon(Icons.star_outline_rounded, size: 15, color: Color(0xFF9CA3AF)),
                                  SizedBox(width: 4),
                                  Text(
                                    'Nueva • Sin calificaciones',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Color(0xFF9CA3AF),
                                      fontStyle: FontStyle.italic,
                                      fontFamily: 'DMSans',
                                    ),
                                  ),
                                ],
                              ),
                            // ─── Badge premium/matriculado ───
                            const SizedBox(height: PKSpacing.xs),
                            if (t.esPremium)
                              const PKInsigniaSaco10()
                            else
                              const PKInsigniaMatriculado(),
                            if (t.puntoEntrega != null) ...[
                              const SizedBox(height: PKSpacing.xs),
                              Text('📍 ${t.puntoEntrega!.nombre}', style: PKTextStyles.etiqueta),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                  if (t.descripcion != null && t.descripcion!.isNotEmpty) ...[
                    const SizedBox(height: PKSpacing.md),
                    Text(t.descripcion!, style: PKTextStyles.cuerpo),
                  ],
                  if (t.horario != null) ...[
                    const SizedBox(height: PKSpacing.xs),
                    Text('⏰ Horario: ${t.horario}', style: PKTextStyles.etiqueta),
                  ],
                  if (!t.puedeRecibirPedidos) ...[
                    const SizedBox(height: PKSpacing.sm),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFEDED),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFFFCDD2)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.info_outline, color: Color(0xFFBA1A1A), size: 18),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              !t.recibiendoPedidos
                                  ? 'Esta tienda ha pausado sus pedidos temporalmente.'
                                  : 'Fuera de horario de atención. Abre ${t.siguienteApertura ?? "próximamente"}.',
                              style: const TextStyle(
                                fontSize: 12,
                                color: Color(0xFFBA1A1A),
                                fontWeight: FontWeight.w600,
                                fontFamily: 'DMSans',
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Catálogo de Productos
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
              child: Text(
                'Productos disponibles (${t.productos.length})',
                style: PKTextStyles.tituloSeccion.copyWith(fontSize: 16),
              ),
            ),

            if (t.productos.isEmpty)
              const Padding(
                padding: EdgeInsets.all(PKSpacing.xl),
                child: Center(
                  child: PKEstadoVacio(
                    icono: Icons.inventory_2_outlined,
                    titulo: 'Sin productos aún',
                    subtitulo: 'Esta tienda todavía no ha publicado productos.',
                  ),
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base),
                itemCount: t.productos.length,
                itemBuilder: (context, index) {
                  final prod = t.productos[index];
                  return ProductCard(
                    producto: prod,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => ProductoScreen(producto: prod, tienda: t)),
                      );
                    },
                    onAddToCart: () {
                      if (!t.puedeRecibirPedidos) {
                        pkSnackError(context, 'Esta tienda no está recibiendo pedidos en este momento.');
                        return;
                      }
                      final agregado = cart.agregarProducto(prod, t);
                      if (!agregado) {
                        showDialog(
                          context: context,
                          builder: (ctx) => AlertDialog(
                            title: const Text('Cambiar de tienda'),
                            content: Text(
                              'Tu carrito tiene productos de "${cart.tiendaNombre}". ¿Quieres vaciarlo y empezar con "${t.nombre}"?',
                            ),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(ctx),
                                child: const Text('Mantener carrito'),
                              ),
                              PKBotonPrimario(
                                texto: 'Vaciar y continuar',
                                onPressed: () {
                                  cart.forzarAgregar(prod, t);
                                  Navigator.pop(ctx);
                                },
                              ),
                            ],
                          ),
                        );
                      } else {
                        pkSnackOk(context, '${prod.nombre} agregado al carrito');
                      }
                    },
                  );
                },
              ),

            const SizedBox(height: PKSpacing.xxl),
          ],
        ),
      ),
    );
  }
}

// ─── P08: Detalle de Producto ─────────────────────────────────────────────────
class ProductoScreen extends StatefulWidget {
  final Producto producto;
  final Tienda tienda;
  const ProductoScreen({super.key, required this.producto, required this.tienda});

  @override
  State<ProductoScreen> createState() => _ProductoScreenState();
}

class _ProductoScreenState extends State<ProductoScreen> {
  int _cantidad = 1;

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final p = widget.producto;

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: Text(p.nombre)),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(PKSpacing.base),
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
          boxShadow: PKShadows.tarjeta,
        ),
        child: Row(
          children: [
            // Selector de cantidad (1..20)
            Container(
              decoration: BoxDecoration(
                color: PKColors.tarjeta,
                border: Border.all(color: PKColors.borde),
                borderRadius: PKRadii.botonAll,
              ),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.remove, size: 18),
                    onPressed: _cantidad > 1 ? () => setState(() => _cantidad--) : null,
                  ),
                  Text('$_cantidad', style: PKTextStyles.tituloTarjeta),
                  IconButton(
                    icon: const Icon(Icons.add, size: 18),
                    onPressed: _cantidad < 20 ? () => setState(() => _cantidad++) : null,
                  ),
                ],
              ),
            ),
            const SizedBox(width: PKSpacing.base),
            Expanded(
              child: PKBotonPrimario(
                texto: !widget.tienda.puedeRecibirPedidos
                    ? 'Tienda en pausa'
                    : (p.disponible ? 'Agregar \$${(p.precioEfectivo * _cantidad).toStringAsFixed(2)}' : 'Agotado'),
                icono: Icons.add_shopping_cart,
                onPressed: (p.disponible && widget.tienda.puedeRecibirPedidos)
                    ? () {
                        cart.agregarProducto(p, widget.tienda, cantidad: _cantidad);
                        Navigator.pop(context);
                      }
                    : null,
              ),
            ),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (p.primeraFotoUrl != null && p.primeraFotoUrl!.isNotEmpty)
              Container(
                height: 250,
                width: double.infinity,
                decoration: BoxDecoration(
                  borderRadius: PKRadii.tarjetaAll,
                  color: PKColors.neutroFondo,
                  border: Border.all(color: PKColors.borde),
                ),
                clipBehavior: Clip.antiAlias,
                child: Image.network(
                  p.primeraFotoUrl!,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const Center(
                    child: Icon(Icons.fastfood_outlined, size: 64, color: PKColors.secundario),
                  ),
                ),
              ),
            const SizedBox(height: PKSpacing.base),
            Text(p.nombre, style: PKTextStyles.tituloPantalla.copyWith(fontSize: 22)),
            const SizedBox(height: PKSpacing.xs),
            // ─── Tienda + calificación del vendedor en detalle de producto ───
            Row(
              children: [
                const Icon(Icons.storefront_outlined, size: 14, color: PKColors.secundario),
                const SizedBox(width: 4),
                Text(widget.tienda.nombre, style: PKTextStyles.etiqueta),
                const SizedBox(width: 10),
                if (widget.tienda.estrellasPromedio != null && widget.tienda.estrellasTotal >= 3) ...[
                  const Icon(Icons.star_rounded, size: 14, color: Color(0xFFF59E0B)),
                  const SizedBox(width: 2),
                  Text(
                    '${widget.tienda.estrellasPromedio!.toStringAsFixed(1)} (${widget.tienda.estrellasTotal})',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF92400E),
                      fontFamily: 'DMSans',
                    ),
                  ),
                ] else
                  const Text(
                    '✨ Nueva',
                    style: TextStyle(fontSize: 12, color: Color(0xFF9CA3AF), fontFamily: 'DMSans'),
                  ),
              ],
            ),
            const SizedBox(height: PKSpacing.sm),
            if (p.tieneDescuento) ...[
              Row(
                children: [
                  Text('\$${p.precioEfectivo.toStringAsFixed(2)}', style: PKTextStyles.precioGrande),
                  const SizedBox(width: PKSpacing.sm),
                  Text(
                    '\$${p.precio.toStringAsFixed(2)}',
                    style: PKTextStyles.tituloTarjeta.copyWith(
                      decoration: TextDecoration.lineThrough,
                      color: PKColors.secundario,
                    ),
                  ),
                  const SizedBox(width: PKSpacing.sm),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFCC4E01),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '${p.porcentajeDescuento.toStringAsFixed(0)}% OFF',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        fontFamily: 'DMSans',
                      ),
                    ),
                  ),
                ],
              ),
            ] else ...[
              Text('\$${p.precio.toStringAsFixed(2)}', style: PKTextStyles.precioGrande),
            ],
            const SizedBox(height: PKSpacing.base),
            if (p.descripcion != null && p.descripcion!.isNotEmpty) ...[
              Text('Descripción', style: PKTextStyles.tituloTarjeta),
              const SizedBox(height: PKSpacing.xs),
              Text(p.descripcion!, style: PKTextStyles.cuerpo),
            ],
          ],
        ),
      ),
    );
  }
}
