import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:intl/intl.dart';

import '../controllers/booking_list_controller.dart';
import '../../../routes/app_pages.dart';

class BookingListView extends StatelessWidget {
  const BookingListView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<BookingListController>();
    return Scaffold(
      appBar: AppBar(title: const Text('حجوزاتي')),
      body: Obx(() {
        if (controller.isLoading.value && controller.bookings.isEmpty) {
          return const Center(child: CircularProgressIndicator());
        }

        if (controller.errorMessage.value.isNotEmpty &&
            controller.bookings.isEmpty) {
          return _CenteredMessage(
            message: controller.errorMessage.value,
            onRetry: controller.loadBookings,
          );
        }

        return RefreshIndicator(
          onRefresh: controller.refreshBookings,
          child: controller.bookings.isEmpty
              ? ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(24),
                  children: const [
                    _CenteredPlaceholder(
                      title: 'لا توجد حجوزات حالية',
                      subtitle: 'ابدأ بحجز شاليه لتجده هنا فور تأكيده.',
                    ),
                  ],
                )
              : ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemBuilder: (context, index) {
                    final booking = controller.bookings[index];
                    return InkWell(
                      borderRadius: BorderRadius.circular(12),
                      onTap: () async {
                        final result = await Get.toNamed(
                          Routes.bookingDetail,
                          arguments: {
                            'booking': booking,
                            'bookingNumber': booking.bookingNumber,
                          },
                        );
                        if (result == true) {
                          controller.loadBookings();
                        }
                      },
                      child: Card(
                        elevation: 2,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      booking.chalet?.name ?? 'شاليه غير معروف',
                                      style: Theme.of(context)
                                          .textTheme
                                          .titleMedium,
                                    ),
                                  ),
                                  _StatusChip(label: booking.statusLabel),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text('رقم الحجز: ${booking.bookingNumber}'),
                              const SizedBox(height: 6),
                              Text(_formatStay(
                                  booking.checkInDate, booking.checkOutDate)),
                              const SizedBox(height: 6),
                              Text('عدد الضيوف: ${booking.guestsCount}'),
                              const SizedBox(height: 6),
                              Text(
                                  'الإجمالي النهائي: ${booking.finalAmount.toStringAsFixed(2)} ر.س'),
                              if (booking.extras.isNotEmpty) ...[
                                const SizedBox(height: 12),
                                Text('إضافات:',
                                    style: Theme.of(context)
                                        .textTheme
                                        .bodyMedium
                                        ?.copyWith(
                                            fontWeight: FontWeight.bold)),
                                const SizedBox(height: 4),
                                Wrap(
                                  spacing: 8,
                                  runSpacing: 4,
                                  children: booking.extras.map((extra) {
                                    return Chip(
                                      label: Text(
                                        '${extra.name} (+${extra.price.toStringAsFixed(0)} ر.س)',
                                        style: const TextStyle(fontSize: 12),
                                      ),
                                    );
                                  }).toList(),
                                ),
                              ],
                              if (booking.specialRequests?.isNotEmpty ==
                                  true) ...[
                                const SizedBox(height: 12),
                                Text('طلبات خاصة:',
                                    style: Theme.of(context)
                                        .textTheme
                                        .bodyMedium
                                        ?.copyWith(
                                            fontWeight: FontWeight.bold)),
                                const SizedBox(height: 4),
                                Text(booking.specialRequests!),
                              ],
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemCount: controller.bookings.length,
                ),
        );
      }),
    );
  }

  String _formatStay(DateTime checkIn, DateTime checkOut) {
    final formatter = DateFormat('dd MMM yyyy', 'ar');
    return 'الفترة: ${formatter.format(checkIn)} - ${formatter.format(checkOut)}';
  }
}

class _CenteredMessage extends StatelessWidget {
  const _CenteredMessage({required this.message, required this.onRetry});

  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              message,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            OutlinedButton(
              onPressed: onRetry,
              child: const Text('إعادة المحاولة'),
            ),
          ],
        ),
      ),
    );
  }
}

class _CenteredPlaceholder extends StatelessWidget {
  const _CenteredPlaceholder({required this.title, required this.subtitle});

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(Icons.receipt_long,
            size: 72, color: Theme.of(context).colorScheme.primary),
        const SizedBox(height: 12),
        Text(
          title,
          style: Theme.of(context).textTheme.titleMedium,
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 8),
        Text(
          subtitle,
          textAlign: TextAlign.center,
        ),
      ],
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(label.isEmpty ? 'غير محدد' : label),
      backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.1),
      labelStyle: TextStyle(
        color: Theme.of(context).colorScheme.primary,
        fontWeight: FontWeight.bold,
      ),
    );
  }
}
