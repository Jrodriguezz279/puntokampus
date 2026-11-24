import 'package:flutter/foundation.dart';
import '../models/tienda.dart';
import '../models/producto.dart';
import '../services/favoritos_service.dart';

/// Provider que mantiene en memoria los favoritos del usuario
/// y los sincroniza con SharedPreferences.
class FavoritosProvider extends ChangeNotifier {
  Set<int> _tiendasFav = {};
  Set<int> _productosFav = {};

  Set<int> get tiendasFav => _tiendasFav;
  Set<int> get productosFav => _productosFav;

  bool esTiendaFav(int id) => _tiendasFav.contains(id);
  bool esProductoFav(int id) => _productosFav.contains(id);

  Future<void> cargar() async {
    _tiendasFav = await FavoritosService.getTiendasFavoritas();
    _productosFav = await FavoritosService.getProductosFavoritos();
    notifyListeners();
  }

  Future<void> toggleTienda(int id) async {
    await FavoritosService.toggleTienda(id);
    if (_tiendasFav.contains(id)) {
      _tiendasFav.remove(id);
    } else {
      _tiendasFav.add(id);
    }
    notifyListeners();
  }

  Future<void> toggleProducto(int id) async {
    await FavoritosService.toggleProducto(id);
    if (_productosFav.contains(id)) {
      _productosFav.remove(id);
    } else {
      _productosFav.add(id);
    }
    notifyListeners();
  }

  // ─── Listas filtradas de modelos guardados ──────────────────────────────────

  List<Tienda> tiendasFavoritas(List<Tienda> todas) =>
      todas.where((t) => _tiendasFav.contains(t.id)).toList();

  List<Producto> productosFavoritos(List<Producto> todos) =>
      todos.where((p) => _productosFav.contains(p.id)).toList();
}
