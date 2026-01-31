import 'package:get/get.dart';
import '../modules/auth/views/login_view.dart';
import '../modules/auth/views/register_view.dart';
import '../modules/profile/controllers/profile_controller.dart';
import '../modules/profile/views/profile_view.dart';
import '../modules/chalets/views/chalet_list_view.dart';
import '../modules/chalets/controllers/chalet_detail_controller.dart';
import '../modules/chalets/views/chalet_detail_view.dart';
import '../modules/booking/views/booking_start_view.dart';
import '../modules/booking/views/booking_summary_view.dart';
import '../modules/booking/views/payment_method_view.dart';
import '../modules/booking/views/payment_result_view.dart';
import '../modules/booking/views/booking_list_view.dart';
import '../modules/booking/controllers/booking_list_controller.dart';
import '../modules/booking/views/booking_detail_view.dart';
import '../data/providers/booking_provider.dart';
import '../modules/splash/controllers/splash_controller.dart';
import '../modules/splash/views/splash_view.dart';
import '../core/services/auth_service.dart';
import '../modules/main/views/main_layout.dart';
import '../core/controllers/main_controller.dart';
import '../modules/settings/views/settings_view.dart';
import '../modules/help/views/help_view.dart';
import '../modules/about/views/about_view.dart';
import '../core/middleware/auth_middleware.dart';

part 'app_routes.dart';

class AppPages {
  AppPages._();

  static const initial = Routes.splash;

  static final routes = [
    GetPage(
      name: Routes.splash,
      page: () => const SplashView(),
      transition: Transition.fadeIn,
      transitionDuration: const Duration(milliseconds: 300),
      binding: BindingsBuilder(() {
        Get.put(SplashController(Get.find<AuthService>()));
      }),
    ),
    GetPage(
      name: Routes.login,
      page: () => const LoginView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
    ),
    GetPage(
      name: Routes.register,
      page: () => const RegisterView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
    ),
    GetPage(
      name: Routes.main,
      page: () => MainLayout(),
      transition: Transition.fadeIn,
      transitionDuration: const Duration(milliseconds: 300),
      // No middleware - main page is accessible without login
      binding: BindingsBuilder(() {
        Get.put(MainController());
      }),
    ),
    GetPage(
      name: Routes.profile,
      page: () => const ProfileView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
      binding: BindingsBuilder(() {
        Get.find<ProfileController>();
      }),
    ),
    GetPage(
      name: Routes.chaletList,
      page: () => const ChaletListView(),
      transition: Transition.fadeIn,
      transitionDuration: const Duration(milliseconds: 300),
    ),
    GetPage(
      name: Routes.chaletDetail,
      page: () => const ChaletDetailView(),
      transition: Transition.rightToLeftWithFade,
      transitionDuration: const Duration(milliseconds: 400),
      binding: BindingsBuilder(() {
        Get.find<ChaletDetailController>();
      }),
    ),
    GetPage(
      name: Routes.bookingList,
      page: () => const BookingListView(),
      transition: Transition.fadeIn,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
      binding: BindingsBuilder(() {
        Get.put(BookingListController(Get.find<BookingProvider>()));
      }),
    ),
    GetPage(
      name: Routes.bookingDetail,
      page: () => const BookingDetailView(),
      transition: Transition.rightToLeftWithFade,
      transitionDuration: const Duration(milliseconds: 400),
      middlewares: [AuthMiddleware()],
    ),
    GetPage(
      name: Routes.bookingStart,
      page: () => const BookingStartView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
    ),
    GetPage(
      name: Routes.bookingSummary,
      page: () => const BookingSummaryView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
    ),
    GetPage(
      name: Routes.paymentMethod,
      page: () => const PaymentMethodView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
    ),
    GetPage(
      name: Routes.paymentResult,
      page: () => const PaymentResultView(),
      transition: Transition.fadeIn,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
    ),
    GetPage(
      name: Routes.settings,
      page: () => const SettingsView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
      middlewares: [AuthMiddleware()],
    ),
    GetPage(
      name: Routes.help,
      page: () => const HelpView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
    ),
    GetPage(
      name: Routes.about,
      page: () => const AboutView(),
      transition: Transition.rightToLeft,
      transitionDuration: const Duration(milliseconds: 300),
    ),
  ];
}
