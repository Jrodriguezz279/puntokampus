import '../models/usuario.dart';
import 'api_service.dart';
import 'storage_service.dart';

class AuthService {
  // Iniciar sesión con correo y contraseña
  static Future<Map<String, dynamic>> login({
    required String correo,
    required String contrasena,
    String? dispositivo,
  }) async {
    final response = await ApiService.post('/auth/entrar', body: {
      'correo': correo.trim().toLowerCase(),
      'contrasena': contrasena,
      if (dispositivo != null) 'dispositivo': dispositivo,
    }, requiresAuth: false);

    final data = (response is Map<String, dynamic>) ? response : <String, dynamic>{};
    if (data.containsKey('token')) {
      final token = data['token'] as String;
      await StorageService.saveToken(token);
    }
    if (data.containsKey('usuario') && data['usuario'] is Map<String, dynamic>) {
      final user = Usuario.fromJson(data['usuario'] as Map<String, dynamic>);
      await StorageService.saveUsuario(user);
    }

    return data;
  }

  // P02: Solicitar código de 6 dígitos
  static Future<void> solicitarCodigo(String correo) async {
    await ApiService.post('/auth/codigo', body: {
      'correo': correo.trim().toLowerCase(),
    }, requiresAuth: false);
  }

  // P03: Verificar código de 6 dígitos y opcionalmente establecer contraseña
  static Future<Map<String, dynamic>> verificarCodigo(
    String correo,
    String codigo, {
    String? contrasena,
    String? dispositivo,
  }) async {
    final body = {
      'correo': correo.trim().toLowerCase(),
      'codigo': codigo.trim(),
      if (contrasena != null && contrasena.isNotEmpty) 'contrasena': contrasena,
      if (dispositivo != null) 'dispositivo': dispositivo,
    };

    final response = await ApiService.post('/auth/verificar', body: body, requiresAuth: false);

    final data = (response is Map<String, dynamic>) ? response : <String, dynamic>{};
    if (data.containsKey('token')) {
      final token = data['token'] as String;
      await StorageService.saveToken(token);
    }
    if (data.containsKey('usuario') && data['usuario'] is Map<String, dynamic>) {
      final user = Usuario.fromJson(data['usuario'] as Map<String, dynamic>);
      await StorageService.saveUsuario(user);
    }

    return data;
  }

  // Solicitar código para restablecer contraseña
  static Future<void> solicitarRecuperacion(String correo) async {
    await ApiService.post('/auth/codigo-recuperacion', body: {
      'correo': correo.trim().toLowerCase(),
    }, requiresAuth: false);
  }

  // Restablecer contraseña con código
  static Future<void> recuperarContrasena({
    required String correo,
    required String codigo,
    required String nuevaContrasena,
  }) async {
    await ApiService.post('/auth/recuperar', body: {
      'correo': correo.trim().toLowerCase(),
      'codigo': codigo.trim(),
      'contrasena': nuevaContrasena,
    }, requiresAuth: false);
  }

  // P04 / P13: Obtener perfil del usuario actual
  static Future<Usuario> getPerfil() async {
    final response = await ApiService.get('/yo');
    final data = response as Map<String, dynamic>;
    final user = Usuario.fromJson(data['usuario'] ?? data);
    await StorageService.saveUsuario(user);
    return user;
  }

  // P04 / P13: Actualizar perfil y aceptar privacidad
  static Future<Usuario> actualizarPerfil({
    String? nombres,
    String? whatsapp,
    bool? mostrarWhatsapp,
    String? facultad,
    String? carrera,
    int? semestre,
    bool? aceptoPrivacidad,
  }) async {
    final Map<String, dynamic> body = {};
    if (nombres != null) body['nombres'] = nombres;
    if (whatsapp != null) body['whatsapp'] = whatsapp;
    if (mostrarWhatsapp != null) body['mostrar_whatsapp'] = mostrarWhatsapp;
    if (facultad != null) body['facultad'] = facultad;
    if (carrera != null) body['carrera'] = carrera;
    if (semestre != null) body['semestre'] = semestre;
    if (aceptoPrivacidad != null) body['acepto_privacidad'] = aceptoPrivacidad;

    final response = await ApiService.put('/yo', body: body);
    final data = response as Map<String, dynamic>;
    final user = Usuario.fromJson(data['usuario'] ?? data);
    await StorageService.saveUsuario(user);
    return user;
  }

  // Salir de sesión
  static Future<void> salir() async {
    try {
      await ApiService.post('/auth/salir');
    } catch (_) {}
    await StorageService.clearAll();
  }

  // P22: Solicitar código de borrado definitivo de cuenta
  static Future<void> solicitarCodigoBorrado() async {
    await ApiService.post('/yo/codigo-borrado');
  }

  // P22: Borrar cuenta definitivamente
  static Future<void> borrarCuenta(String codigo) async {
    await ApiService.delete('/yo', body: {'codigo': codigo});
    await StorageService.clearAll();
  }
}
