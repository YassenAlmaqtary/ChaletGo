import 'package:flutter/material.dart';

class AppColors {
  AppColors._();

  static const Color primary = Color(0xFF0FA3B1);
  static const Color secondary = Color(0xFF2EC4B6);
  static const Color accent = Color(0xFFFFD166);
  static const Color dark = Color(0xFF102542);
  static const Color muted = Color(0xFF4A5663);
  static const Color surface = Colors.white;
  static const Color softBlue = Color(0xFFE6F7F8);

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
}
