import 'package:get/get.dart';

import '../../../core/services/auth_service.dart';
import '../../../routes/app_pages.dart';

class SplashController extends GetxController {
  final AuthService authService;

  SplashController(this.authService);

  @override
  void onReady() {
    super.onReady();
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    await Future.delayed(const Duration(milliseconds: 400));
    
    // Always go to main page (chalet list) - no login required
    // Login will be requested when accessing protected pages
    if (authService.isLoggedIn) {
      // Check if user can access mobile app (only customers)
      if (authService.canAccessMobileApp) {
        Get.offAllNamed(Routes.main);
      } else {
        // Admin or Owner should use web panels, not mobile app
        // Clear session and go to main page
        await authService.clearSession();
        Get.offAllNamed(Routes.main);
      }
    } else {
      // Not logged in - go to main page (chalet list) directly
      Get.offAllNamed(Routes.main);
    }
  }
}
