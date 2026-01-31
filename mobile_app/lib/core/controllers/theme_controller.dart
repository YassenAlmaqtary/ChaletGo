import 'package:get/get.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../theme/app_theme.dart';

class ThemeController extends GetxController {
  final RxBool isDarkMode = false.obs;
  final _storage = const FlutterSecureStorage();
  static const String _themeKey = 'app_theme_mode';

  @override
  void onInit() {
    super.onInit();
    _loadTheme();
  }

  Future<void> _loadTheme() async {
    try {
      final savedTheme = await _storage.read(key: _themeKey);
      if (savedTheme != null) {
        isDarkMode.value = savedTheme == 'dark';
        _updateTheme();
      }
    } catch (e) {
      // Use default theme (light)
      isDarkMode.value = false;
    }
  }

  Future<void> toggleTheme() async {
    isDarkMode.value = !isDarkMode.value;
    await _storage.write(
      key: _themeKey,
      value: isDarkMode.value ? 'dark' : 'light',
    );
    _updateTheme();
  }

  Future<void> setDarkMode(bool value) async {
    if (isDarkMode.value != value) {
      isDarkMode.value = value;
      await _storage.write(
        key: _themeKey,
        value: value ? 'dark' : 'light',
      );
      _updateTheme();
    }
  }

  void _updateTheme() {
    Get.changeThemeMode(
      isDarkMode.value ? ThemeMode.dark : ThemeMode.light,
    );
  }

  ThemeData get currentTheme {
    return isDarkMode.value ? AppTheme.dark : AppTheme.light;
  }
}


