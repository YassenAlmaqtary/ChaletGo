import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:get/get.dart';

class AppConfig {
  AppConfig._();

  static String get apiBaseUrl {
    final envValue = dotenv.isInitialized ? dotenv.env['API_BASE_URL'] : null;
    final baseUrl = (envValue == null || envValue.trim().isEmpty)
        ? _defaultBaseUrl
        : envValue.trim();

    if (GetPlatform.isAndroid &&
        (baseUrl.contains('127.0.0.1') || baseUrl.contains('localhost'))) {
      return baseUrl.replaceFirst(
          RegExp(r'127\.0\.0\.1|localhost'), '10.0.2.2');
    }

    return baseUrl;
  }

  static String get defaultLocale {
    if (!dotenv.isInitialized) {
      return 'ar';
    }
    return dotenv.env['DEFAULT_LOCALE'] ?? 'ar';
  }

  static String get _defaultBaseUrl {
    if (GetPlatform.isAndroid) {
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://127.0.0.1:8000/api';
  }
}
