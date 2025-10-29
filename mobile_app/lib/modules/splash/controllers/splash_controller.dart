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
    if (authService.isLoggedIn) {
      Get.offAllNamed(Routes.chaletList);
    } else {
      Get.offAllNamed(Routes.login);
    }
  }
}
