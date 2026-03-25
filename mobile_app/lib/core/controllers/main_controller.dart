import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../routes/app_pages.dart';
import '../services/auth_service.dart';

class MainController extends GetxController {
  final RxInt currentIndex = 0.obs;
  final AuthService authService = Get.find<AuthService>();

  List<NavItem> get navItems => [
    NavItem(
      icon: Icons.home_rounded,
      label: 'home'.tr,
      route: Routes.chaletList,
      requiresAuth: false,
    ),
    NavItem(
      icon: Icons.calendar_today_rounded,
      label: 'bookings'.tr,
      route: Routes.bookingList,
      requiresAuth: true,
    ),
    NavItem(
      icon: Icons.person_rounded,
      label: 'profile'.tr,
      route: Routes.profile,
      requiresAuth: true,
    ),
  ];

  void changeIndex(int index) {
    if (currentIndex.value == index) return;
    
    // Check if the target page requires authentication
    if (index < navItems.length && navItems[index].requiresAuth) {
      if (!authService.isLoggedIn) {
        // Save the route and redirect to login
        authService.setPendingRoute(navItems[index].route);
        Get.snackbar(
          'تسجيل الدخول مطلوب',
          'يجب تسجيل الدخول للوصول إلى هذه الصفحة',
          snackPosition: SnackPosition.BOTTOM,
          duration: const Duration(seconds: 2),
        );
        Get.toNamed(Routes.login);
        return;
      }
    }
    
    // Allow navigation
    currentIndex.value = index;
  }

  void navigateToRoute(String route, {dynamic arguments}) {
    Get.toNamed(route, arguments: arguments);
  }
}

class NavItem {
  final IconData icon;
  final String label;
  final String route;
  final bool requiresAuth;

  NavItem({
    required this.icon,
    required this.label,
    required this.route,
    this.requiresAuth = false,
  });
}

