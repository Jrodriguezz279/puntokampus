import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:puntokampus/main.dart';
import 'package:puntokampus/theme/theme.dart';
import 'package:puntokampus/widgets/pk_components.dart';

void main() {
  Widget crearTestApp(Widget child) {
    return MaterialApp(
      theme: PKTheme.light,
      home: Scaffold(body: child),
    );
  }

  group('PuntoKampus UI Smoke Tests', () {
    testWidgets('renderiza barra de navegacion principal', (WidgetTester tester) async {
      await tester.pumpWidget(const PuntoKampusApp());
      expect(find.text('Vitrina'), findsOneWidget);
      expect(find.text('Favoritos'), findsOneWidget);
      expect(find.text('Mis pedidos'), findsOneWidget);
      expect(find.text('Mi tienda'), findsOneWidget);
      expect(find.text('Cuenta'), findsOneWidget);
    });

    testWidgets('PKBotonPrimario renderiza texto y responde a tap', (WidgetTester tester) async {
      bool pulsado = false;
      await tester.pumpWidget(crearTestApp(
        PKBotonPrimario(
          texto: 'Comprar',
          onPressed: () => pulsado = true,
        ),
      ));

      expect(find.text('Comprar'), findsOneWidget);
      await tester.tap(find.text('Comprar'));
      expect(pulsado, isTrue);
    });

    testWidgets('PKBotonSecundario renderiza texto', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        PKBotonSecundario(
          texto: 'Cancelar',
          onPressed: () {},
        ),
      ));

      expect(find.text('Cancelar'), findsOneWidget);
    });

    testWidgets('PKTarjeta muestra contenido hijo', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        const PKTarjeta(
          child: Text('Contenido tarjeta'),
        ),
      ));

      expect(find.text('Contenido tarjeta'), findsOneWidget);
    });

    testWidgets('PKCampoEtiquetado muestra etiqueta y campo', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        const PKCampoEtiquetado(
          etiqueta: 'Correo institucional',
          hint: 'ejemplo@utb.edu.ec',
        ),
      ));

      expect(find.text('Correo institucional'), findsOneWidget);
      expect(find.byType(TextFormField), findsOneWidget);
    });

    testWidgets('Insignias Matriculado y Saco 10 renderizan correctamente', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        Column(
          children: const [
            PKInsigniaMatriculado(),
            PKInsigniaSaco10(),
          ],
        ),
      ));

      expect(find.text('Matriculado'), findsOneWidget);
      expect(find.text('Sacó 10'), findsOneWidget);
    });

    testWidgets('PKPastillaEstado renderiza estados', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        Column(
          children: const [
            PKPastillaEstado(estado: EstadoPedido.pendiente),
            PKPastillaEstado(estado: EstadoPedido.entregado),
          ],
        ),
      ));

      expect(find.text('Pendiente'), findsOneWidget);
      expect(find.text('Entregado'), findsOneWidget);
    });

    testWidgets('PKEstadoVacio muestra titulo y subtitulo', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        const PKEstadoVacio(
          icono: Icons.inbox,
          titulo: 'No hay nada aquí',
          subtitulo: 'Intenta después',
        ),
      ));

      expect(find.text('No hay nada aquí'), findsOneWidget);
      expect(find.text('Intenta después'), findsOneWidget);
    });

    testWidgets('PKEstadoError muestra mensaje de error y boton reintentar', (WidgetTester tester) async {
      bool reintentado = false;
      await tester.pumpWidget(crearTestApp(
        PKEstadoError(
          mensaje: 'Fallo de red',
          onReintentar: () => reintentado = true,
        ),
      ));

      expect(find.text('Fallo de red'), findsOneWidget);
      expect(find.text('Reintentar'), findsOneWidget);
      await tester.tap(find.text('Reintentar'));
      expect(reintentado, isTrue);
    });

    testWidgets('PKEsqueleto renderiza correctamente', (WidgetTester tester) async {
      await tester.pumpWidget(crearTestApp(
        const PKEsqueleto(height: 100),
      ));

      expect(find.byType(PKEsqueleto), findsOneWidget);
    });
  });
}
