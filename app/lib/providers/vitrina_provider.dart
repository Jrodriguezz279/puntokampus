import 'package:flutter/foundation.dart';
import '../models/categoria.dart';
import '../models/tienda.dart';
import '../models/producto.dart';
import '../services/vitrina_service.dart';

class VitrinaProvider extends ChangeNotifier {
  List<Categoria> _categorias = [];
  List<PuntoEntrega> _puntosEntrega = [];
  List<Tienda> _tiendas = [];
  List<Producto> _productos = [];
  List<Producto> _ofertas = [];
  bool _cargando = false;
  String? _error;
  int? _categoriaSeleccionadaId;
  String _busquedaActual = '';
  String? _instagramUrl;
  String? _tiktokUrl;
  String? _facebookUrl;

  List<Categoria> get categorias => _categorias;
  List<PuntoEntrega> get puntosEntrega => _puntosEntrega;
  List<Tienda> get tiendas => _tiendas;
  List<Producto> get productos => _productos;
  List<Producto> get ofertas => _ofertas;
  bool get cargando => _cargando;
  String? get error => _error;
  int? get categoriaSeleccionadaId => _categoriaSeleccionadaId;
  String get busquedaActual => _busquedaActual;
  String? get instagramUrl => _instagramUrl;
  String? get tiktokUrl => _tiktokUrl;
  String? get facebookUrl => _facebookUrl;

  Future<void> cargarInicial() async {
    _cargando = true;
    _error = null;
    notifyListeners();

    try {
      final res = await Future.wait([
        VitrinaService.getCategorias(),
        VitrinaService.getPuntosEntrega(),
        VitrinaService.getTiendas(),
        VitrinaService.getProductos(),
        VitrinaService.getConfiguracionPublica(),
      ]);

      _categorias = res[0] as List<Categoria>;
      _puntosEntrega = res[1] as List<PuntoEntrega>;
      _tiendas = res[2] as List<Tienda>;
      _productos = res[3] as List<Producto>;
      _ofertas = _productos.where((p) => p.tieneDescuento && p.disponible).toList();
      final config = res[4] as Map<String, String?>;
      _instagramUrl = config['instagram_url'];
      _tiktokUrl = config['tiktok_url'];
      _facebookUrl = config['facebook_url'];
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
      final res = await Future.wait([
        VitrinaService.getTiendas(
          categoriaId: _categoriaSeleccionadaId,
          busqueda: _busquedaActual,
        ),
        VitrinaService.getProductos(
          categoriaId: _categoriaSeleccionadaId,
          busqueda: _busquedaActual,
        ),
      ]);
      _tiendas = res[0] as List<Tienda>;
      _productos = res[1] as List<Producto>;
      _ofertas = _productos.where((p) => p.tieneDescuento && p.disponible).toList();
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
      final res = await Future.wait([
        VitrinaService.getTiendas(
          categoriaId: _categoriaSeleccionadaId,
          busqueda: _busquedaActual,
        ),
        VitrinaService.getProductos(
          categoriaId: _categoriaSeleccionadaId,
          busqueda: _busquedaActual,
        ),
      ]);
      _tiendas = res[0] as List<Tienda>;
      _productos = res[1] as List<Producto>;
      _ofertas = _productos.where((p) => p.tieneDescuento && p.disponible).toList();
      _cargando = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
    }
  }

  Future<void> limpiarFiltros() async {
    _categoriaSeleccionadaId = null;
    _busquedaActual = '';
    await cargarInicial();
  }
}
