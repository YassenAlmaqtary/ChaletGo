import 'package:flutter/material.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:get/get.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'core/bindings/initial_binding.dart';
import 'core/theme/app_theme.dart';
import 'core/translations/app_translations.dart';
import 'core/controllers/language_controller.dart';
import 'core/controllers/theme_controller.dart';
import 'routes/app_pages.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await dotenv.load(fileName: '.env');
  } catch (e) {
    debugPrint('Failed to load .env file: $e');
  }

  // Initialize controllers before app starts
  final languageController = Get.put(LanguageController(), permanent: true);
  final themeController = Get.put(ThemeController(), permanent: true);

  runApp(MyApp(
    languageController: languageController,
    themeController: themeController,
  ));
}

class MyApp extends StatelessWidget {
  final LanguageController languageController;
  final ThemeController themeController;

  const MyApp({
    super.key,
    required this.languageController,
    required this.themeController,
  });

  @override
  Widget build(BuildContext context) {
    return Obx(() => GetMaterialApp(
          title: 'ChaletGo',
          debugShowCheckedModeBanner: false,
          initialBinding: InitialBinding(),
          initialRoute: AppPages.initial,
          getPages: AppPages.routes,
          translations: AppTranslations(),
          locale: languageController.currentLanguage.value == 'ar'
              ? const Locale('ar')
              : const Locale('en'),
          fallbackLocale: const Locale('ar'),
          theme: AppTheme.light,
          darkTheme: AppTheme.dark,
          themeMode: themeController.isDarkMode.value
              ? ThemeMode.dark
              : ThemeMode.light,
          defaultTransition: Transition.cupertino,
          transitionDuration: const Duration(milliseconds: 300),
          supportedLocales: const [
            Locale('en'),
            Locale('ar'),
          ],
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
        ));
  }
}
