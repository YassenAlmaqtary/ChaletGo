import 'package:get/get.dart';
import '../../../core/services/auth_service.dart';
import '../../../core/controllers/language_controller.dart';
import '../../../core/controllers/theme_controller.dart';

class SettingsController extends GetxController {
  final AuthService authService = Get.find<AuthService>();
  final LanguageController languageController = Get.find<LanguageController>();
  final ThemeController themeController = Get.find<ThemeController>();

  // Settings state
  final RxBool notificationsEnabled = true.obs;

  @override
  void onInit() {
    super.onInit();
    _loadSettings();
  }

  void _loadSettings() {
    // Load saved settings from storage if needed
    // For now, using default values
  }

  void toggleNotifications(bool value) {
    notificationsEnabled.value = value;
    // Save to storage
  }

  void toggleDarkMode(bool value) {
    themeController.setDarkMode(value);
  }

  void changeLanguage(String lang) {
    languageController.changeLanguage(lang);
  }

  bool get isDarkMode => themeController.isDarkMode.value;
  String get currentLanguage => languageController.currentLanguage.value;
  String get currentLanguageName => languageController.currentLanguageName;
}

