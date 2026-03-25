import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../widgets/app_logo.dart';
import '../controllers/auth_controller.dart';

class RegisterView extends StatelessWidget {
  const RegisterView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<AuthController>();
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            child: Form(
              key: controller.registerFormKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Center(child: AppLogo()),
                  const SizedBox(height: 32),
                  TextFormField(
                    controller: controller.registerNameCtrl,
                    decoration:
                        InputDecoration(labelText: 'full_name'.tr),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'please_enter_name'.tr;
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: controller.registerEmailCtrl,
                    decoration:
                        InputDecoration(labelText: 'email'.tr),
                    keyboardType: TextInputType.emailAddress,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'please_enter_email'.tr;
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: controller.registerPhoneCtrl,
                    decoration: InputDecoration(
                        labelText: 'phone_optional'.tr),
                  ),
                  const SizedBox(height: 12),
                  Obx(() => DropdownButtonFormField<String>(
                        decoration:
                            InputDecoration(labelText: 'user_type'.tr),
                        value: controller.registerUserType.value,
                        items: [
                          DropdownMenuItem(
                              value: 'customer', child: Text('customer'.tr)),
                          DropdownMenuItem(
                              value: 'owner', child: Text('owner'.tr)),
                        ],
                        onChanged: (value) {
                          if (value != null) {
                            controller.setRegisterUserType(value);
                          }
                        },
                      )),
                  const SizedBox(height: 12),
                  Obx(() => TextFormField(
                        controller: controller.registerPasswordCtrl,
                        obscureText: controller.registerObscure.value,
                        decoration: InputDecoration(
                          labelText: 'password'.tr,
                          suffixIcon: IconButton(
                            icon: Icon(controller.registerObscure.value
                                ? Icons.visibility
                                : Icons.visibility_off),
                            onPressed:
                                controller.toggleRegisterPasswordVisibility,
                          ),
                        ),
                        validator: (value) {
                          if (value == null || value.length < 8) {
                            return 'password_min_length'.tr;
                          }
                          return null;
                        },
                      )),
                  const SizedBox(height: 12),
                  Obx(() => TextFormField(
                        controller: controller.registerPasswordConfirmCtrl,
                        obscureText: controller.registerConfirmObscure.value,
                        decoration: InputDecoration(
                          labelText: 'confirm_password'.tr,
                          suffixIcon: IconButton(
                            icon: Icon(controller.registerConfirmObscure.value
                                ? Icons.visibility
                                : Icons.visibility_off),
                            onPressed: controller
                                .toggleRegisterConfirmPasswordVisibility,
                          ),
                        ),
                        validator: (value) {
                          if (value != controller.registerPasswordCtrl.text) {
                            return 'passwords_not_match'.tr;
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
                              : controller.register,
                          child: controller.isLoading.value
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(
                                      strokeWidth: 2, color: Colors.white),
                                )
                              : Text('register'.tr),
                        ),
                      )),
                  TextButton(
                    onPressed: () => Get.toNamed('/login'),
                    child: Text('have_account'.tr),
                  )
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
