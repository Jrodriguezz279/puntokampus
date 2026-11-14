import 'package:flutter/foundation.dart';
import '../models/producto.dart';
import '../models/tienda.dart';
import '../models/pedido.dart';

class CartProvider extends ChangeNotifier {
  final Map<int, CartItem> _items = {};
  int? _tiendaId;
  String? _tiendaNombre;

  Map<int, CartItem> get items => {..._items};
  int get itemCount => _items.values.fold(0, (sum, i) => sum + i.cantidad);
  double get total => _items.values.fold(0.0, (sum, i) => sum + i.subtotal);
  int? get tiendaId => _tiendaId;
  String? get tiendaNombre => _tiendaNombre;
  bool get isEmpty => _items.isEmpty;

  // Agregar al carrito verificando tienda única
  bool agregarProducto(Producto producto, Tienda tienda, {int cantidad = 1}) {
    if (_tiendaId != null && _tiendaId != tienda.id && _items.isNotEmpty) {
      // Pertenece a otra tienda, requiere confirmación
      return false;
    }

    _tiendaId = tienda.id;
    _tiendaNombre = tienda.nombre;

    if (_items.containsKey(producto.id)) {
      _items[producto.id]!.cantidad += cantidad;
      if (_items[producto.id]!.cantidad > 20) {
        _items[producto.id]!.cantidad = 20; // Límite máximo de 20 por producto
      }
    } else {
      _items[producto.id] = CartItem(
        producto: producto,
        cantidad: cantidad.clamp(1, 20),
      );
    }

    notifyListeners();
    return true;
  }

  void forzarAgregar(Producto producto, Tienda tienda, {int cantidad = 1}) {
    vaciar();
    _tiendaId = tienda.id;
    _tiendaNombre = tienda.nombre;
    _items[producto.id] = CartItem(producto: producto, cantidad: cantidad.clamp(1, 20));
    notifyListeners();
  }

  void actualizarCantidad(int productoId, int cantidad) {
    if (!_items.containsKey(productoId)) return;
    if (cantidad <= 0) {
      _items.remove(productoId);
      if (_items.isEmpty) {
        _tiendaId = null;
        _tiendaNombre = null;
      }
    } else {
      _items[productoId]!.cantidad = cantidad.clamp(1, 20);
    }
    notifyListeners();
  }

  void eliminarItem(int productoId) {
    _items.remove(productoId);
    if (_items.isEmpty) {
      _tiendaId = null;
      _tiendaNombre = null;
    }
    notifyListeners();
  }

  void vaciar() {
    _items.clear();
    _tiendaId = null;
    _tiendaNombre = null;
    notifyListeners();
  }

  List<Map<String, dynamic>> toOrderItems() {
    return _items.values.map((item) => {
      'producto_id': item.producto.id,
      'cantidad': item.cantidad,
    }).toList();
  }
}
