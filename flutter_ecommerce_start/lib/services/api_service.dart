import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  final Dio _dio = Dio();

  ApiService() {
    _dio.options.connectTimeout = const Duration(seconds: 10);
    _dio.options.receiveTimeout = const Duration(seconds: 10);
  }

  Future<void> _setAuthHeader() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('jwt_token');
    if (token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  Future<Response> get(String url) async {
    await _setAuthHeader();
    return await _dio.get(url);
  }

  Future<Response> post(String url, Map<String, dynamic> data) async {
    await _setAuthHeader();
    return await _dio.post(url, data: data);
  }

  Future<Response> put(String url, Map<String, dynamic> data) async {
    await _setAuthHeader();
    return await _dio.put(url, data: data);
  }

  Future<Response> delete(String url) async {
    await _setAuthHeader();
    return await _dio.delete(url);
  }
}
