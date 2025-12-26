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

part 'app_routes.dart';

class AppPages {
  AppPages._();

  static const initial = Routes.splash;

  static final routes = [
    GetPage(
      name: Routes.splash,
      page: () => const SplashView(),
      binding: BindingsBuilder(() {
        Get.put(SplashController(Get.find<AuthService>()));
      }),
    ),
    GetPage(name: Routes.login, page: () => const LoginView()),
    GetPage(name: Routes.register, page: () => const RegisterView()),
    GetPage(
      name: Routes.profile,
      page: () => const ProfileView(),
      binding: BindingsBuilder(() {
        Get.find<ProfileController>();
      }),
    ),
    GetPage(name: Routes.chaletList, page: () => const ChaletListView()),
    GetPage(
      name: Routes.chaletDetail,
      page: () => const ChaletDetailView(),
      binding: BindingsBuilder(() {
        Get.find<ChaletDetailController>();
      }),
    ),
    GetPage(
      name: Routes.bookingList,
      page: () => const BookingListView(),
      binding: BindingsBuilder(() {
        Get.put(BookingListController(Get.find<BookingProvider>()));
      }),
    ),
    GetPage(name: Routes.bookingDetail, page: () => const BookingDetailView()),
    GetPage(name: Routes.bookingStart, page: () => const BookingStartView()),
    GetPage(
        name: Routes.bookingSummary, page: () => const BookingSummaryView()),
    GetPage(name: Routes.paymentMethod, page: () => const PaymentMethodView()),
    GetPage(name: Routes.paymentResult, page: () => const PaymentResultView()),
  ];
}
