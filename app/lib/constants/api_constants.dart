class ApiConstants {
  // URL en producción en Render con HTTPS
  static const String baseUrlProduction = 'https://puntokampus-app.onrender.com/api/v1';

  // URL para desarrollo local (emulador Android usa 10.0.2.2, navegador web usa localhost)
  static const String baseUrlLocalAndroid = 'http://10.0.2.2:8000/api/v1';
  static const String baseUrlLocalWeb = 'http://127.0.0.1:8000/api/v1';

  // URL activa por defecto (apunta a la nube en Render)
  static String baseUrl = baseUrlProduction;

  /// Servidor raíz sin sufijo /api/v1 (ej: https://puntokampus-app.onrender.com)
  static String get serverBaseUrl {
    return baseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
  }

  /// Convierte cualquier URL de imagen (relativa, localhost, etc.) a una URL pública alcanzable por el dispositivo
  static String? formatearUrlImagen(String? url) {
    if (url == null || url.trim().isEmpty) return null;
    final u = url.trim();

    // Si viene como ruta relativa /storage/... o storage/...
    if (u.startsWith('/')) {
      return '$serverBaseUrl$u';
    }
    if (u.startsWith('storage/')) {
      return '$serverBaseUrl/$u';
    }

    // Si viene con localhost o 127.0.0.1 (desde seeders o servidor local)
    if (u.contains('localhost') || u.contains('127.0.0.1')) {
      final uri = Uri.tryParse(u);
      if (uri != null && uri.path.isNotEmpty) {
        return '$serverBaseUrl${uri.path}';
      }
    }

    return u;
  }

  // Endpoints de Autenticación
  static const String solicitarCodigo = '/auth/codigo';
  static const String verificarCodigo = '/auth/verificar';
  static const String salir = '/auth/salir';
  static const String perfil = '/yo';
  static const String codigoBorrado = '/yo/codigo-borrado';
  static const String borrarCuenta = '/yo';

  // Endpoints de Vitrina
  static const String categorias = '/categorias';
  static const String puntosEntrega = '/puntos-entrega';
  static const String tiendas = '/tiendas';
  static const String productos = '/productos';

  // Endpoints de Pedidos
  static const String pedidos = '/pedidos';
  static const String misPedidos = '/mis-pedidos';
  static const String pedidosTienda = '/mi-tienda/pedidos';

  // Endpoints de Mi Tienda
  static const String miTienda = '/mi-tienda';
  static const String miTiendaLogo = '/mi-tienda/logo';
  static const String miTiendaPortada = '/mi-tienda/portada';
  static const String misProductos = '/mi-tienda/productos';
  static const String premiumSolicitud = '/premium/solicitud';
  static const String reportes = '/reportes';
  static const String salud = '/healthcheck';
}
