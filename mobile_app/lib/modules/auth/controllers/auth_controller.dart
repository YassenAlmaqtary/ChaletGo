import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/models/user_model.dart';
import '../../../data/providers/auth_provider.dart';
import '../../../core/services/auth_service.dart';
import '../../../core/controllers/main_controller.dart';
import '../../../routes/app_pages.dart';

class AuthController extends GetxController {
  final AuthProvider authProvider;
  final AuthService authService;

  var isLoading = false.obs;
  var errorMessage = ''.obs;
  final loginFormKey = GlobalKey<FormState>();
  final loginEmailCtrl = TextEditingController();
  final loginPasswordCtrl = TextEditingController();
  final loginObscure = true.obs;

  final registerFormKey = GlobalKey<FormState>();
  final registerNameCtrl = TextEditingController();
  final registerEmailCtrl = TextEditingController();
  final registerPhoneCtrl = TextEditingController();
  final registerPasswordCtrl = TextEditingController();
  final registerPasswordConfirmCtrl = TextEditingController();
  final registerObscure = true.obs;
  final registerConfirmObscure = true.obs;
  final registerUserType = 'customer'.obs;

  AuthController(this.authProvider, this.authService);

  void toggleLoginPasswordVisibility() {
    loginObscure.toggle();
  }

  void toggleRegisterPasswordVisibility() {
    registerObscure.toggle();
  }

  void toggleRegisterConfirmPasswordVisibility() {
    registerConfirmObscure.toggle();
  }

  void setRegisterUserType(String value) {
    registerUserType.value = value;
  }

  Future<void> login() async {
    if (isClosed) return;
    
    errorMessage.value = '';
    final isValid = loginFormKey.currentState?.validate() ?? false;
    if (!isValid) {
      return;
    }

    if (isClosed) return;
    final email = loginEmailCtrl.text.trim();
    final password = loginPasswordCtrl.text.trim();

    try {
      if (isClosed) return;
      isLoading.value = true;
      final res = await authProvider.login(email, password);
      
      if (isClosed) return;
      
      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>;
        final token = data['token'] as String?;
        final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);

        if (token != null) {
          await authService.setSession(token, user);
          
          if (isClosed) return;
          
          // Check if user can access mobile app (only customers)
          if (user.canAccessMobileApp) {
            // Check if there's a pending route to redirect to
            final pendingRoute = authService.pendingRoute;
            if (pendingRoute != null && pendingRoute.isNotEmpty) {
              // Get the arguments if any (e.g., chalet for booking)
              final args = Get.arguments;
              authService.clearPendingRoute();
              // Small delay to ensure session is fully set
              await Future.delayed(const Duration(milliseconds: 100));
              
              // If pending route is profile or bookingList, navigate to main with correct index
              if (pendingRoute == Routes.profile) {
                Get.offAllNamed(Routes.main);
                // Change to profile tab after navigation
                Future.delayed(const Duration(milliseconds: 200), () {
                  try {
                    final mainController = Get.find<MainController>();
                    mainController.changeIndex(2); // Profile index
                  } catch (e) {
                    Get.log('Error changing to profile tab: $e');
                  }
                });
              } else if (pendingRoute == Routes.bookingList) {
                Get.offAllNamed(Routes.main);
                // Change to bookings tab after navigation
                Future.delayed(const Duration(milliseconds: 200), () {
                  try {
                    final mainController = Get.find<MainController>();
                    mainController.changeIndex(1); // Bookings index
                  } catch (e) {
                    Get.log('Error changing to bookings tab: $e');
                  }
                });
              } else {
                // Other routes (like booking start)
                if (args != null) {
                  Get.offAllNamed(pendingRoute, arguments: args);
                } else {
                  Get.offAllNamed(pendingRoute);
                }
              }
            } else {
              // No pending route, go to main page
              Get.offAllNamed(Routes.main);
            }
          } else {
            // Admin or Owner should use web panels
            Get.snackbar(
              'غير مصرح',
              'هذا التطبيق مخصص للعملاء فقط. يرجى استخدام لوحة التحكم على الموقع.',
              duration: const Duration(seconds: 4),
            );
            await authService.clearSession();
          }
          return;
        }
      }

      if (!isClosed) {
        errorMessage.value = res['message']?.toString() ?? 'تعذر تسجيل الدخول';
      }
    } catch (e) {
      Get.log('Login error: $e');
      if (!isClosed) {
        errorMessage.value = 'حدث خطأ غير متوقع';
      }
    } finally {
      if (!isClosed) {
        isLoading.value = false;
      }
    }
  }

  Future<void> register() async {
    if (isClosed) return;
    
    errorMessage.value = '';
    final isValid = registerFormKey.currentState?.validate() ?? false;
    if (!isValid) {
      return;
    }

    if (isClosed) return;
    final Map<String, dynamic> payload = {
      'name': registerNameCtrl.text.trim(),
      'email': registerEmailCtrl.text.trim(),
      'phone': registerPhoneCtrl.text.trim().isEmpty
          ? null
          : registerPhoneCtrl.text.trim(),
      'password': registerPasswordCtrl.text.trim(),
      'password_confirmation': registerPasswordConfirmCtrl.text.trim(),
      // Mobile app registration is customer-only.
      'user_type': 'customer',
    };
    try {
      payload.removeWhere((key, value) =>
          value == null || (value is String && value.trim().isEmpty));
      
      if (isClosed) return;
      isLoading.value = true;
      final res = await authProvider.register(payload);

      if (isClosed) return;

      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>;
        final token = data['token'] as String?;
        final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);

        if (token != null) {
          await authService.setSession(token, user);
          
          if (isClosed) return;
          
          // Check if user can access mobile app (only customers)
          if (user.canAccessMobileApp) {
            // Check if there's a pending route to redirect to
            final pendingRoute = authService.pendingRoute;
            if (pendingRoute != null && pendingRoute.isNotEmpty) {
              // Get the arguments if any (e.g., chalet for booking)
              final args = Get.arguments;
              authService.clearPendingRoute();
              // Small delay to ensure session is fully set
              await Future.delayed(const Duration(milliseconds: 100));
              
              // If pending route is profile or bookingList, navigate to main with correct index
              if (pendingRoute == Routes.profile) {
                Get.offAllNamed(Routes.main);
                // Change to profile tab after navigation
                Future.delayed(const Duration(milliseconds: 200), () {
                  try {
                    final mainController = Get.find<MainController>();
                    mainController.changeIndex(2); // Profile index
                  } catch (e) {
                    Get.log('Error changing to profile tab: $e');
                  }
                });
              } else if (pendingRoute == Routes.bookingList) {
                Get.offAllNamed(Routes.main);
                // Change to bookings tab after navigation
                Future.delayed(const Duration(milliseconds: 200), () {
                  try {
                    final mainController = Get.find<MainController>();
                    mainController.changeIndex(1); // Bookings index
                  } catch (e) {
                    Get.log('Error changing to bookings tab: $e');
                  }
                });
              } else {
                // Other routes (like booking start)
                if (args != null) {
                  Get.offAllNamed(pendingRoute, arguments: args);
                } else {
                  Get.offAllNamed(pendingRoute);
                }
              }
            } else {
              // No pending route, go to main page
              Get.offAllNamed(Routes.main);
            }
            if (!isClosed) {
              _resetRegisterForm();
            }
          } else {
            // Admin or Owner should use web panels
            Get.snackbar(
              'غير مصرح',
              'هذا التطبيق مخصص للعملاء فقط. يرجى استخدام لوحة التحكم على الموقع.',
              duration: const Duration(seconds: 4),
            );
            await authService.clearSession();
          }
          return;
        }
      }

      if (!isClosed) {
        errorMessage.value = res['message']?.toString() ?? 'تعذر إنشاء الحساب';
      }
    } catch (e) {
      if (!isClosed) {
        errorMessage.value = 'حدث خطأ غير متوقع';
      }
    } finally {
      if (!isClosed) {
        isLoading.value = false;
      }
    }
  }

  Future<void> logout() async {
    try {
      await authProvider.logout();
    } catch (_) {}
    await authService.clearSession();
    Get.offAllNamed(Routes.login);
  }

  Future<UserModel?> loadProfile() async {
    try {
      final res = await authProvider.profile();
      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>?;
        if (data != null && data['user'] != null) {
          final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
          await authService.setSession(authService.token ?? '', user);
          return user;
        }
      }
    } catch (_) {}
    return null;
  }

  Future<String?> updateProfile(Map<String, dynamic> payload) async {
    try {
      payload.removeWhere((key, value) =>
          value == null || (value is String && value.trim().isEmpty));
      final res = await authProvider.updateProfile(payload);
      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>?;
        if (data != null && data['user'] != null) {
          final currentToken = authService.token ?? '';
          final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
          await authService.setSession(currentToken, user);
        }
        return null;
      }
      return res['message']?.toString() ?? 'تعذر تحديث البيانات';
    } catch (e) {
      return 'حدث خطأ غير متوقع';
    }
  }

  @override
  void onClose() {
    loginEmailCtrl.dispose();
    loginPasswordCtrl.dispose();
    registerNameCtrl.dispose();
    registerEmailCtrl.dispose();
    registerPhoneCtrl.dispose();
    registerPasswordCtrl.dispose();
    registerPasswordConfirmCtrl.dispose();
    super.onClose();
  }

  void _resetRegisterForm() {
    registerFormKey.currentState?.reset();
    registerNameCtrl.clear();
    registerEmailCtrl.clear();
    registerPhoneCtrl.clear();
    registerPasswordCtrl.clear();
    registerPasswordConfirmCtrl.clear();
    registerUserType.value = 'customer';
    registerObscure.value = true;
    registerConfirmObscure.value = true;
  }
}
