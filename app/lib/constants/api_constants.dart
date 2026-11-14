class ApiConstants {
  // URL en producción en Render con HTTPS
  static const String baseUrlProduction = 'https://puntokampus-app.onrender.com/api/v1';

  // URL para desarrollo local (emulador Android usa 10.0.2.2, navegador web usa localhost)
  static const String baseUrlLocalAndroid = 'http://10.0.2.2:8000/api/v1';
  static const String baseUrlLocalWeb = 'http://127.0.0.1:8000/api/v1';

  // URL activa por defecto (apunta a la nube en Render)
  static String baseUrl = baseUrlProduction;

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
