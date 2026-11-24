import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../theme/theme.dart';
import '../../models/tienda.dart';
import '../../models/producto.dart';
import '../../providers/favoritos_provider.dart';
import '../../providers/vitrina_provider.dart';
import '../../widgets/item_cards.dart';
import '../vitrina/vitrina_screens.dart';

// ══════════════════════════════════════════════════════════════════════════════
// FAVORITOS — Tiendas y Productos guardados localmente
// ══════════════════════════════════════════════════════════════════════════════

class FavoritosScreen extends StatefulWidget {
  const FavoritosScreen({super.key});

  @override
  State<FavoritosScreen> createState() => _FavoritosScreenState();
}

class _FavoritosScreenState extends State<FavoritosScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<FavoritosProvider>(context, listen: false).cargar();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final favs = Provider.of<FavoritosProvider>(context);
    final vitrina = Provider.of<VitrinaProvider>(context);

    final tiendasFav = favs.tiendasFavoritas(vitrina.tiendas);
    final productosFav = favs.productosFavoritos(vitrina.productos);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Mis Favoritos'),
        backgroundColor: PKColors.tarjeta,
        elevation: 0,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Container(
            margin: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: 6),
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: const Color(0xFFF1EAE4),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: PKColors.borde),
            ),
            child: TabBar(
              controller: _tabController,
              indicator: BoxDecoration(
                color: PKColors.tarjeta,
                borderRadius: BorderRadius.circular(10),
                boxShadow: PKShadows.tarjeta,
              ),
              labelColor: PKColors.primario,
              unselectedLabelColor: PKColors.secundario,
              labelStyle: const TextStyle(
                fontFamily: 'DMSans',
                fontWeight: FontWeight.bold,
                fontSize: 13,
              ),
              unselectedLabelStyle: const TextStyle(
                fontFamily: 'DMSans',
                fontWeight: FontWeight.w500,
                fontSize: 13,
              ),
              dividerColor: Colors.transparent,
              tabs: [
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.storefront_outlined, size: 16),
                      const SizedBox(width: 6),
                      const Text('Tiendas'),
                      const SizedBox(width: 5),
                      _countBadge(tiendasFav.length),
                    ],
                  ),
                ),
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.inventory_2_outlined, size: 16),
                      const SizedBox(width: 6),
                      const Text('Productos'),
                      const SizedBox(width: 5),
                      _countBadge(productosFav.length),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // ─── Tab Tiendas ───────────────────────────────────────────────────
          tiendasFav.isEmpty
              ? _EmptyFavs(
                  icono: Icons.storefront_outlined,
                  titulo: 'Sin tiendas favoritas',
                  subtitulo: 'Toca el ❤️ en cualquier tienda para guardarla aquí.',
                  onExplorar: () => _irAVitrina(context),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(PKSpacing.base),
                  itemCount: tiendasFav.length,
                  itemBuilder: (ctx, i) {
                    final tienda = tiendasFav[i];
                    return Stack(
                      children: [
                        StoreCard(
                          tienda: tienda,
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => TiendaScreen(slug: tienda.slug),
                            ),
                          ),
                        ),
                        Positioned(
                          right: 8,
                          top: 8,
                          child: _FavButton(
                            isFav: true,
                            onToggle: () => favs.toggleTienda(tienda.id),
                          ),
                        ),
                      ],
                    );
                  },
                ),

          // ─── Tab Productos ─────────────────────────────────────────────────
          productosFav.isEmpty
              ? _EmptyFavs(
                  icono: Icons.inventory_2_outlined,
                  titulo: 'Sin productos favoritos',
                  subtitulo: 'Toca el ❤️ en cualquier producto para guardarlo aquí.',
                  onExplorar: () => _irAVitrina(context),
                )
              : GridView.builder(
                  padding: const EdgeInsets.all(PKSpacing.base),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 0.68,
                  ),
                  itemCount: productosFav.length,
                  itemBuilder: (ctx, i) {
                    final producto = productosFav[i];
                    final matchTienda = vitrina.tiendas
                        .where((t) => t.slug == producto.tiendaSlug)
                        .toList();
                    return Stack(
                      children: [
                        ProductGridCard(
                          producto: producto,
                          onTap: () {
                            if (matchTienda.isNotEmpty) {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ProductoScreen(
                                    producto: producto,
                                    tienda: matchTienda.first,
                                  ),
                                ),
                              );
                            } else if (producto.tiendaSlug != null) {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => TiendaScreen(slug: producto.tiendaSlug!),
                                ),
                              );
                            }
                          },
                        ),
                        Positioned(
                          right: 6,
                          top: 6,
                          child: _FavButton(
                            isFav: true,
                            onToggle: () => favs.toggleProducto(producto.id),
                          ),
                        ),
                      ],
                    );
                  },
                ),
        ],
      ),
    );
  }

  Widget _countBadge(int count) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
        decoration: BoxDecoration(
          color: PKColors.primarioSuave,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Text(
          '$count',
          style: const TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.bold,
            color: PKColors.primario,
            fontFamily: 'DMSans',
          ),
        ),
      );

  void _irAVitrina(BuildContext context) {
    // Regresa al stack y el bottom nav se encarga del cambio de tab
    Navigator.pop(context);
  }
}

// ─── Botón de favorito (corazón) ───────────────────────────────────────────
class _FavButton extends StatelessWidget {
  final bool isFav;
  final VoidCallback onToggle;
  const _FavButton({required this.isFav, required this.onToggle});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onToggle,
      child: Container(
        padding: const EdgeInsets.all(6),
        decoration: BoxDecoration(
          color: isFav ? const Color(0xFFFEE2E2) : PKColors.tarjeta,
          shape: BoxShape.circle,
          border: Border.all(
            color: isFav ? const Color(0xFFFECACA) : PKColors.borde,
          ),
          boxShadow: PKShadows.tarjeta,
        ),
        child: Icon(
          isFav ? Icons.favorite_rounded : Icons.favorite_border_rounded,
          size: 16,
          color: isFav ? const Color(0xFFE11D48) : PKColors.secundario,
        ),
      ),
    );
  }
}

// ─── Widget de estado vacío ────────────────────────────────────────────────
class _EmptyFavs extends StatelessWidget {
  final IconData icono;
  final String titulo;
  final String subtitulo;
  final VoidCallback onExplorar;

  const _EmptyFavs({
    required this.icono,
    required this.titulo,
    required this.subtitulo,
    required this.onExplorar,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(PKSpacing.xl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: const BoxDecoration(
                color: PKColors.primarioSuave,
                shape: BoxShape.circle,
              ),
              child: Icon(icono, size: 36, color: PKColors.primario),
            ),
            const SizedBox(height: PKSpacing.base),
            Text(
              titulo,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: PKColors.titulo,
                fontFamily: 'DMSans',
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: PKSpacing.xs),
            Text(
              subtitulo,
              style: const TextStyle(
                fontSize: 14,
                color: PKColors.secundario,
                fontFamily: 'DMSans',
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: PKSpacing.base),
            OutlinedButton.icon(
              icon: const Icon(Icons.storefront_outlined, size: 16),
              label: const Text('Explorar vitrina'),
              onPressed: onExplorar,
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Widget reutilizable: botón de favorito para tiendas y productos ─────────
class FavTiendaButton extends StatefulWidget {
  final int tiendaId;
  final double size;
  const FavTiendaButton({super.key, required this.tiendaId, this.size = 20});

  @override
  State<FavTiendaButton> createState() => _FavTiendaButtonState();
}

class _FavTiendaButtonState extends State<FavTiendaButton> {
  @override
  Widget build(BuildContext context) {
    final favs = Provider.of<FavoritosProvider>(context);
    final isFav = favs.esTiendaFav(widget.tiendaId);
    return GestureDetector(
      onTap: () => favs.toggleTienda(widget.tiendaId),
      child: AnimatedSwitcher(
        duration: const Duration(milliseconds: 200),
        child: Icon(
          isFav ? Icons.favorite_rounded : Icons.favorite_border_rounded,
          key: ValueKey(isFav),
          size: widget.size,
          color: isFav ? const Color(0xFFE11D48) : PKColors.secundario,
        ),
      ),
    );
  }
}

class FavProductoButton extends StatefulWidget {
  final int productoId;
  final double size;
  const FavProductoButton({super.key, required this.productoId, this.size = 20});

  @override
  State<FavProductoButton> createState() => _FavProductoButtonState();
}

class _FavProductoButtonState extends State<FavProductoButton> {
  @override
  Widget build(BuildContext context) {
    final favs = Provider.of<FavoritosProvider>(context);
    final isFav = favs.esProductoFav(widget.productoId);
    return GestureDetector(
      onTap: () => favs.toggleProducto(widget.productoId),
      child: AnimatedSwitcher(
        duration: const Duration(milliseconds: 200),
        child: Icon(
          isFav ? Icons.favorite_rounded : Icons.favorite_border_rounded,
          key: ValueKey(isFav),
          size: widget.size,
          color: isFav ? const Color(0xFFE11D48) : PKColors.secundario,
        ),
      ),
    );
  }
}
