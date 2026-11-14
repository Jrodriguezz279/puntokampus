import 'package:flutter/foundation.dart';
import '../models/categoria.dart';
import '../models/tienda.dart';
import '../services/vitrina_service.dart';

class VitrinaProvider extends ChangeNotifier {
  List<Categoria> _categorias = [];
  List<PuntoEntrega> _puntosEntrega = [];
  List<Tienda> _tiendas = [];
  bool _cargando = false;
  String? _error;
  int? _categoriaSeleccionadaId;
  String _busquedaActual = '';

  List<Categoria> get categorias => _categorias;
  List<PuntoEntrega> get puntosEntrega => _puntosEntrega;
  List<Tienda> get tiendas => _tiendas;
  bool get cargando => _cargando;
  String? get error => _error;
  int? get categoriaSeleccionadaId => _categoriaSeleccionadaId;
  String get busquedaActual => _busquedaActual;

  Future<void> cargarInicial() async {
    _cargando = true;
    _error = null;
    notifyListeners();

    try {
      final res = await Future.wait([
        VitrinaService.getCategorias(),
        VitrinaService.getPuntosEntrega(),
        VitrinaService.getTiendas(),
      ]);

      _categorias = res[0] as List<Categoria>;
      _puntosEntrega = res[1] as List<PuntoEntrega>;
      _tiendas = res[2] as List<Tienda>;
      _cargando = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
    }
  }

  Future<void> filtrarPorCategoria(int? categoriaId) async {
    _categoriaSeleccionadaId = categoriaId;
    _cargando = true;
    notifyListeners();

    try {
      _tiendas = await VitrinaService.getTiendas(
        categoriaId: _categoriaSeleccionadaId,
        busqueda: _busquedaActual,
      );
      _cargando = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
    }
  }

  Future<void> buscar(String query) async {
    _busquedaActual = query;
    _cargando = true;
    notifyListeners();

    try {
      _tiendas = await VitrinaService.getTiendas(
        categoriaId: _categoriaSeleccionadaId,
        busqueda: _busquedaActual,
      );
      _cargando = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
    }
  }
}
