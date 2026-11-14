import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../models/tienda.dart';
import '../../models/producto.dart';
import '../../providers/vitrina_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/vitrina_service.dart';
import '../../widgets/common_widgets.dart';
import '../../widgets/item_cards.dart';
import '../auth/auth_screens.dart';
import '../carrito/carrito_screens.dart';

// P05: Vitrina Principal
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
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: RichText(
          text: const TextSpan(
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.primary),
            children: [
              TextSpan(text: 'Punto'),
              TextSpan(text: 'Kampus', style: TextStyle(color: AppColors.accent)),
            ],
          ),
        ),
        backgroundColor: AppColors.surface,
        elevation: 0,
        actions: [
          // Botón Carrito con badge
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.shopping_bag_outlined, color: AppColors.primary),
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
                    decoration: const BoxDecoration(color: AppColors.accent, shape: BoxShape.circle),
                    constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                    child: Text(
                      '${cart.itemCount}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
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
              child: const Text('Entrar', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary)),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => vitrina.cargarInicial(),
        child: CustomScrollView(
          slivers: [
            // Barra de búsqueda
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Buscar comida, postres, impresiones...',
                    prefixIcon: const Icon(Icons.search, color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.surface,
                    contentPadding: const EdgeInsets.symmetric(vertical: 0),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: AppColors.border),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: AppColors.border),
                    ),
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
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: vitrina.categorias.length + 1,
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      final isSelected = vitrina.categoriaSeleccionadaId == null;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: const Text('Todas'),
                          selected: isSelected,
                          onSelected: (_) => vitrina.filtrarPorCategoria(null),
                          selectedColor: AppColors.primary,
                          labelStyle: TextStyle(
                            color: isSelected ? Colors.white : AppColors.text,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      );
                    }
                    final cat = vitrina.categorias[index - 1];
                    final isSelected = vitrina.categoriaSeleccionadaId == cat.id;
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: ChoiceChip(
                        label: Text(cat.nombre),
                        selected: isSelected,
                        onSelected: (_) => vitrina.filtrarPorCategoria(isSelected ? null : cat.id),
                        selectedColor: AppColors.primary,
                        labelStyle: TextStyle(
                          color: isSelected ? Colors.white : AppColors.text,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 16)),

            // Título de la sección
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Text(
                  'Tiendas en el campus UTB (${vitrina.tiendas.length})',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.text),
                ),
              ),
            ),

            // Lista de tiendas
            if (vitrina.cargando)
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (_, __) => const LoadingSkeleton(height: 90),
                    childCount: 4,
                  ),
                ),
              )
            else if (vitrina.error != null)
              SliverFillRemaining(
                child: ErrorViewWidget(
                  message: vitrina.error!,
                  onRetry: () => vitrina.cargarInicial(),
                ),
              )
            else if (vitrina.tiendas.isEmpty)
              const SliverFillRemaining(
                child: EmptyStateWidget(
                  icon: '🏪',
                  message: 'No encontramos tiendas en esta categoría o búsqueda.',
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
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

// P07: Pantalla Detalle de Tienda y Catálogo
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
        appBar: AppBar(title: const Text('Cargando tienda...')),
        body: const Padding(
          padding: EdgeInsets.all(16.0),
          child: Column(
            children: [
              LoadingSkeleton(height: 120),
              SizedBox(height: 16),
              LoadingSkeleton(height: 80),
              LoadingSkeleton(height: 80),
            ],
          ),
        ),
      );
    }

    if (_error != null || _tienda == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Error')),
        body: ErrorViewWidget(message: _error ?? 'Tienda no disponible.', onRetry: _cargar),
      );
    }

    final t = _tienda!;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(t.nombre),
        actions: [
          IconButton(
            icon: const Icon(Icons.shopping_bag_outlined),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CarritoScreen())),
          ),
        ],
      ),
      bottomNavigationBar: cart.itemCount > 0 && cart.tiendaId == t.id
          ? Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, -2))],
              ),
              child: ElevatedButton(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CarritoScreen())),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Ver carrito (${cart.itemCount})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    Text('\$${cart.total.toStringAsFixed(2)}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            )
          : null,
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Cabecera de la Tienda
            Container(
              padding: const EdgeInsets.all(20),
              color: AppColors.surface,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 70,
                        height: 70,
                        decoration: BoxDecoration(
                          color: AppColors.primaryLight,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Center(
                          child: Text(t.nombre.isNotEmpty ? t.nombre[0].toUpperCase() : 'T',
                              style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppColors.primary)),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(t.nombre, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                            if (t.esPremium)
                              const Text('⭐ Sacó 10 (Comercio Verificado)',
                                  style: TextStyle(color: AppColors.accent, fontWeight: FontWeight.bold, fontSize: 12)),
                            if (t.puntoEntrega != null)
                              Text('📍 ${t.puntoEntrega!.nombre}', style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
                          ],
                        ),
                      ),
                    ],
                  ),
                  if (t.descripcion != null && t.descripcion!.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Text(t.descripcion!, style: const TextStyle(color: AppColors.text, fontSize: 14)),
                  ],
                  if (t.horario != null) ...[
                    const SizedBox(height: 6),
                    Text('⏰ Horario: ${t.horario}', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                  ],
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Catálogo de Productos
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Text('Productos disponibles (${t.productos.length})',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ),

            if (t.productos.isEmpty)
              const Padding(
                padding: EdgeInsets.all(32),
                child: Center(child: Text('Esta tienda no tiene productos publicados aún.')),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 16),
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
                      final agregado = cart.agregarProducto(prod, t);
                      if (!agregado) {
                        // Diálogo para cambiar de tienda
                        showDialog(
                          context: context,
                          builder: (ctx) => AlertDialog(
                            title: const Text('Cambiar de tienda'),
                            content: Text(
                                'Tu carrito tiene productos de "${cart.tiendaNombre}". ¿Quieres vaciarlo y empezar con "${t.nombre}"?'),
                            actions: [
                              TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Mantener carrito')),
                              ElevatedButton(
                                onPressed: () {
                                  cart.forzarAgregar(prod, t);
                                  Navigator.pop(ctx);
                                },
                                style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: Colors.white),
                                child: const Text('Vaciar y continuar'),
                              ),
                            ],
                          ),
                        );
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('${prod.nombre} agregado al carrito'),
                            duration: const Duration(seconds: 1),
                            action: SnackBarAction(
                              label: 'Ver carrito',
                              textColor: AppColors.accent,
                              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CarritoScreen())),
                            ),
                          ),
                        );
                      }
                    },
                  );
                },
              ),

            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }
}

// P08: Detalle de Producto
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
      backgroundColor: AppColors.background,
      appBar: AppBar(title: Text(p.nombre)),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        color: AppColors.surface,
        child: Row(
          children: [
            // Selector de cantidad (1..20)
            Container(
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.border),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.remove),
                    onPressed: _cantidad > 1 ? () => setState(() => _cantidad--) : null,
                  ),
                  Text('$_cantidad', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  IconButton(
                    icon: const Icon(Icons.add),
                    onPressed: _cantidad < 20 ? () => setState(() => _cantidad++) : null,
                  ),
                ],
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: ElevatedButton(
                onPressed: p.disponible
                    ? () {
                        cart.agregarProducto(p, widget.tienda, cantidad: _cantidad);
                        Navigator.pop(context);
                      }
                    : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: Text(
                  p.disponible ? 'Agregar \$${(p.precio * _cantidad).toStringAsFixed(2)}' : 'Agotado',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (p.primeraFotoUrl != null)
              Container(
                height: 250,
                width: double.infinity,
                decoration: BoxDecoration(borderRadius: BorderRadius.circular(14), color: Colors.grey.shade100),
                clipBehavior: Clip.antiAlias,
                child: Image.network(p.primeraFotoUrl!, fit: BoxFit.cover),
              ),
            const SizedBox(height: 20),
            Text(p.nombre, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text('\$${p.precio.toStringAsFixed(2)}',
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.primary)),
            const SizedBox(height: 16),
            if (p.descripcion != null && p.descripcion!.isNotEmpty) ...[
              const Text('Descripción', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 6),
              Text(p.descripcion!, style: const TextStyle(color: AppColors.text, fontSize: 14, height: 1.5)),
            ],
          ],
        ),
      ),
    );
  }
}
