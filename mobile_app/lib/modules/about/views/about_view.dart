import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/constants/app_colors.dart';

class AboutView extends StatelessWidget {
  const AboutView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('about'.tr),
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
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              children: [
                // App Logo/Icon
                Container(
                  width: 120,
                  height: 120,
                  decoration: BoxDecoration(
                    color: AppColors.primary,
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: const Icon(
                    Icons.home_rounded,
                    size: 60,
                    color: Colors.white,
                  ),
                ),

                const SizedBox(height: 24),

                // App Name
                const Text(
                  'shaleio',
                  style: TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: AppColors.dark,
                  ),
                ),

                const SizedBox(height: 8),

                // App Version
                Text(
                  'الإصدار 1.0.0',
                  style: TextStyle(
                    fontSize: 16,
                    color: AppColors.muted,
                  ),
                ),

                const SizedBox(height: 32),

                // App Description
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'عن التطبيق',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.dark,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'shaleio هو تطبيق متخصص في حجز الشاليهات والإقامات الفاخرة. نوفر لك تجربة سهلة ومريحة للعثور على الشاليه المثالي لحجزك القادم.',
                          style: TextStyle(
                            fontSize: 15,
                            height: 1.6,
                            color: AppColors.muted,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 24),

                // Features
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'المميزات',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.dark,
                          ),
                        ),
                        const SizedBox(height: 16),
                        _buildFeatureItem('تصفح واسع للشاليهات المتاحة'),
                        _buildFeatureItem('حجز سريع وسهل'),
                        _buildFeatureItem('إدارة حجوزاتك من مكان واحد'),
                        _buildFeatureItem('تقييمات ومراجعات حقيقية'),
                        _buildFeatureItem('دعم فني متواصل'),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 24),

                // Copyright
                Text(
                  '© 2026 shaleio. جميع الحقوق محفوظة.',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppColors.muted,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFeatureItem(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Icon(
            Icons.check_circle_rounded,
            color: AppColors.primary,
            size: 20,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                fontSize: 15,
                color: AppColors.muted,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

