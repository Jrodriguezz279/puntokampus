import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'theme/theme.dart';
import 'widgets/pk_components.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/vitrina_provider.dart';
import 'providers/tienda_provider.dart';
import 'providers/favoritos_provider.dart';
import 'screens/main_navigation_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final authProvider = AuthProvider();
  await authProvider.inicializar();

  runApp(PuntoKampusApp(authProvider: authProvider));
}

class PuntoKampusApp extends StatelessWidget {
  final AuthProvider? authProvider;
  const PuntoKampusApp({super.key, this.authProvider});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => authProvider ?? AuthProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => VitrinaProvider()),
        ChangeNotifierProvider(create: (_) => TiendaProvider()),
        ChangeNotifierProvider(create: (_) => FavoritosProvider()),
      ],
      child: MaterialApp(
        title: 'PuntoKampus UTB',
        debugShowCheckedModeBanner: false,
        theme: PKTheme.light,
        home: const MainNavigationScreen(),
        routes: {
          '/galeria': (ctx) => const _GaleriaComponentes(),
        },
      ),
    );
  }
}

// ─── Galería de componentes (modo debug) ──────────────────────────────────────
class _GaleriaComponentes extends StatelessWidget {
  const _GaleriaComponentes();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Galería de Componentes'),
        backgroundColor: PKColors.tarjeta,
      ),
      body: ListView(
        padding: const EdgeInsets.all(PKSpacing.base),
        children: [
          _seccion('Colores del sistema'),
          _filaColores(),
          _seccion('Tipografía'),
          _tipografia(),
          _seccion('Botones'),
          _botones(context),
          _seccion('Campos de texto'),
          _campos(),
          _seccion('Insignias y pastillas'),
          _insignias(),
          _seccion('Estado vacío'),
          const PKEstadoVacio(
            icono: Icons.storefront_outlined,
            titulo: 'Sin tiendas aún',
            subtitulo: 'Aquí aparecerán las tiendas del campus.',
            botonTexto: 'Explorar',
          ),
          _seccion('Estado de error'),
          PKEstadoError(mensaje: 'No se pudo conectar al servidor.', onReintentar: () {}),
          _seccion('Esqueletos de carga'),
          const PKEsqueleto(height: 90),
          const PKEsqueleto(height: 60),
          const PKEsqueleto(height: 40, borderRadius: PKRadii.chip),
          _seccion('Logo'),
          const Center(child: PKLogo(fontSize: 32)),
          const SizedBox(height: PKSpacing.xxl),
        ],
      ),
    );
  }

  Widget _seccion(String titulo) => Padding(
        padding: const EdgeInsets.only(top: PKSpacing.lg, bottom: PKSpacing.sm),
        child: Text(titulo, style: PKTextStyles.tituloSeccion),
      );

  Widget _filaColores() => Wrap(
        spacing: PKSpacing.sm,
        runSpacing: PKSpacing.sm,
        children: const [
          _ColorChip('primario', PKColors.primario),
          _ColorChip('primarioSuave', PKColors.primarioSuave),
          _ColorChip('fondo', PKColors.fondo),
          _ColorChip('tarjeta', PKColors.tarjeta),
          _ColorChip('titulo', PKColors.titulo),
          _ColorChip('secundario', PKColors.secundario),
          _ColorChip('borde', PKColors.borde),
          _ColorChip('okFondo', PKColors.okFondo),
          _ColorChip('errorFondo', PKColors.errorFondo),
          _ColorChip('avisoFondo', PKColors.avisoFondo),
          _ColorChip('neutroFondo', PKColors.neutroFondo),
        ],
      );

  Widget _tipografia() => PKTarjeta(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Título de Pantalla (28/700)', style: PKTextStyles.tituloPantalla),
            const SizedBox(height: PKSpacing.sm),
            Text('Título de Sección (20/700)', style: PKTextStyles.tituloSeccion),
            const SizedBox(height: PKSpacing.sm),
            Text('Título de Tarjeta (17/600)', style: PKTextStyles.tituloTarjeta),
            const SizedBox(height: PKSpacing.sm),
            Text('Cuerpo (16/400)', style: PKTextStyles.cuerpo),
            const SizedBox(height: PKSpacing.sm),
            Text('Cuerpo secundario (14/400)', style: PKTextStyles.cuerpoSecundario),
            const SizedBox(height: PKSpacing.sm),
            Text('Etiqueta/ayuda (13/500)', style: PKTextStyles.etiqueta),
            const SizedBox(height: PKSpacing.sm),
            Text('\$12.50', style: PKTextStyles.precio),
          ],
        ),
      );

  Widget _botones(BuildContext context) => PKTarjeta(
        child: Column(
          children: [
            PKBotonPrimario(texto: 'Botón primario', onPressed: () {}),
            const SizedBox(height: PKSpacing.sm),
            const PKBotonPrimario(texto: 'Cargando...', onPressed: null, cargando: true),
            const SizedBox(height: PKSpacing.sm),
            const PKBotonPrimario(texto: 'Deshabilitado', onPressed: null),
            const SizedBox(height: PKSpacing.sm),
            PKBotonSecundario(texto: 'Botón secundario', onPressed: () {}),
            const SizedBox(height: PKSpacing.sm),
            TextButton(
              onPressed: () {},
              child: const Text('Botón de texto'),
            ),
            const SizedBox(height: PKSpacing.sm),
            OutlinedButton(
              onPressed: () {},
              style: OutlinedButton.styleFrom(
                foregroundColor: PKColors.error,
                side: const BorderSide(color: PKColors.error),
              ),
              child: const Text('Botón destructivo'),
            ),
          ],
        ),
      );

  Widget _campos() => PKTarjeta(
        child: Column(
          children: [
            const PKCampoEtiquetado(
              etiqueta: 'Nombre del producto',
              hint: 'Ej. Brownie de chocolate',
            ),
            const SizedBox(height: PKSpacing.sm),
            PKCampoEtiquetado(
              etiqueta: 'Precio',
              hint: '0.00',
              keyboardType: TextInputType.number,
              prefixIcon: const Icon(Icons.attach_money_outlined, size: 20),
              validator: (v) => v == null || v.isEmpty ? 'Requerido' : null,
            ),
          ],
        ),
      );

  Widget _insignias() => Wrap(
        spacing: PKSpacing.sm,
        runSpacing: PKSpacing.sm,
        children: const [
          PKInsigniaMatriculado(),
          PKInsigniaSaco10(),
          PKPastillaEstado(estado: EstadoPedido.pendiente),
          PKPastillaEstado(estado: EstadoPedido.entregado),
          PKPastillaEstado(estado: EstadoPedido.cancelado),
          PKPastillaEstado(estado: EstadoPedido.vencido),
        ],
      );
}

class _ColorChip extends StatelessWidget {
  final String nombre;
  final Color color;
  const _ColorChip(this.nombre, this.color);

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 80,
      padding: const EdgeInsets.all(PKSpacing.xs),
      decoration: BoxDecoration(
        color: color,
        borderRadius: PKRadii.botonAll,
        border: Border.all(color: PKColors.borde),
      ),
      child: Text(
        nombre,
        textAlign: TextAlign.center,
        style: const TextStyle(fontSize: 9, color: PKColors.titulo),
      ),
    );
  }
}
