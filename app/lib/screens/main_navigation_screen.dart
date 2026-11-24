import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../theme/theme.dart';
import '../widgets/pk_components.dart';
import '../providers/auth_provider.dart';
import '../providers/tienda_provider.dart';
import '../providers/favoritos_provider.dart';
import 'vitrina/vitrina_screens.dart';
import 'pedidos/mis_pedidos_screen.dart';
import 'tienda/tienda_screens.dart';
import 'cuenta/cuenta_screens.dart';
import 'favoritos/favoritos_screen.dart';
import 'auth/auth_screens.dart';

class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    VitrinaScreen(),
    FavoritosScreen(),
    MisPedidosScreen(),
    MiTiendaScreen(),
    CuentaScreen(),
  ];

  static const _items = [
    PKNavItem(icono: Icons.storefront_outlined,    iconoActivo: Icons.storefront,          etiqueta: 'Vitrina'),
    PKNavItem(icono: Icons.favorite_border_rounded, iconoActivo: Icons.favorite_rounded,   etiqueta: 'Favoritos'),
    PKNavItem(icono: Icons.receipt_long_outlined,  iconoActivo: Icons.receipt_long,         etiqueta: 'Mis pedidos'),
    PKNavItem(icono: Icons.shopping_bag_outlined,  iconoActivo: Icons.shopping_bag,         etiqueta: 'Mi tienda'),
    PKNavItem(icono: Icons.person_outline_rounded, iconoActivo: Icons.person_rounded,       etiqueta: 'Cuenta'),
  ];

  void _onItemTapped(int index) {
    final auth = Provider.of<AuthProvider>(context, listen: false);

    // Favoritos (index 1) accesible sin sesión (es local)
    if (index != 0 && index != 1 && !auth.tieneSesion) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const BienvenidaScreen()),
      );
      return;
    }

    if (index == 1) {
      Provider.of<FavoritosProvider>(context, listen: false).cargar();
    }

    if (index == 3) {
      Provider.of<TiendaProvider>(context, listen: false).cargarMiTienda();
    }

    setState(() => _currentIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    final ancho = MediaQuery.of(context).size.width;

    if (ancho >= PKBreakpoints.mobile) {
      // ── Tableta/escritorio: NavigationRail lateral ─────────────────────────
      return Scaffold(
        body: Row(
          children: [
            Container(
              decoration: const BoxDecoration(
                border: Border(right: BorderSide(color: PKColors.borde)),
              ),
              child: NavigationRail(
                selectedIndex: _currentIndex,
                onDestinationSelected: _onItemTapped,
                backgroundColor: PKColors.tarjeta,
                indicatorColor: PKColors.primarioSuave,
                selectedIconTheme: const IconThemeData(color: PKColors.primarioSuaveTexto),
                selectedLabelTextStyle: const TextStyle(
                  fontFamily: 'DMSans',
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: PKColors.primarioSuaveTexto,
                ),
                unselectedIconTheme: const IconThemeData(color: PKColors.secundario),
                unselectedLabelTextStyle: const TextStyle(
                  fontFamily: 'DMSans',
                  fontSize: 12,
                  color: PKColors.secundario,
                ),
                labelType: NavigationRailLabelType.all,
                destinations: _items
                    .map((i) => NavigationRailDestination(
                          icon: Icon(i.icono),
                          selectedIcon: Icon(i.iconoActivo ?? i.icono),
                          label: Text(i.etiqueta),
                        ))
                    .toList(),
              ),
            ),
            Expanded(
              child: IndexedStack(
                index: _currentIndex,
                children: _screens,
              ),
            ),
          ],
        ),
      );
    }

    // ── Móvil: NavigationBar inferior ──────────────────────────────────────
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: PKColors.tarjeta,
          border: Border(top: BorderSide(color: PKColors.borde)),
        ),
        child: NavigationBar(
          selectedIndex: _currentIndex,
          onDestinationSelected: _onItemTapped,
          destinations: _items
              .map((i) => NavigationDestination(
                    icon: Icon(i.icono),
                    selectedIcon: Icon(i.iconoActivo ?? i.icono),
                    label: i.etiqueta,
                  ))
              .toList(),
        ),
      ),
    );
  }
}
