import 'dart:convert';
import 'package:http/http.dart' as http;
import '../constants/api_constants.dart';
import 'storage_service.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

class ApiService {
  static Future<Map<String, String>> _getHeaders({bool requiresAuth = true}) async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (requiresAuth) {
      final token = await StorageService.getToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  static Uri _buildUri(String endpoint, [Map<String, String>? queryParams]) {
    final cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/$endpoint';
    final urlString = '${ApiConstants.baseUrl}$cleanEndpoint';
    return Uri.parse(urlString).replace(queryParameters: queryParams);
  }

  static Future<dynamic> get(String endpoint, {Map<String, String>? queryParams, bool requiresAuth = true}) async {
    try {
      final uri = _buildUri(endpoint, queryParams);
      final headers = await _getHeaders(requiresAuth: requiresAuth);
      final response = await http.get(uri, headers: headers);
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
    }
  }

  static Future<dynamic> post(String endpoint, {Map<String, dynamic>? body, bool requiresAuth = true}) async {
    try {
      final uri = _buildUri(endpoint);
      final headers = await _getHeaders(requiresAuth: requiresAuth);
      final response = await http.post(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
    }
  }

  static Future<dynamic> put(String endpoint, {Map<String, dynamic>? body, bool requiresAuth = true}) async {
    try {
      final uri = _buildUri(endpoint);
      final headers = await _getHeaders(requiresAuth: requiresAuth);
      final response = await http.put(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
    }
  }

  static Future<dynamic> delete(String endpoint, {Map<String, dynamic>? body, bool requiresAuth = true}) async {
    try {
      final uri = _buildUri(endpoint);
      final headers = await _getHeaders(requiresAuth: requiresAuth);
      final response = await http.delete(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
    }
  }

  static dynamic _processResponse(http.Response response) {
    dynamic decoded;
    try {
      decoded = jsonDecode(response.body);
    } catch (_) {
      decoded = null;
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    String message = 'Algo falló de nuestro lado. Inténtalo en unos minutos.';
    dynamic errors;

    if (decoded is Map<String, dynamic>) {
      if (decoded.containsKey('mensaje')) {
        message = decoded['mensaje'].toString();
      } else if (decoded.containsKey('message')) {
        message = decoded['message'].toString();
      }
      if (decoded.containsKey('errores')) {
        errors = decoded['errores'];
      } else if (decoded.containsKey('errors')) {
        errors = decoded['errors'];
      }
    }

    throw ApiException(message, statusCode: response.statusCode, errors: errors);
  }
}
