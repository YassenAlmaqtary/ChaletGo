import 'dart:convert';

import 'package:get/get.dart';

import '../../data/models/user_model.dart';
import 'secure_storage.dart';

class AuthService extends GetxService {
  final _token = RxnString();
  final _user = Rxn<UserModel>();

  String? get token => _token.value;
  UserModel? get user => _user.value;
  bool get isLoggedIn => token != null && token!.isNotEmpty;

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
}
