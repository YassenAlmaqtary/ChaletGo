import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/models/chalet_model.dart';
import '../../../data/providers/booking_provider.dart';
import '../../../data/providers/chalet_provider.dart';
import '../controllers/booking_controller.dart';

class BookingStartView extends StatelessWidget {
  const BookingStartView({super.key});

  BookingController _resolveController() {
    final args = Get.arguments;
    ChaletModel? chalet;
    if (args is ChaletModel) {
      chalet = args;
    } else if (args is Map<String, dynamic>) {
      if (args['chalet'] is ChaletModel) {
        chalet = args['chalet'] as ChaletModel;
      }
    }

    if (chalet == null) {
      throw Exception('لم يتم تمرير بيانات الشاليه إلى تدفق الحجز');
    }

    if (Get.isRegistered<BookingController>(tag: 'booking')) {
      final controller = Get.find<BookingController>(tag: 'booking');
      controller.updateChalet(chalet);
      return controller;
    }

    return Get.put(
      BookingController(
        Get.find<BookingProvider>(),
        Get.find<ChaletProvider>(),
        chalet: chalet,
      ),
      tag: 'booking',
    );
  }

  @override
  Widget build(BuildContext context) {
    final controller = _resolveController();
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: Text('حجز ${controller.chalet.name}'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'سعر الليلة: ${controller.chalet.pricePerNight.toStringAsFixed(0)} ر.ي',
              style: theme.textTheme.titleMedium,
            ),
            const SizedBox(height: 4),
            Text(
              'الحد الأقصى للضيوف: ${controller.chalet.maxGuests}',
              style: theme.textTheme.bodySmall,
            ),
            const Divider(height: 32),
            _DateSection(controller: controller),
            const SizedBox(height: 24),
            _GuestsSection(controller: controller),
            const SizedBox(height: 24),
            _ExtrasSection(controller: controller),
             const SizedBox(height: 24),
             _SpecialRequestsField(controller: controller),
             const SizedBox(height: 24),
             _PriceSummary(controller: controller),
             const SizedBox(height: 32),
            _ContinueButton(controller: controller),
          ],
        ),
      ),
    );
  }
}

class _DateSection extends StatelessWidget {
  const _DateSection({required this.controller});

  final BookingController controller;

  Future<void> _pickDateRange(BuildContext context) async {
    final now = DateTime.now();
    final initialStart = controller.startDate.value ?? now;
    final initialEnd =
        controller.endDate.value ?? now.add(const Duration(days: 1));

    final picked = await showDateRangePicker(
      context: context,
      firstDate: now,
      lastDate: DateTime(now.year + 1, 12, 31),
      initialDateRange: DateTimeRange(start: initialStart, end: initialEnd),
      locale: const Locale('ar'),
    );

    if (picked != null) {
      controller.setDateRange(picked.start, picked.end);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('التواريخ', style: theme.textTheme.titleMedium),
        const SizedBox(height: 8),
        Obx(() {
          return Row(
            children: [
              Expanded(
                child: _InfoTile(
                  title: 'الوصول',
                  value: controller.checkInDisplay,
                  icon: Icons.login,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _InfoTile(
                  title: 'المغادرة',
                  value: controller.checkOutDisplay,
                  icon: Icons.logout,
                ),
              ),
            ],
          );
        }),
        const SizedBox(height: 10),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => _pickDateRange(context),
            icon: const Icon(Icons.calendar_month),
            label: const Text('اختيار المدة'),
          ),
        ),
        const SizedBox(height: 8),
        Obx(() {
          if (controller.availabilityMessage.value.isEmpty) {
            return const SizedBox.shrink();
          }
          return Text(
            controller.availabilityMessage.value,
            style: theme.textTheme.bodySmall?.copyWith(color: Colors.green),
          );
        }),
        Obx(() {
          if (controller.bookingError.value.isEmpty) {
            return const SizedBox.shrink();
          }
          return Padding(
            padding: const EdgeInsets.only(top: 8),
            child: Text(
              controller.bookingError.value,
              style: theme.textTheme.bodySmall?.copyWith(color: Colors.red),
            ),
          );
        }),
      ],
    );
  }
}

class _GuestsSection extends StatelessWidget {
  const _GuestsSection({required this.controller});

  final BookingController controller;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('عدد الضيوف', style: theme.textTheme.titleMedium),
        const SizedBox(height: 8),
        Obx(() {
          return Row(
            children: [
              IconButton(
                onPressed: controller.guests.value > 1
                    ? controller.decrementGuests
                    : null,
                icon: const Icon(Icons.remove_circle_outline),
              ),
              Text(
                '${controller.guests.value}',
                style: theme.textTheme.titleLarge,
              ),
              IconButton(
                onPressed: controller.guests.value < controller.chalet.maxGuests
                    ? controller.incrementGuests
                    : null,
                icon: const Icon(Icons.add_circle_outline),
              ),
            ],
          );
        }),
      ],
    );
  }
}

class _ExtrasSection extends StatelessWidget {
  const _ExtrasSection({required this.controller});

  final BookingController controller;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('إضافات اختيارية', style: theme.textTheme.titleMedium),
        const SizedBox(height: 12),
        Wrap(
          spacing: 10,
          runSpacing: 10,
          children: controller.extraOptions.map((option) {
            return Obx(() {
              return FilterChip(
                label: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(option.name),
                    Text(
                      '+${option.price.toStringAsFixed(0)} ر.ي',
                      style: theme.textTheme.bodySmall,
                    ),
                  ],
                ),
                selected: option.selected.value,
                onSelected: (_) => controller.toggleExtra(option.id),
              );
            });
          }).toList(),
        ),
      ],
    );
  }
}

class _SpecialRequestsField extends StatelessWidget {
  const _SpecialRequestsField({required this.controller});

  final BookingController controller;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller.specialRequestsCtrl,
      maxLines: 4,
      decoration: const InputDecoration(
        labelText: 'طلبات خاصة (اختياري)',
        border: OutlineInputBorder(),
      ),
    );
  }
}

class _PriceSummary extends StatelessWidget {
  const _PriceSummary({required this.controller});

  final BookingController controller;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Obx(() {
      final nights = controller.nights;
      final base = controller.baseAmount;
      final extras = controller.extrasTotal;
      final total = controller.grandTotal;
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('الملخص', style: theme.textTheme.titleMedium),
          const SizedBox(height: 12),
          _SummaryRow(label: 'عدد الليالي', value: '$nights'),
          _SummaryRow(
            label: 'إجمالي الإقامة',
            value: '${base.toStringAsFixed(2)} ر.ي',
          ),
          _SummaryRow(
            label: 'إجمالي الإضافات',
            value: '${extras.toStringAsFixed(2)} ر.ي',
          ),
          const Divider(height: 24),
          _SummaryRow(
            label: 'الإجمالي المتوقع',
            value: '${total.toStringAsFixed(2)} ر.ي',
            isBold: true,
          ),
        ],
      );
    });
  }
}

class _ContinueButton extends StatelessWidget {
  const _ContinueButton({required this.controller});

  final BookingController controller;

  @override
  Widget build(BuildContext context) {
    return Obx(() {
      final isBusy = controller.isCheckingAvailability.value ||
          controller.isSubmittingBooking.value;
      return SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: isBusy ? null : () => controller.goToSummary(),
          child: isBusy
              ? const SizedBox(
                  width: 22,
                  height: 22,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : const Text('متابعة للملخص'),
        ),
      );
    });
  }
}

class _SummaryRow extends StatelessWidget {
  const _SummaryRow({
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

class _InfoTile extends StatelessWidget {
  const _InfoTile({
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
          const SizedBox(width: 8),
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
