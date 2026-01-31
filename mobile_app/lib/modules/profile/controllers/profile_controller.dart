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
    // Load data immediately if user is already logged in
    if (!isClosed && authService.isLoggedIn && authService.user != null) {
      final user = authService.user!;
      nameCtrl.text = user.name;
      emailCtrl.text = user.email;
      phoneCtrl.text = user.phone ?? '';
      isLoading.value = false;
    }
    // Then try to refresh from API
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    if (isClosed) return;
    
    // Use cached user data first (from authService)
    final cachedUser = authService.user;
    if (cachedUser != null && !isClosed) {
      nameCtrl.text = cachedUser.name;
      emailCtrl.text = cachedUser.email;
      phoneCtrl.text = cachedUser.phone ?? '';
      // Set loading to false immediately since we have cached data
      isLoading.value = false;
    } else {
      // No cached data, show loading
      isLoading.value = true;
    }

    // Try to refresh data from API in background (non-blocking)
    try {
      if (isClosed) return;
      final user = await authController.loadProfile().timeout(
        const Duration(seconds: 10),
        onTimeout: () {
          // If timeout, just return null and use cached data
          return null;
        },
      );
      if (user != null && !isClosed) {
        nameCtrl.text = user.name;
        emailCtrl.text = user.email;
        phoneCtrl.text = user.phone ?? '';
      }
    } catch (e) {
      // If error, just use cached data - don't show error to user
      Get.log('Error loading profile: $e');
    } finally {
      if (!isClosed) {
        isLoading.value = false;
      }
    }
  }

  Future<void> saveProfile() async {
    if (isClosed) return;
    
    errorMessage.value = '';
    successMessage.value = '';

    if (isClosed) return;
    final name = nameCtrl.text.trim();
    if (name.isEmpty) {
      if (!isClosed) {
        errorMessage.value = 'الرجاء إدخال الاسم';
      }
      return;
    }

    if (isClosed) return;
    final password = passwordCtrl.text.trim();
    final confirm = confirmPasswordCtrl.text.trim();

    if (password.isNotEmpty && password.length < 8) {
      if (!isClosed) {
        errorMessage.value = 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل';
      }
      return;
    }

    if (password.isNotEmpty && password != confirm) {
      if (!isClosed) {
        errorMessage.value = 'تأكيد كلمة المرور غير مطابق';
      }
      return;
    }

    if (isClosed) return;
    isSaving.value = true;
    if (isClosed) return;
    final phone = phoneCtrl.text.trim();
    final payload = {
      'name': name,
      'phone': phone,
      if (password.isNotEmpty) 'password': password,
      if (password.isNotEmpty) 'password_confirmation': confirm,
    };

    final result = await authController.updateProfile(payload);
    
    if (isClosed) return;
    isSaving.value = false;

    if (result != null) {
      errorMessage.value = result;
      return;
    }

    successMessage.value = 'تم تحديث البيانات بنجاح';
    if (password.isNotEmpty && !isClosed) {
      passwordCtrl.clear();
      confirmPasswordCtrl.clear();
    }
    if (!isClosed) {
      Get.snackbar('تم', successMessage.value,
          snackPosition: SnackPosition.BOTTOM);
    }
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
