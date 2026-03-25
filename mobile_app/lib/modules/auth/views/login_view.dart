import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../widgets/app_logo.dart';
import '../controllers/auth_controller.dart';

class LoginView extends StatelessWidget {
  const LoginView({super.key});

  @override
  Widget build(BuildContext context) {
    final AuthController controller = Get.find<AuthController>();

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            child: Form(
              key: controller.loginFormKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Center(child: AppLogo()),
                  const SizedBox(height: 32),
                  TextFormField(
                    controller: controller.loginEmailCtrl,
                    keyboardType: TextInputType.emailAddress,
                    decoration:
                        InputDecoration(labelText: 'email'.tr),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'please_enter_email'.tr;
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),
                  Obx(() => TextFormField(
                        controller: controller.loginPasswordCtrl,
                        obscureText: controller.loginObscure.value,
                        decoration: InputDecoration(
                          labelText: 'password'.tr,
                          suffixIcon: IconButton(
                            icon: Icon(controller.loginObscure.value
                                ? Icons.visibility
                                : Icons.visibility_off),
                            onPressed: controller.toggleLoginPasswordVisibility,
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'please_enter_password'.tr;
                          }
                          return null;
                        },
                      )),
                  const SizedBox(height: 16),
                  Obx(() {
                    if (controller.errorMessage.value.isNotEmpty) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Text(
                          controller.errorMessage.value,
                          style: const TextStyle(color: Colors.red),
                        ),
                      );
                    }
                    return const SizedBox.shrink();
                  }),
                  const SizedBox(height: 8),
                  Obx(() => SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: controller.isLoading.value
                              ? null
                              : controller.login,
                          child: controller.isLoading.value
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(
                                      strokeWidth: 2, color: Colors.white),
                                )
                              : Text('login'.tr),
                        ),
                      )),
                  TextButton(
                    onPressed: () => Get.toNamed('/register'),
                    child: Text('new_user'.tr),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
