import 'package:flutter_test/flutter_test.dart';
import 'package:puntokampus/main.dart';

void main() {
  testWidgets('PuntoKampus smoke test - renderiza barra de navegacion', (WidgetTester tester) async {
    await tester.pumpWidget(const PuntoKampusApp());
    expect(find.text('Vitrina'), findsOneWidget);
    expect(find.text('Mis pedidos'), findsOneWidget);
    expect(find.text('Mi tienda'), findsOneWidget);
    expect(find.text('Cuenta'), findsOneWidget);
  });
}
