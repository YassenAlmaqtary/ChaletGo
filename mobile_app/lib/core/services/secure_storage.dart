import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../constants/app_constants.dart';

class SecureStorage {
  static const FlutterSecureStorage _storage = FlutterSecureStorage();

  static Future<void> saveToken(String token) async {
    await _storage.write(key: AppConstants.authTokenKey, value: token);
  }

  static Future<String?> getToken() async {
    return await _storage.read(key: AppConstants.authTokenKey);
  }

  static Future<void> deleteToken() async {
    await _storage.delete(key: AppConstants.authTokenKey);
  }

  static Future<void> saveUser(String data) async {
    await _storage.write(key: AppConstants.userDataKey, value: data);
  }

  static Future<String?> getUser() async {
    return await _storage.read(key: AppConstants.userDataKey);
  }

  static Future<void> deleteUser() async {
    await _storage.delete(key: AppConstants.userDataKey);
  }
}
