import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../core/services/auth_service.dart';
import '../../auth/controllers/auth_controller.dart';

class ProfileController extends GetxController {
  final AuthService authService;
  final AuthController authController;

  final nameCtrl = TextEditingController();
  final emailCtrl = TextEditingController();
  final phoneCtrl = TextEditingController();
  final passwordCtrl = TextEditingController();
  final confirmPasswordCtrl = TextEditingController();

  final isLoading = false.obs;
  final isSaving = false.obs;
  final errorMessage = ''.obs;
  final successMessage = ''.obs;
  final passwordObscure = true.obs;
  final confirmPasswordObscure = true.obs;

  ProfileController({required this.authService, required this.authController});

  @override
  void onInit() {
    super.onInit();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final cachedUser = authService.user;
    if (cachedUser != null) {
      nameCtrl.text = cachedUser.name;
      emailCtrl.text = cachedUser.email;
      phoneCtrl.text = cachedUser.phone ?? '';
    }

    try {
      isLoading.value = true;
      final user = await authController.loadProfile();
      if (user != null) {
        nameCtrl.text = user.name;
        emailCtrl.text = user.email;
        phoneCtrl.text = user.phone ?? '';
      }
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> saveProfile() async {
    errorMessage.value = '';
    successMessage.value = '';

    if (nameCtrl.text.trim().isEmpty) {
      errorMessage.value = 'الرجاء إدخال الاسم';
      return;
    }

    final password = passwordCtrl.text.trim();
    final confirm = confirmPasswordCtrl.text.trim();

    if (password.isNotEmpty && password.length < 8) {
      errorMessage.value = 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل';
      return;
    }

    if (password.isNotEmpty && password != confirm) {
      errorMessage.value = 'تأكيد كلمة المرور غير مطابق';
      return;
    }

    isSaving.value = true;
    final payload = {
      'name': nameCtrl.text.trim(),
      'phone': phoneCtrl.text.trim(),
      if (password.isNotEmpty) 'password': password,
      if (password.isNotEmpty) 'password_confirmation': confirm,
    };

    final result = await authController.updateProfile(payload);
    isSaving.value = false;

    if (result != null) {
      errorMessage.value = result;
      return;
    }

    successMessage.value = 'تم تحديث البيانات بنجاح';
    if (password.isNotEmpty) {
      passwordCtrl.clear();
      confirmPasswordCtrl.clear();
    }
    Get.snackbar('تم', successMessage.value,
        snackPosition: SnackPosition.BOTTOM);
  }

  void togglePasswordVisibility() {
    passwordObscure.toggle();
  }

  void toggleConfirmPasswordVisibility() {
    confirmPasswordObscure.toggle();
  }

  void logout() {
    authController.logout();
  }

  @override
  void onClose() {
    nameCtrl.dispose();
    emailCtrl.dispose();
    phoneCtrl.dispose();
    passwordCtrl.dispose();
    confirmPasswordCtrl.dispose();
    super.onClose();
  }
}
