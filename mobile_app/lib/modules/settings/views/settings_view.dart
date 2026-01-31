import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/constants/app_colors.dart';
import '../controllers/settings_controller.dart';
import '../../auth/controllers/auth_controller.dart';

class SettingsView extends StatelessWidget {
  const SettingsView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.put(SettingsController());

    return Scaffold(
      appBar: AppBar(
        title: Text('settings'.tr),
        leading: Builder(
          builder: (context) => IconButton(
            icon: const Icon(Icons.menu_rounded),
            onPressed: () => Scaffold.of(context).openDrawer(),
          ),
        ),
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: AppColors.getBackgroundGradient(context),
        ),
        child: SafeArea(
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // General Settings
              _buildSectionHeader('settings'.tr),
              _buildSettingsCard(
                children: [
                  Obx(() => _buildSwitchTile(
                    icon: Icons.notifications_rounded,
                    title: 'notifications'.tr,
                    subtitle: 'notifications_desc'.tr,
                    value: controller.notificationsEnabled.value,
                    onChanged: controller.toggleNotifications,
                  )),
                  const Divider(),
                  Obx(() => _buildSwitchTile(
                    icon: Icons.dark_mode_rounded,
                    title: 'dark_mode'.tr,
                    subtitle: 'dark_mode_desc'.tr,
                    value: controller.isDarkMode,
                    onChanged: controller.toggleDarkMode,
                  )),
                ],
              ),

              const SizedBox(height: 24),

              // Language Settings
              _buildSectionHeader('language'.tr),
              _buildSettingsCard(
                children: [
                  Obx(() => _buildListTile(
                    icon: Icons.language_rounded,
                    title: 'language'.tr,
                    subtitle: controller.currentLanguageName,
                    trailing: DropdownButton<String>(
                      value: controller.currentLanguage,
                      underline: const SizedBox(),
                      items: const [
                        DropdownMenuItem(value: 'ar', child: Text('العربية')),
                        DropdownMenuItem(value: 'en', child: Text('English')),
                      ],
                      onChanged: (value) {
                        if (value != null) {
                          controller.changeLanguage(value);
                        }
                      },
                    ),
                  )),
                ],
              ),

              const SizedBox(height: 24),

              // Account Settings
              _buildSectionHeader('account'.tr),
              _buildSettingsCard(
                children: [
                  _buildListTile(
                    icon: Icons.person_rounded,
                    title: 'profile'.tr,
                    subtitle: 'personal_info'.tr,
                    onTap: () {
                      Get.back();
                      // Navigate to profile
                    },
                  ),
                  const Divider(),
                  _buildListTile(
                    icon: Icons.lock_rounded,
                    title: 'change_password'.tr,
                    subtitle: 'change_password_desc'.tr,
                    onTap: () {
                      // Navigate to change password
                    },
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // App Info
              _buildSectionHeader('about'.tr),
              _buildSettingsCard(
                children: [
                  _buildListTile(
                    icon: Icons.info_outline_rounded,
                    title: 'app_version'.tr,
                    subtitle: '1.0.0',
                  ),
                  const Divider(),
                  _buildListTile(
                    icon: Icons.privacy_tip_rounded,
                    title: 'privacy_policy'.tr,
                    onTap: () {
                      // Show privacy policy
                    },
                  ),
                  const Divider(),
                  _buildListTile(
                    icon: Icons.description_rounded,
                    title: 'terms_of_service'.tr,
                    onTap: () {
                      // Show terms of service
                    },
                  ),
                ],
              ),

              const SizedBox(height: 32),

              // Logout Button
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final authController = Get.find<AuthController>();
                    await authController.logout();
                  },
                  icon: const Icon(Icons.logout_rounded),
                  label: const Text('تسجيل الخروج'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                ),
              ),

              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.bold,
          color: AppColors.dark,
        ),
      ),
    );
  }

  Widget _buildSettingsCard({required List<Widget> children}) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: children,
      ),
    );
  }

  Widget _buildSwitchTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required Function(bool) onChanged,
  }) {
    return SwitchListTile(
      secondary: Icon(icon, color: AppColors.primary),
      title: Text(title),
      subtitle: Text(subtitle),
      value: value,
      onChanged: onChanged,
    );
  }

  Widget _buildListTile({
    required IconData icon,
    required String title,
    String? subtitle,
    Widget? trailing,
    VoidCallback? onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.primary),
      title: Text(title),
      subtitle: subtitle != null ? Text(subtitle) : null,
      trailing: trailing ?? (onTap != null ? const Icon(Icons.chevron_left_rounded) : null),
      onTap: onTap,
    );
  }
}

