import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../core/constants/app_colors.dart';

class HelpView extends StatelessWidget {
  const HelpView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('help'.tr),
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
              // FAQ Section
              _buildSectionHeader('الأسئلة الشائعة'),
              _buildHelpCard(
                question: 'كيف أقوم بحجز شاليه؟',
                answer:
                    'يمكنك تصفح الشاليهات المتاحة من الصفحة الرئيسية، ثم اختيار الشاليه المناسب والضغط عليه لعرض التفاصيل. بعد ذلك، اضغط على زر "احجز الآن" واتبع الخطوات.',
              ),
              _buildHelpCard(
                question: 'كيف يمكنني إلغاء حجز؟',
                answer:
                    'يمكنك إلغاء الحجز من صفحة "حجوزاتي". اختر الحجز الذي تريد إلغاءه واضغط على "إلغاء الحجز". يرجى ملاحظة سياسة الإلغاء الخاصة بكل شاليه.',
              ),
              _buildHelpCard(
                question: 'ما هي طرق الدفع المتاحة؟',
                answer:
                    'نقبل الدفع ببطاقات الائتمان والخصم، التحويل البنكي، والمحافظ الرقمية. جميع المعاملات آمنة ومشفرة.',
              ),
              _buildHelpCard(
                question: 'كيف يمكنني التواصل مع الدعم؟',
                answer:
                    'يمكنك التواصل معنا عبر البريد الإلكتروني أو الهاتف. ستجد معلومات الاتصال في قسم "اتصل بنا" أدناه.',
              ),

              const SizedBox(height: 24),

              // Contact Section
              _buildSectionHeader('اتصل بنا'),
              Card(
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      _buildContactItem(
                        icon: Icons.email_rounded,
                        title: 'البريد الإلكتروني',
                        value: 'support@chaletgo.com',
                        onTap: () {
                          // Open email
                        },
                      ),
                      const Divider(),
                      _buildContactItem(
                        icon: Icons.phone_rounded,
                        title: 'الهاتف',
                        value: '+966 50 123 4567',
                        onTap: () {
                          // Make phone call
                        },
                      ),
                      const Divider(),
                      _buildContactItem(
                        icon: Icons.chat_rounded,
                        title: 'الدردشة المباشرة',
                        value: 'متاحة من 9 صباحاً إلى 9 مساءً',
                        onTap: () {
                          // Open chat
                        },
                      ),
                    ],
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

  Widget _buildHelpCard({
    required String question,
    required String answer,
  }) {
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: ExpansionTile(
        title: Text(
          question,
          style: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 15,
          ),
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              answer,
              style: const TextStyle(
                color: AppColors.muted,
                height: 1.6,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContactItem({
    required IconData icon,
    required String title,
    required String value,
    VoidCallback? onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.primary),
      title: Text(title),
      subtitle: Text(value),
      trailing: onTap != null ? const Icon(Icons.chevron_left_rounded) : null,
      onTap: onTap,
    );
  }
}

