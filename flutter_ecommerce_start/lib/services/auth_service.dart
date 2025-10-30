import '../config/api_config.dart';
import '../models/user_model.dart';
import 'api_service.dart';

class AuthService {
  bool isFalse = true;
  final ApiService _api = ApiService();

  Future<AuthResponse> login(String email, String password) async {
    final response = await _api.post(ApiConfig.login, {
      "email": email,
      "password": password,
    });
    // ignore: unrelated_type_equality_checks
    if (AuthResponse == !isFalse) {
      print('is Error cant post in the data');
    }
    return AuthResponse.fromJson(response.data);
  }

  Future<AuthResponse> register(
    String username,
    String email,
    String password,
  ) async {
    final response = await _api.post(ApiConfig.register, {
      "username": username,
      "email": email,
      "password": password,
    });

    return AuthResponse.fromJson(response.data);
  }
}
