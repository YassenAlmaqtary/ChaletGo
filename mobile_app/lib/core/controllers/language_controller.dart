import 'package:get/get.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class LanguageController extends GetxController {
  final RxString currentLanguage = 'ar'.obs;
  final _storage = const FlutterSecureStorage();
  static const String _languageKey = 'app_language';

  @override
  void onInit() {
    super.onInit();
    _loadLanguage();
  }

  Future<void> _loadLanguage() async {
    try {
      final savedLanguage = await _storage.read(key: _languageKey);
      if (savedLanguage != null && (savedLanguage == 'ar' || savedLanguage == 'en')) {
        currentLanguage.value = savedLanguage;
        _updateLocale(savedLanguage);
      } else {
        // Use default language
        currentLanguage.value = 'ar';
        _updateLocale('ar');
      }
    } catch (e) {
      // Use default language
      currentLanguage.value = 'ar';
      _updateLocale('ar');
    }
  }

  Future<void> changeLanguage(String languageCode) async {
    if (languageCode != currentLanguage.value) {
      currentLanguage.value = languageCode;
      await _storage.write(key: _languageKey, value: languageCode);
      _updateLocale(languageCode);
    }
  }

  void _updateLocale(String languageCode) {
    final locale = Locale(languageCode);
    Get.updateLocale(locale);
  }

  String get currentLanguageName {
    return currentLanguage.value == 'ar' ? 'العربية' : 'English';
  }

  bool get isArabic => currentLanguage.value == 'ar';
  bool get isEnglish => currentLanguage.value == 'en';
}

