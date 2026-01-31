import 'dart:convert';

import 'package:get/get.dart';

import '../../data/models/user_model.dart';
import 'secure_storage.dart';

class AuthService extends GetxService {
  final _token = RxnString();
  final _user = Rxn<UserModel>();
  String? _pendingRoute; // Store route to redirect after login

  String? get token => _token.value;
  UserModel? get user => _user.value;
  bool get isLoggedIn => token != null && token!.isNotEmpty;
  String? get pendingRoute => _pendingRoute;
  
  // Check if current user can access mobile app
  bool get canAccessMobileApp => user?.canAccessMobileApp ?? false;
  
  // Set pending route to redirect after login
  void setPendingRoute(String route) {
    _pendingRoute = route;
  }
  
  // Clear pending route
  void clearPendingRoute() {
    _pendingRoute = null;
  }

  Future<void> init() async {
    final storedToken = await SecureStorage.getToken();
    if (storedToken != null) {
      _token.value = storedToken;
    }
    final storedUser = await SecureStorage.getUser();
    if (storedUser != null) {
      final json = jsonDecode(storedUser) as Map<String, dynamic>;
      _user.value = UserModel.fromJson(json);
    }
  }

  Future<void> setSession(String token, UserModel user) async {
    _token.value = token;
    _user.value = user;
    await SecureStorage.saveToken(token);
    await SecureStorage.saveUser(jsonEncode(user.toJson()));
  }

  Future<void> clearSession() async {
    _token.value = null;
    _user.value = null;
    await SecureStorage.deleteToken();
    await SecureStorage.deleteUser();
  }

  /// Check if token is valid (not expired)
  /// This is a simple check - actual validation happens on the server
  bool get isTokenValid {
    if (token == null || token!.isEmpty) {
      return false;
    }
    // Token validation is done by the server
    // We just check if token exists
    return true;
  }

  /// Validate token with server
  /// Returns true if token is valid, false otherwise
  Future<bool> validateToken() async {
    if (!isTokenValid) {
      return false;
    }
    // Token validation is handled by Dio interceptor
    // If token is invalid, server will return 401 and interceptor will handle it
    return true;
  }
}
