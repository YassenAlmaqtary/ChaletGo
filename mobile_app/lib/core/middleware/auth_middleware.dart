import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../services/auth_service.dart';
import '../../routes/app_pages.dart';

class AuthMiddleware extends GetMiddleware {
  @override
  RouteSettings? redirect(String? route) {
    try {
      final authService = Get.find<AuthService>();
      
      // Check if user is logged in
      if (!authService.isLoggedIn) {
        // Save the requested route to redirect after login
        if (route != null && route != Routes.login && route != Routes.register) {
          authService.setPendingRoute(route);
        }
        
        // Redirect to login if not authenticated
        Get.snackbar(
          'تسجيل الدخول مطلوب',
          'يجب تسجيل الدخول للوصول إلى هذه الصفحة',
          snackPosition: SnackPosition.BOTTOM,
          duration: const Duration(seconds: 2),
        );
        return const RouteSettings(name: Routes.login);
      }
      
      // Check if user can access mobile app (only customers)
      if (!authService.canAccessMobileApp) {
        // Clear session and redirect to login
        authService.clearSession();
        Get.snackbar(
          'غير مصرح',
          'هذا التطبيق مخصص للعملاء فقط',
          snackPosition: SnackPosition.BOTTOM,
          duration: const Duration(seconds: 2),
        );
        return const RouteSettings(name: Routes.login);
      }
      
      // Allow access
      return null;
    } catch (e) {
      // AuthService might not be initialized yet
      Get.log('AuthMiddleware error: $e');
      return const RouteSettings(name: Routes.login);
    }
  }
}

