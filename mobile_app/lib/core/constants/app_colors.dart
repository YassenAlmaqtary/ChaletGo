import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/theme_controller.dart';

class AppColors {
  AppColors._();

  static const Color primary = Color(0xFF0FA3B1);
  static const Color secondary = Color(0xFF2EC4B6);
  static const Color accent = Color(0xFFFFD166);
  static const Color dark = Color(0xFF102542);
  static const Color muted = Color(0xFF4A5663);
  static const Color surface = Colors.white;
  static const Color softBlue = Color(0xFFE6F7F8);

  // Dark mode colors
  static const Color darkPrimary = Color(0xFF2EC4B6);
  static const Color darkSecondary = Color(0xFF0FA3B1);
  static const Color darkAccent = Color(0xFFFFD166);
  static const Color darkBackground = Color(0xFF121212);
  static const Color darkSurface = Color(0xFF1E1E1E);
  static const Color darkMuted = Color(0xFFB0B0B0);

  // Light mode gradients
  static const LinearGradient backgroundGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [Color(0xFFF5F7FA), Color(0xFFEAFBFB)],
  );

  static const LinearGradient cardGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFFFFFFFF), Color(0xFFEFFCFB)],
  );

  static const LinearGradient accentGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFF0FA3B1), Color(0xFF2EC4B6)],
  );

  // Dark mode gradients
  static const LinearGradient darkBackgroundGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [Color(0xFF121212), Color(0xFF1E1E1E)],
  );

  static const LinearGradient darkCardGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFF1E1E1E), Color(0xFF2A2A2A)],
  );

  static const LinearGradient darkAccentGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFF2EC4B6), Color(0xFF0FA3B1)],
  );

  // Helper methods to get gradients based on theme
  static LinearGradient getBackgroundGradient(BuildContext context) {
    final themeController = Get.find<ThemeController>();
    return themeController.isDarkMode.value
        ? darkBackgroundGradient
        : backgroundGradient;
  }

  static LinearGradient getCardGradient(BuildContext context) {
    final themeController = Get.find<ThemeController>();
    return themeController.isDarkMode.value
        ? darkCardGradient
        : cardGradient;
  }

  static LinearGradient getAccentGradient(BuildContext context) {
    final themeController = Get.find<ThemeController>();
    return themeController.isDarkMode.value
        ? darkAccentGradient
        : accentGradient;
  }

  // Helper methods to get colors based on theme
  static Color getSurfaceColor(BuildContext context) {
    final themeController = Get.find<ThemeController>();
    return themeController.isDarkMode.value
        ? darkSurface
        : surface;
  }

  static Color getBackgroundColor(BuildContext context) {
    final themeController = Get.find<ThemeController>();
    return themeController.isDarkMode.value
        ? darkBackground
        : softBlue;
  }
}
