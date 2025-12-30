import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();

  User? _user;
  String? _token;
  bool _isLoading = false;

  User? get user => _user;
  String? get token => _token;
  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;

  Future<void> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final authResponse = await _authService.login(email, password);
      _user = authResponse.currentUser;
      _token = authResponse.token;

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('jwt_token', _token!);
      if (_user != null) {
        await prefs.setString('username', _user!.username);
      }

      debugPrint("Login berhasil. User: ${_user?.username}");
    } catch (e) {
      debugPrint("Login gagal: $e");
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> register(String username, String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final authResponse = await _authService.register(
        username,
        email,
        password,
      );
      _user = authResponse.currentUser;
      _token = authResponse.token;

      final prefs = await SharedPreferences.getInstance();
      if (_token != null) {
        await prefs.setString('jwt_token', _token!);
      }
      if (_user != null) {
        await prefs.setString('username', _user!.username);
      }
    } catch (e) {
      debugPrint("Register gagal: $e");
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('jwt_token');
    await prefs.remove('username');
    _user = null;
    _token = null;
    notifyListeners();
  }

  Future<void> loadUser() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('jwt_token');
    final username = prefs.getString('username');
    if (username != null) {
      _user = User(id: 0, username: username, email: '');
    }
    notifyListeners();
  }
}
