import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:puntokampus/models/tienda.dart';
import 'package:puntokampus/providers/auth_provider.dart';
import 'package:puntokampus/providers/cart_provider.dart';
import 'package:puntokampus/providers/tienda_provider.dart';
import 'package:puntokampus/providers/vitrina_provider.dart';
import 'package:puntokampus/screens/auth/auth_screens.dart';
import 'package:puntokampus/screens/tienda/tienda_screens.dart';
import 'package:puntokampus/screens/tienda/premium_screen.dart';
import 'package:puntokampus/screens/vitrina/reportar_tienda_screen.dart';
import 'package:puntokampus/constants/app_strings.dart';
import 'package:puntokampus/theme/theme.dart';
import 'package:puntokampus/widgets/pk_components.dart';

void main() {
  Widget envolverConProviders(Widget child) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => VitrinaProvider()),
        ChangeNotifierProvider(create: (_) => TiendaProvider()),
      ],
      child: MaterialApp(
        theme: PKTheme.light,
        home: child,
      ),
    );
  }

  group('Pruebas de Formularios PuntoKampus', () {
    testWidgets('BienvenidaScreen muestra opciones de ingresar y explorar', (WidgetTester tester) async {
      await tester.pumpWidget(envolverConProviders(const BienvenidaScreen()));

      expect(find.text(AppStrings.btnEntrarUtb), findsOneWidget);
      expect(find.text('Ver la vitrina sin sesión'), findsOneWidget);
    });

    testWidgets('CorreoScreen renderiza campo de correo y boton continuar', (WidgetTester tester) async {
      await tester.pumpWidget(envolverConProviders(const CorreoScreen()));

      expect(find.text('Acceso con correo UTB'), findsOneWidget);
      expect(find.byType(TextFormField), findsOneWidget);
    });

    testWidgets('CodigoScreen renderiza pantalla de verificacion', (WidgetTester tester) async {
      await tester.pumpWidget(envolverConProviders(
        const CodigoScreen(correo: 'test@utb.edu.ec'),
      ));

      expect(find.text('Verificar código'), findsOneWidget);
    });

    testWidgets('FormProductoScreen renderiza campos requeridos de producto y selector de imagen', (WidgetTester tester) async {
      await tester.pumpWidget(envolverConProviders(const FormProductoScreen()));

      expect(find.text('Nuevo Producto'), findsOneWidget);
      expect(find.text('Nombre del producto *'), findsOneWidget);
      expect(find.text('Precio en dólares (\$)*'), findsOneWidget);
      expect(find.text('Descripción (opcional)'), findsOneWidget);
      expect(find.text('Foto del producto (opcional)'), findsOneWidget);
      expect(find.text('Guardar Producto'), findsOneWidget);
    });

    testWidgets('FormTiendaScreen renderiza campos de creacion de tienda y logo', (WidgetTester tester) async {
      await tester.pumpWidget(envolverConProviders(const FormTiendaScreen()));

      expect(find.text('Crear mi tienda'), findsWidgets);
      expect(find.text('Nombre de la tienda *'), findsOneWidget);
      expect(find.text('Descripción *'), findsOneWidget);
      expect(find.text('Categoría principal *'), findsOneWidget);
      expect(find.text('Punto de entrega preferido *'), findsOneWidget);
      expect(find.text('Horario disponible *'), findsOneWidget);
      expect(find.text('Logo de la tienda (opcional)'), findsOneWidget);
    });

    testWidgets('ReportarTiendaScreen renderiza motivos de reporte y boton de envio', (WidgetTester tester) async {
      final tiendaDummy = Tienda(
        id: 99,
        nombre: 'Snacks UTB Test',
        slug: 'snacks-utb-test',
        usuarioId: 10,
        plan: 'gratis',
        estado: 'activa',
      );

      await tester.pumpWidget(envolverConProviders(
        ReportarTiendaScreen(tienda: tiendaDummy),
      ));

      expect(find.text('Reportar Tienda'), findsOneWidget);
      expect(find.text('Motivo del reporte *'), findsOneWidget);
      expect(find.text('Venta de productos no permitidos en el campus'), findsOneWidget);
      expect(find.text('Enviar Reporte'), findsOneWidget);
    });

    testWidgets('PKSelectorImagen muestra placeholder de galeria', (WidgetTester tester) async {
      await tester.pumpWidget(MaterialApp(
        theme: PKTheme.light,
        home: Scaffold(
          body: PKSelectorImagen(
            etiqueta: 'Foto de comprobante',
            onImagenSeleccionada: (bytes, nombre) {},
          ),
        ),
      ));

      expect(find.text('Foto de comprobante'), findsOneWidget);
      expect(find.text('Elegir de la galería'), findsOneWidget);
      expect(find.text('PNG o JPG hasta 5MB'), findsOneWidget);
    });
  });
}
