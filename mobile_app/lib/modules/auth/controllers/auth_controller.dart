import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/models/user_model.dart';
import '../../../data/providers/auth_provider.dart';
import '../../../core/services/auth_service.dart';
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
    errorMessage.value = '';
    final isValid = loginFormKey.currentState?.validate() ?? false;
    if (!isValid) {
      return;
    }

    final email = loginEmailCtrl.text.trim();
    final password = loginPasswordCtrl.text.trim();

    try {
      isLoading.value = true;
      final res = await authProvider.login(email, password);
      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>;
        final token = data['token'] as String?;
        final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);

        if (token != null) {
          await authService.setSession(token, user);
          Get.offAllNamed(Routes.chaletList);
          return;
        }
      }

      errorMessage.value = res['message']?.toString() ?? 'تعذر تسجيل الدخول';
    } catch (e) {
      Get.log('Login error: $e');
      errorMessage.value = 'حدث خطأ غير متوقع';
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> register() async {
    errorMessage.value = '';
    final isValid = registerFormKey.currentState?.validate() ?? false;
    if (!isValid) {
      return;
    }

    final Map<String, dynamic> payload = {
      'name': registerNameCtrl.text.trim(),
      'email': registerEmailCtrl.text.trim(),
      'phone': registerPhoneCtrl.text.trim().isEmpty
          ? null
          : registerPhoneCtrl.text.trim(),
      'password': registerPasswordCtrl.text.trim(),
      'password_confirmation': registerPasswordConfirmCtrl.text.trim(),
      'user_type': registerUserType.value,
    };
    try {
      payload.removeWhere((key, value) =>
          value == null || (value is String && value.trim().isEmpty));
      isLoading.value = true;
      final res = await authProvider.register(payload);

      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>;
        final token = data['token'] as String?;
        final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);

        if (token != null) {
          await authService.setSession(token, user);
          Get.offAllNamed(Routes.chaletList);
          _resetRegisterForm();
          return;
        }
      }

      errorMessage.value = res['message']?.toString() ?? 'تعذر إنشاء الحساب';
    } catch (e) {
      errorMessage.value = 'حدث خطأ غير متوقع';
    } finally {
      isLoading.value = false;
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
