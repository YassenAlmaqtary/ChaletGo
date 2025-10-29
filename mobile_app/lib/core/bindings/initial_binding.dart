import 'package:get/get.dart';

import '../../data/providers/api_provider.dart';
import '../../data/providers/auth_provider.dart';
import '../../data/providers/chalet_provider.dart';
import '../../data/providers/booking_provider.dart';
import '../../modules/auth/controllers/auth_controller.dart';
import '../../modules/chalets/controllers/chalet_controller.dart';
import '../../modules/chalets/controllers/chalet_detail_controller.dart';
import '../services/auth_service.dart';
import '../services/dio_client.dart';
import '../../modules/profile/controllers/profile_controller.dart';

class InitialBinding extends Bindings {
  @override
  void dependencies() {
    final authService = Get.put(AuthService(), permanent: true);
    authService.init();
    Get.lazyPut<DioClient>(() => DioClient(), fenix: true);
    Get.lazyPut<ApiProvider>(() => ApiProvider(Get.find<DioClient>()),
        fenix: true);
    Get.lazyPut<AuthProvider>(() => AuthProvider(Get.find<ApiProvider>()),
        fenix: true);
    Get.lazyPut<ChaletProvider>(() => ChaletProvider(Get.find<ApiProvider>()),
        fenix: true);
    Get.lazyPut<BookingProvider>(() => BookingProvider(Get.find<ApiProvider>()),
        fenix: true);
    Get.lazyPut<AuthController>(() => AuthController(Get.find(), Get.find()),
        fenix: true);
    Get.lazyPut<ProfileController>(
      () => ProfileController(
          authService: Get.find(), authController: Get.find()),
      fenix: true,
    );
    Get.lazyPut<ChaletController>(() => ChaletController(Get.find()),
        fenix: true);
    Get.lazyPut<ChaletDetailController>(
        () => ChaletDetailController(Get.find()),
        fenix: true);
  }
}
