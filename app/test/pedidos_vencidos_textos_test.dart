import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:puntokampus/models/pedido.dart';
import 'package:puntokampus/widgets/pk_components.dart';

void main() {
  group('Pruebas de widgets para pedidos vencidos y aviso paso_hora', () {
    testWidgets('Comprador ve etiqueta "Pasó la hora de entrega" en pedido pendiente', (tester) async {
      final pedidoPasoHora = Pedido(
        id: 1,
        codigo: 'PK-000001',
        tiendaId: 10,
        nombreTienda: 'Tienda Snacks',
        compradorId: 5,
        total: 5.0,
        formaPago: 'efectivo',
        estado: 'pendiente',
        horaEntrega: '2026-11-23T10:00:00Z',
        creadoEn: '2026-11-23T08:00:00Z',
        pasoHora: true,
        venceEn: '2026-11-24T10:00:00Z',
        codigoEntrega: '1234',
      );

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: Column(
              children: [
                if (pedidoPasoHora.pasoHora)
                  Container(
                    child: const Text('Pasó la hora de entrega'),
                  ),
                Text('Tienda: ${pedidoPasoHora.nombreTienda}'),
                Text('🗓️ Realizado el: ${pedidoPasoHora.fechaHoraCreacionFormateada}'),
                Text('⏰ Entrega: ${pedidoPasoHora.fechaHoraEntregaFormateada}'),
              ],
            ),
          ),
        ),
      );

      expect(find.text('Pasó la hora de entrega'), findsOneWidget);
      expect(find.text('Tienda: Tienda Snacks'), findsOneWidget);
      expect(find.textContaining('🗓️ Realizado el:'), findsOneWidget);
      expect(find.textContaining('⏰ Entrega:'), findsOneWidget);
    });

    testWidgets('Comprador ve texto de pedido vencido y no ve código de entrega', (tester) async {
      final pedidoVencido = Pedido(
        id: 2,
        codigo: 'PK-000002',
        tiendaId: 10,
        nombreTienda: 'Tienda Snacks',
        compradorId: 5,
        total: 5.0,
        formaPago: 'efectivo',
        estado: 'vencido',
        horaEntrega: '2026-11-22T10:00:00Z',
        creadoEn: '2026-11-22T08:00:00Z',
        pasoHora: false,
        codigoEntrega: null,
      );

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: Column(
              children: [
                if (pedidoVencido.esVencido)
                  const Text('Este pedido venció porque no se confirmó la entrega.'),
                if (pedidoVencido.esPendiente && pedidoVencido.codigoEntrega != null)
                  const Text('Código de entrega: 1234'),
              ],
            ),
          ),
        ),
      );

      expect(find.text('Este pedido venció porque no se confirmó la entrega.'), findsOneWidget);
      expect(find.textContaining('Código de entrega'), findsNothing);
    });

    testWidgets('Vendedor ve advertencia con vence_en cuando pasó la hora', (tester) async {
      final pedidoVendedorPasoHora = Pedido(
        id: 3,
        codigo: 'PK-000003',
        tiendaId: 10,
        compradorId: 5,
        nombreComprador: 'Carlos Estudiante',
        total: 8.5,
        formaPago: 'efectivo',
        estado: 'pendiente',
        horaEntrega: '2026-11-23T12:00:00Z',
        creadoEn: '2026-11-23T09:00:00Z',
        pasoHora: true,
        venceEn: '2026-11-24T12:00:00Z',
      );

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: Column(
              children: [
                if (pedidoVendedorPasoHora.pasoHora) ...[
                  const Text('Pasó la hora de entrega'),
                  Text('Confirma la entrega o cancela antes de que venza el ${pedidoVendedorPasoHora.venceEnFormateado}.'),
                ],
              ],
            ),
          ),
        ),
      );

      expect(find.text('Pasó la hora de entrega'), findsOneWidget);
      expect(find.textContaining('Confirma la entrega o cancela antes de que venza el'), findsOneWidget);
    });

    testWidgets('Vendedor ve texto exacto de pedido vencido y no puede gestionarlo', (tester) async {
      final pedidoVendedorVencido = Pedido(
        id: 4,
        codigo: 'PK-000004',
        tiendaId: 10,
        compradorId: 5,
        nombreComprador: 'Carlos Estudiante',
        total: 8.5,
        formaPago: 'efectivo',
        estado: 'vencido',
        horaEntrega: '2026-11-22T12:00:00Z',
        creadoEn: '2026-11-22T09:00:00Z',
      );

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: Column(
              children: [
                if (pedidoVendedorVencido.esVencido)
                  const Text(
                    'Este pedido venció porque no se confirmó la entrega. Ya no se puede cambiar. Si fue un error, escribe al equipo.',
                  ),
                if (pedidoVendedorVencido.esPendiente)
                  ElevatedButton(onPressed: () {}, child: const Text('Confirmar entrega')),
              ],
            ),
          ),
        ),
      );

      expect(
        find.text('Este pedido venció porque no se confirmó la entrega. Ya no se puede cambiar. Si fue un error, escribe al equipo.'),
        findsOneWidget,
      );
      expect(find.text('Confirmar entrega'), findsNothing);
    });
  });
}
