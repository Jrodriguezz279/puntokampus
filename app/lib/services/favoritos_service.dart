import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

/// Servicio local de favoritos usando SharedPreferences.
/// Almacena IDs de tiendas y productos favoritos del usuario.
class FavoritosService {
  static const _keyTiendas = 'pk_favoritos_tiendas';
  static const _keyProductos = 'pk_favoritos_productos';

  // ─── Tiendas ───────────────────────────────────────────────────────────────

  static Future<Set<int>> getTiendasFavoritas() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_keyTiendas);
    if (raw == null) return {};
    try {
      final list = jsonDecode(raw) as List<dynamic>;
      return list.map((e) => e as int).toSet();
    } catch (_) {
      return {};
    }
  }

  static Future<void> toggleTienda(int tiendaId) async {
    final ids = await getTiendasFavoritas();
    if (ids.contains(tiendaId)) {
      ids.remove(tiendaId);
    } else {
      ids.add(tiendaId);
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyTiendas, jsonEncode(ids.toList()));
  }

  static Future<bool> esTiendaFavorita(int tiendaId) async {
    final ids = await getTiendasFavoritas();
    return ids.contains(tiendaId);
  }

  // ─── Productos ─────────────────────────────────────────────────────────────

  static Future<Set<int>> getProductosFavoritos() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_keyProductos);
    if (raw == null) return {};
    try {
      final list = jsonDecode(raw) as List<dynamic>;
      return list.map((e) => e as int).toSet();
    } catch (_) {
      return {};
    }
  }

  static Future<void> toggleProducto(int productoId) async {
    final ids = await getProductosFavoritos();
    if (ids.contains(productoId)) {
      ids.remove(productoId);
    } else {
      ids.add(productoId);
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyProductos, jsonEncode(ids.toList()));
  }

  static Future<bool> esProductoFavorito(int productoId) async {
    final ids = await getProductosFavoritos();
    return ids.contains(productoId);
  }

  static Future<void> clearAll() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyTiendas);
    await prefs.remove(_keyProductos);
  }
}
