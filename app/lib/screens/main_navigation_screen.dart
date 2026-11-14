import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constants/app_colors.dart';
import '../providers/auth_provider.dart';
import 'vitrina/vitrina_screens.dart';
import 'pedidos/mis_pedidos_screen.dart';
import 'tienda/tienda_screens.dart';
import 'cuenta/cuenta_screens.dart';
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
    MisPedidosScreen(),
    MiTiendaScreen(),
    CuentaScreen(),
  ];

  void _onItemTapped(int index) {
    final auth = Provider.of<AuthProvider>(context, listen: false);

    // Si intenta ir a pedidos, tienda o cuenta sin sesión, invitar a entrar
    if (index != 0 && !auth.tieneSesion) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const BienvenidaScreen()),
      );
      return;
    }

    setState(() {
      _currentIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: _onItemTapped,
        backgroundColor: AppColors.surface,
        indicatorColor: AppColors.primaryLight,
        elevation: 4,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.storefront_outlined),
            selectedIcon: Icon(Icons.storefront, color: AppColors.primary),
            label: 'Vitrina',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long, color: AppColors.primary),
            label: 'Mis pedidos',
          ),
          NavigationDestination(
            icon: Icon(Icons.shopping_bag_outlined),
            selectedIcon: Icon(Icons.shopping_bag, color: AppColors.primary),
            label: 'Mi tienda',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person, color: AppColors.primary),
            label: 'Cuenta',
          ),
        ],
      ),
    );
  }
}
