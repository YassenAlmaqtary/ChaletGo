import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/profile_controller.dart';

class ProfileView extends StatelessWidget {
  final GlobalKey<ScaffoldState>? scaffoldKey;
  
  const ProfileView({super.key, this.scaffoldKey});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<ProfileController>();

    return Scaffold(
      appBar: AppBar(
        title: Text('profile'.tr),
        leading: IconButton(
          icon: const Icon(Icons.menu_rounded),
          onPressed: () => scaffoldKey?.currentState?.openDrawer(),
        ),
      ),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        return Padding(
          padding: const EdgeInsets.all(24),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TextField(
                  controller: controller.nameCtrl,
                  decoration: InputDecoration(labelText: 'full_name'.tr),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: controller.emailCtrl,
                  enabled: false,
                  decoration: InputDecoration(labelText: 'email'.tr),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: controller.phoneCtrl,
                  decoration: InputDecoration(labelText: 'phone_number'.tr),
                ),
                const SizedBox(height: 24),
                Text(
                  'change_password_title'.tr,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 12),
                Obx(() {
                  return TextField(
                    controller: controller.passwordCtrl,
                    obscureText: controller.passwordObscure.value,
                    decoration: InputDecoration(
                      labelText: 'new_password'.tr,
                      helperText: 'password_helper'.tr,
                      suffixIcon: IconButton(
                        onPressed: controller.togglePasswordVisibility,
                        icon: Icon(controller.passwordObscure.value
                            ? Icons.visibility_off
                            : Icons.visibility),
                      ),
                    ),
                  );
                }),
                const SizedBox(height: 16),
                Obx(() {
                  return TextField(
                    controller: controller.confirmPasswordCtrl,
                    obscureText: controller.confirmPasswordObscure.value,
                    decoration: InputDecoration(
                      labelText: 'confirm_new_password'.tr,
                      suffixIcon: IconButton(
                        onPressed: controller.toggleConfirmPasswordVisibility,
                        icon: Icon(controller.confirmPasswordObscure.value
                            ? Icons.visibility_off
                            : Icons.visibility),
                      ),
                    ),
                  );
                }),
                const SizedBox(height: 20),
                Obx(() {
                  final error = controller.errorMessage.value;
                  if (error.isEmpty) {
                    return const SizedBox.shrink();
                  }
                  return Text(
                    error,
                    style: const TextStyle(color: Colors.red),
                  );
                }),
                Obx(() {
                  final success = controller.successMessage.value;
                  if (success.isEmpty) {
                    return const SizedBox.shrink();
                  }
                  return Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(
                      success,
                      style: const TextStyle(color: Colors.green),
                    ),
                  );
                }),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: Obx(() => ElevatedButton(
                        onPressed: controller.isSaving.value
                            ? null
                            : controller.saveProfile,
                        child: controller.isSaving.value
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                    strokeWidth: 2, color: Colors.white),
                              )
                            : Text('save_changes'.tr),
                      )),
                ),
              ],
            ),
          ),
        );
      }),
    );
  }
}
