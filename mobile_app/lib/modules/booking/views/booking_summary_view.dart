import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/booking_controller.dart';

class BookingSummaryView extends StatelessWidget {
  const BookingSummaryView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<BookingController>(tag: 'booking');
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('ملخص الحجز')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('تفاصيل الإقامة', style: theme.textTheme.titleMedium),
            const SizedBox(height: 12),
            Obx(() => _SummaryTile(
                  title: 'تاريخ الوصول',
                  value: controller.checkInDisplay,
                  icon: Icons.login,
                )),
            const SizedBox(height: 12),
            Obx(() => _SummaryTile(
                  title: 'تاريخ المغادرة',
                  value: controller.checkOutDisplay,
                  icon: Icons.logout,
                )),
            const SizedBox(height: 12),
            Obx(() => _SummaryTile(
                  title: 'عدد الليالي',
                  value: '${controller.nights}',
                  icon: Icons.nights_stay_outlined,
                )),
            const SizedBox(height: 12),
            Obx(() => _SummaryTile(
                  title: 'عدد الضيوف',
                  value: '${controller.guests.value}',
                  icon: Icons.group_outlined,
                )),
            const Divider(height: 32),
            Text('الإضافات المختارة', style: theme.textTheme.titleMedium),
            const SizedBox(height: 8),
            Obx(() {
              final extras = controller.selectedExtras;
              if (extras.isEmpty) {
                return Text('لا توجد إضافات', style: theme.textTheme.bodySmall);
              }
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: extras
                    .map((extra) => ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text(extra.name),
                          trailing:
                              Text('+${extra.price.toStringAsFixed(2)} ر.س'),
                        ))
                    .toList(),
              );
            }),
            const Divider(height: 32),
            Obx(() => _AmountRow(
                  label: 'إجمالي الإقامة',
                  value: '${controller.baseAmount.toStringAsFixed(2)} ر.س',
                )),
            Obx(() => _AmountRow(
                  label: 'إجمالي الإضافات',
                  value: '${controller.extrasTotal.toStringAsFixed(2)} ر.س',
                )),
            const SizedBox(height: 12),
            Obx(() => _AmountRow(
                  label: 'الإجمالي المبدئي',
                  value: '${controller.grandTotal.toStringAsFixed(2)} ر.س',
                  isBold: true,
                )),
            const SizedBox(height: 24),
            Obx(() {
              final error = controller.bookingError.value;
              if (error.isEmpty) {
                return const SizedBox.shrink();
              }
              return Text(
                error,
                style: theme.textTheme.bodySmall?.copyWith(color: Colors.red),
              );
            }),
            const SizedBox(height: 16),
            Obx(() {
              final isLoading = controller.isSubmittingBooking.value;
              return SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: isLoading ? null : controller.confirmBooking,
                  child: isLoading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('تأكيد الحجز وإرسال الطلب'),
                ),
              );
            }),
          ],
        ),
      ),
    );
  }
}

class _SummaryTile extends StatelessWidget {
  const _SummaryTile({
    required this.title,
    required this.value,
    required this.icon,
  });

  final String title;
  final String value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: theme.dividerColor),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: theme.textTheme.bodySmall),
                const SizedBox(height: 4),
                Text(value, style: theme.textTheme.titleMedium),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _AmountRow extends StatelessWidget {
  const _AmountRow({
    required this.label,
    required this.value,
    this.isBold = false,
  });

  final String label;
  final String value;
  final bool isBold;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final style = isBold
        ? theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)
        : theme.textTheme.bodyMedium;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(child: Text(label, style: style)),
          const SizedBox(width: 16),
          Text(value, style: style),
        ],
      ),
    );
  }
}
