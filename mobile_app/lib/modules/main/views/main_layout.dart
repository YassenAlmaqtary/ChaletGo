import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/controllers/main_controller.dart';
import '../../chalets/views/chalet_list_view.dart';
import '../../booking/views/booking_list_view.dart';
import '../../profile/views/profile_view.dart';
import '../../profile/controllers/profile_controller.dart';
import '../../../core/services/auth_service.dart';
import '../../../core/constants/app_colors.dart';
import '../../auth/controllers/auth_controller.dart';
import '../../../routes/app_pages.dart';

class MainLayout extends StatelessWidget {
  MainLayout({super.key});

  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<MainController>();

    return Scaffold(
      key: _scaffoldKey,
      body: Obx(() {
        final currentIndex = controller.currentIndex.value;
        return AnimatedSwitcher(
          duration: const Duration(milliseconds: 300),
          transitionBuilder: (child, animation) {
            return FadeTransition(
              opacity: animation,
              child: child,
            );
          },
          child: IndexedStack(
            key: ValueKey<int>(currentIndex),
            index: currentIndex,
            children: [
              ChaletListView(scaffoldKey: _scaffoldKey),
              // Only load protected views if user is logged in, otherwise show placeholder
              Obx(() {
                final authService = Get.find<AuthService>();
                if (authService.isLoggedIn) {
                  return BookingListView(scaffoldKey: _scaffoldKey);
                } else {
                  return _PlaceholderView(
                    message: 'تسجيل الدخول مطلوب',
                    scaffoldKey: _scaffoldKey,
                    onTap: () {
                      authService.setPendingRoute(Routes.bookingList);
                      Get.toNamed(Routes.login);
                    },
                  );
                }
              }),
              Obx(() {
                final authService = Get.find<AuthService>();
                if (authService.isLoggedIn && authService.user != null) {
                  // Ensure ProfileController is initialized
                  try {
                    Get.find<ProfileController>();
                  } catch (e) {
                    // Controller not found, will be created by binding
                  }
                  return ProfileView(scaffoldKey: _scaffoldKey);
                } else {
                  return _PlaceholderView(
                    message: 'تسجيل الدخول مطلوب',
                    scaffoldKey: _scaffoldKey,
                    onTap: () {
                      authService.setPendingRoute(Routes.profile);
                      Get.toNamed(Routes.login);
                    },
                  );
                }
              }),
            ],
          ),
        );
      }),
      bottomNavigationBar: Obx(() => _buildBottomNavBar(context, controller)),
      drawer: _buildDrawer(context, controller),
    );
  }

  Widget _buildBottomNavBar(BuildContext context, MainController controller) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    
    return Container(
      decoration: BoxDecoration(
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(20),
          topRight: Radius.circular(20),
        ),
        child: BottomNavigationBar(
          currentIndex: controller.currentIndex.value,
          onTap: controller.changeIndex,
          type: BottomNavigationBarType.fixed,
          backgroundColor: isDark ? const Color(0xFF1E1E1E) : Colors.white,
          selectedItemColor: AppColors.primary,
          unselectedItemColor: isDark ? const Color(0xFFB0B0B0) : AppColors.muted,
          selectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 12,
          ),
          unselectedLabelStyle: const TextStyle(
            fontWeight: FontWeight.normal,
            fontSize: 12,
          ),
          elevation: 0,
          items: controller.navItems.map((item) {
            final index = controller.navItems.indexOf(item);
            final isSelected = controller.currentIndex.value == index;
            return BottomNavigationBarItem(
              icon: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: isSelected
                      ? AppColors.primary.withOpacity(0.1)
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  item.icon,
                  size: 24,
                ),
              ),
              label: item.label,
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildDrawer(BuildContext context, MainController controller) {
    final authService = Get.find<AuthService>();
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    
    return Drawer(
      backgroundColor: isDark ? const Color(0xFF1E1E1E) : Colors.white,
      child: SafeArea(
        child: Column(
          children: [
            // Header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: AppColors.getAccentGradient(context),
              ),
              child: Obx(() {
                final user = authService.user;
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CircleAvatar(
                      radius: 35,
                      backgroundColor: isDark ? const Color(0xFF2E2E2E) : Colors.white,
                      child: Text(
                        user?.name.substring(0, 1).toUpperCase() ?? 'U',
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      user?.name ?? 'user'.tr,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      user?.email ?? '',
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.9),
                        fontSize: 14,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                );
              }),
            ),
            // Menu Items
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 8),
                children: [
                  _buildDrawerItem(
                    context,
                    icon: Icons.home_rounded,
                    title: 'home'.tr,
                    onTap: () {
                      Navigator.pop(context);
                      controller.changeIndex(0);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.calendar_today_rounded,
                    title: 'bookings'.tr,
                    onTap: () {
                      Navigator.pop(context);
                      controller.changeIndex(1);
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.person_rounded,
                    title: 'profile'.tr,
                    onTap: () {
                      Navigator.pop(context);
                      controller.changeIndex(2);
                    },
                  ),
                  Divider(
                    height: 16,
                    indent: 16,
                    endIndent: 16,
                    color: isDark ? const Color(0xFF2E2E2E) : null,
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.settings_rounded,
                    title: 'settings'.tr,
                    onTap: () {
                      Navigator.pop(context);
                      Get.toNamed('/settings');
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.help_outline_rounded,
                    title: 'help'.tr,
                    onTap: () {
                      Navigator.pop(context);
                      Get.toNamed('/help');
                    },
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.info_outline_rounded,
                    title: 'about'.tr,
                    onTap: () {
                      Navigator.pop(context);
                      Get.toNamed('/about');
                    },
                  ),
                  Divider(
                    height: 16,
                    indent: 16,
                    endIndent: 16,
                    color: isDark ? const Color(0xFF2E2E2E) : null,
                  ),
                  _buildDrawerItem(
                    context,
                    icon: Icons.logout_rounded,
                    title: 'logout'.tr,
                    color: Colors.red,
                    onTap: () async {
                      Navigator.pop(context);
                      final authController = Get.find<AuthController>();
                      await authController.logout();
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerItem(
    BuildContext context, {
    required IconData icon,
    required String title,
    required VoidCallback onTap,
    Color? color,
  }) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final isLogout = color == Colors.red;
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        color: isLogout ? Colors.red.withOpacity(0.1) : Colors.transparent,
      ),
      child: ListTile(
        leading: Icon(
          icon,
          color: color ?? AppColors.primary,
          size: 24,
        ),
        title: Text(
          title,
          style: TextStyle(
            color: color ?? (isDark ? Colors.white : AppColors.dark),
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        trailing: color == null
            ? Icon(
                Icons.chevron_left_rounded,
                color: isDark ? const Color(0xFFB0B0B0) : AppColors.muted,
              )
            : null,
        onTap: onTap,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }
}

class _PlaceholderView extends StatelessWidget {
  final String message;
  final VoidCallback onTap;
  final GlobalKey<ScaffoldState>? scaffoldKey;

  const _PlaceholderView({
    required this.message,
    required this.onTap,
    this.scaffoldKey,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('chalets'.tr),
        leading: IconButton(
          icon: const Icon(Icons.menu_rounded),
          onPressed: () => scaffoldKey?.currentState?.openDrawer(),
        ),
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.lock_outline_rounded,
                size: 64,
                color: AppColors.muted,
              ),
              const SizedBox(height: 16),
              Text(
                message,
                style: Theme.of(context).textTheme.titleMedium,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: onTap,
                child: Text('login'.tr),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

