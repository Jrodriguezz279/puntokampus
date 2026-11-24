import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:puntokampus/widgets/calificar_bottom_sheet.dart';

void main() {
  testWidgets('CalificarBottomSheet renderiza 5 estrellas interactivas, aclaracion y boton', (tester) async {
    int? calificacionEnviada;

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: CalificarBottomSheet(
            nombre: 'Café UTB',
            onEnviar: (estrellas) async {
              calificacionEnviada = estrellas;
            },
          ),
        ),
      ),
    );

    // Verifica título
    expect(find.text('¿Cómo te fue con Café UTB?'), findsOneWidget);

    // Verifica aclaración
    expect(find.text('Se verá cuando califiquen los dos o en 3 días.'), findsOneWidget);

    // Verifica botón
    expect(find.text('Enviar calificación'), findsOneWidget);

    // Verifica semántica de estrellas (1 a 5)
    expect(find.bySemanticsLabel('1 estrella'), findsOneWidget);
    expect(find.bySemanticsLabel('2 estrellas'), findsOneWidget);
    expect(find.bySemanticsLabel('3 estrellas'), findsOneWidget);
    expect(find.bySemanticsLabel('4 estrellas'), findsOneWidget);
    expect(find.bySemanticsLabel('5 estrellas'), findsOneWidget);

    // Inicialmente 5 estrellas seleccionadas
    expect(find.text('5 de 5 estrellas'), findsOneWidget);

    // Toca en 4 estrellas
    await tester.tap(find.bySemanticsLabel('4 estrellas'));
    await tester.pump();
    expect(find.text('4 de 5 estrellas'), findsOneWidget);

    // Toca en Enviar calificación
    await tester.tap(find.text('Enviar calificación'));
    await tester.pump();

    expect(calificacionEnviada, 4);
  });
}
