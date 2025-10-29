import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/profile_controller.dart';

class ProfileView extends StatelessWidget {
  const ProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<ProfileController>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('الملف الشخصي'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: controller.logout,
            tooltip: 'تسجيل الخروج',
          ),
        ],
      ),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        return Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: controller.nameCtrl,
                decoration: const InputDecoration(labelText: 'الاسم الكامل'),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: controller.emailCtrl,
                enabled: false,
                decoration:
                    const InputDecoration(labelText: 'البريد الإلكتروني'),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: controller.phoneCtrl,
                decoration: const InputDecoration(labelText: 'رقم الجوال'),
              ),
              const SizedBox(height: 16),
              Obx(() {
                if (controller.errorMessage.value.isNotEmpty) {
                  return Text(
                    controller.errorMessage.value,
                    style: const TextStyle(color: Colors.red),
                  );
                }
                return const SizedBox.shrink();
              }),
              const SizedBox(height: 16),
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
                          : const Text('حفظ التغييرات'),
                    )),
              ),
            ],
          ),
        );
      }),
    );
  }
}
