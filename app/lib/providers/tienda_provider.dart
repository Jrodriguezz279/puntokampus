import 'package:flutter/foundation.dart';
import '../models/tienda.dart';
import '../services/tienda_service.dart';

class TiendaProvider extends ChangeNotifier {
  Tienda? _miTienda;
  bool _cargando = false;
  String? _error;

  Tienda? get miTienda => _miTienda;
  bool get cargando => _cargando;
  String? get error => _error;
  bool get tieneTienda => _miTienda != null;

  Future<void> cargarMiTienda() async {
    _cargando = true;
    _error = null;
    notifyListeners();

    try {
      _miTienda = await TiendaService.getMiTienda();
      _cargando = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
    }
  }

  Future<bool> guardarTienda({
    required String nombre,
    required String descripcion,
    required int categoria1Id,
    int? categoria2Id,
    required int puntoEntregaId,
    required String horario,
    String? whatsapp,
  }) async {
    _cargando = true;
    _error = null;
    notifyListeners();

    try {
      final esNueva = _miTienda == null;
      _miTienda = await TiendaService.guardarTienda(
        nombre: nombre,
        descripcion: descripcion,
        categoria1Id: categoria1Id,
        categoria2Id: categoria2Id,
        puntoEntregaId: puntoEntregaId,
        horario: horario,
        whatsapp: whatsapp,
        esCreacion: esNueva,
      );
      _cargando = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> crearProducto({
    required String nombre,
    required String descripcion,
    required double precio,
    bool disponible = true,
  }) async {
    _cargando = true;
    notifyListeners();
    try {
      await TiendaService.crearProducto(
        nombre: nombre,
        descripcion: descripcion,
        precio: precio,
        disponible: disponible,
      );
      await cargarMiTienda();
      return true;
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> actualizarProducto(
    int productoId, {
    required String nombre,
    required String descripcion,
    required double precio,
    bool disponible = true,
  }) async {
    _cargando = true;
    notifyListeners();
    try {
      await TiendaService.actualizarProducto(
        productoId,
        nombre: nombre,
        descripcion: descripcion,
        precio: precio,
        disponible: disponible,
      );
      await cargarMiTienda();
      return true;
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> eliminarProducto(int productoId) async {
    _cargando = true;
    notifyListeners();
    try {
      await TiendaService.eliminarProducto(productoId);
      await cargarMiTienda();
      return true;
    } catch (e) {
      _error = e.toString();
      _cargando = false;
      notifyListeners();
      return false;
    }
  }
}
