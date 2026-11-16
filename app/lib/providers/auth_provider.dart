import 'package:flutter/foundation.dart';
import '../models/usuario.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  Usuario? _usuario;
  String? _token;
  bool _cargando = false;
  String? _error;

  Usuario? get usuario => _usuario;
  String? get token => _token;
  bool get cargando => _cargando;
  String? get error => _error;
  bool get tieneSesion => _token != null && _token!.isNotEmpty;

  Future<void> inicializar() async {
    _cargando = true;
    notifyListeners();
    try {
      _token = await StorageService.getToken();
      if (_token != null && _token!.isNotEmpty) {
        _usuario = await StorageService.getUsuario();
        // Refrescar perfil en segundo plano
        AuthService.getPerfil().then((user) {
          _usuario = user;
          notifyListeners();
        }).catchError((_) {});
      }
    } catch (_) {}
    _cargando = false;
    notifyListeners();
  }

  Future<bool> login(String correo, String contrasena) async {
    _cargando = true;
    _error = null;
    notifyListeners();
    try {
      final res = await AuthService.login(correo: correo, contrasena: contrasena);
      _token = res['token'] as String?;
      if (res.containsKey('usuario') && res['usuario'] is Map<String, dynamic>) {
        _usuario = Usuario.fromJson(res['usuario'] as Map<String, dynamic>);
      }
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

  Future<bool> solicitarCodigo(String correo) async {
    _cargando = true;
    _error = null;
    notifyListeners();
    try {
      await AuthService.solicitarCodigo(correo);
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

  Future<bool> verificarCodigo(String correo, String codigo, {String? contrasena}) async {
    _cargando = true;
    _error = null;
    notifyListeners();
    try {
      final res = await AuthService.verificarCodigo(correo, codigo, contrasena: contrasena);
      _token = res['token'] as String?;
      if (res.containsKey('usuario') && res['usuario'] is Map<String, dynamic>) {
        _usuario = Usuario.fromJson(res['usuario'] as Map<String, dynamic>);
      }
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

  Future<bool> solicitarRecuperacion(String correo) async {
    _cargando = true;
    _error = null;
    notifyListeners();
    try {
      await AuthService.solicitarRecuperacion(correo);
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

  Future<bool> recuperarContrasena(String correo, String codigo, String nuevaContrasena) async {
    _cargando = true;
    _error = null;
    notifyListeners();
    try {
      await AuthService.recuperarContrasena(
        correo: correo,
        codigo: codigo,
        nuevaContrasena: nuevaContrasena,
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

  Future<bool> actualizarPerfil({
    String? nombres,
    String? whatsapp,
    bool? mostrarWhatsapp,
    String? facultad,
    String? carrera,
    int? semestre,
    bool? aceptoPrivacidad,
  }) async {
    _cargando = true;
    _error = null;
    notifyListeners();
    try {
      _usuario = await AuthService.actualizarPerfil(
        nombres: nombres,
        whatsapp: whatsapp,
        mostrarWhatsapp: mostrarWhatsapp,
        facultad: facultad,
        carrera: carrera,
        semestre: semestre,
        aceptoPrivacidad: aceptoPrivacidad,
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

  Future<void> salir() async {
    _cargando = true;
    notifyListeners();
    await AuthService.salir();
    _usuario = null;
    _token = null;
    _cargando = false;
    notifyListeners();
  }

  void limpiarError() {
    _error = null;
    notifyListeners();
  }
}
