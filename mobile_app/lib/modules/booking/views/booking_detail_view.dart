import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:intl/intl.dart';

import '../../../data/models/booking_model.dart';
import '../../../data/models/payment_model.dart';
import '../controllers/booking_detail_controller.dart';
import '../../../data/providers/booking_provider.dart';
import '../../../data/providers/review_provider.dart';

class BookingDetailView extends StatelessWidget {
  const BookingDetailView({super.key});

  BookingDetailController _resolveController() {
    final args = Get.arguments;
    BookingModel? initial;
    String? bookingNumber;

    if (args is BookingModel) {
      initial = args;
      bookingNumber = args.bookingNumber;
    } else if (args is Map<String, dynamic>) {
      if (args['booking'] is BookingModel) {
        initial = args['booking'] as BookingModel;
      }
      final number = args['bookingNumber'];
      if (number is String && number.isNotEmpty) {
        bookingNumber = number;
      }
    } else if (args is Map && args['bookingNumber'] is String) {
      bookingNumber = args['bookingNumber'] as String;
    }

    bookingNumber ??= initial?.bookingNumber;

    if (bookingNumber == null || bookingNumber.isEmpty) {
      throw Exception('لم يتم تمرير رقم الحجز لعرض التفاصيل');
    }

    final tag = 'booking-detail-$bookingNumber';
    if (Get.isRegistered<BookingDetailController>(tag: tag)) {
      final controller = Get.find<BookingDetailController>(tag: tag);
      if (initial != null) {
        controller.booking.value = initial;
      }
      return controller;
    }

    return Get.put(
      BookingDetailController(
        Get.find<BookingProvider>(),
        Get.find<ReviewProvider>(),
        bookingNumber: bookingNumber,
        initialBooking: initial,
      ),
      tag: tag,
    );
  }

  @override
  Widget build(BuildContext context) {
    final controller = _resolveController();

    return WillPopScope(
      onWillPop: () async {
        Get.back(result: controller.hasMutations);
        return false;
      },
      child: Scaffold(
        appBar: AppBar(
          title: const Text('تفاصيل الحجز'),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed: () => Get.back(result: controller.hasMutations),
          ),
        ),
        body: Obx(() {
          if (controller.isLoading.value && controller.current == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.errorMessage.value.isNotEmpty &&
              controller.current == null) {
            return _CenteredMessage(
              message: controller.errorMessage.value,
              onRetry: controller.fetchBooking,
            );
          }

          return RefreshIndicator(
            onRefresh: controller.fetchBooking,
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20),
              child: Obx(() {
                final booking = controller.current;
                if (booking == null) {
                  return _CenteredMessage(
                    message: 'تعذر تحميل تفاصيل الحجز',
                    onRetry: controller.fetchBooking,
                  );
                }

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _BookingHeader(booking: booking),
                    const SizedBox(height: 16),
                    _StatusChip(statusLabel: booking.statusLabel),
                    const SizedBox(height: 24),
                    if (booking.status == 'pending') ...[
                      _PendingNotice(),
                      const SizedBox(height: 24),
                    ],
                    _InfoSection(booking: booking),
                    const SizedBox(height: 24),
                    if (booking.specialRequests?.isNotEmpty == true) ...[
                      _SectionTitle('الطلبات الخاصة'),
                      const SizedBox(height: 8),
                      Text(booking.specialRequests!),
                      const SizedBox(height: 24),
                    ],
                    if (booking.extras.isNotEmpty) ...[
                      _SectionTitle('الإضافات'),
                      const SizedBox(height: 8),
                      Column(
                        children: booking.extras
                            .map((extra) => ListTile(
                                  title: Text(extra.name),
                                  subtitle: Text('الكمية: ${extra.quantity}'),
                                  trailing: Text(
                                      '${extra.totalPrice.toStringAsFixed(2)} ر.س'),
                                ))
                            .toList(),
                      ),
                      const SizedBox(height: 24),
                    ],
                    _SectionTitle('المدفوعات'),
                    const SizedBox(height: 8),
                    if (controller.payments.isEmpty)
                      Text(
                        'لم يتم تسجيل مدفوعات بعد',
                        style: Theme.of(context)
                            .textTheme
                            .bodySmall
                            ?.copyWith(color: Colors.grey[600]),
                      )
                    else
                      Column(
                        children: controller.payments
                            .map((payment) => _PaymentTile(payment: payment))
                            .toList(),
                      ),
                    if (controller.requiresPayment) ...[
                      const SizedBox(height: 16),
                      _PaymentActions(controller: controller),
                    ],
                    const SizedBox(height: 24),
                    _PriceSummary(booking: booking),
                    const SizedBox(height: 24),
                    if (controller.canCancel)
                      _CancelButton(controller: controller),
                    const SizedBox(height: 24),
                    _ReviewSection(controller: controller),
                  ],
                );
              }),
            ),
          );
        }),
      ),
    );
  }
}

class _BookingHeader extends StatelessWidget {
  const _BookingHeader({required this.booking});

  final BookingModel booking;

  @override
  Widget build(BuildContext context) {
    final formatter = DateFormat('dd MMM yyyy', 'ar');
    return Card(
      elevation: 2,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('رقم الحجز: ${booking.bookingNumber}',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _InfoTile(
                    label: 'الوصول',
                    value: formatter.format(booking.checkInDate),
                    icon: Icons.login,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _InfoTile(
                    label: 'المغادرة',
                    value: formatter.format(booking.checkOutDate),
                    icon: Icons.logout,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            _InfoTile(
              label: 'عدد الضيوف',
              value: '${booking.guestsCount}',
              icon: Icons.group,
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.statusLabel});

  final String statusLabel;

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerRight,
      child: Chip(
        label: Text(statusLabel.isEmpty ? 'غير محدد' : statusLabel),
        backgroundColor: Theme.of(context).colorScheme.primary.withOpacity(0.1),
        labelStyle: TextStyle(
          color: Theme.of(context).colorScheme.primary,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}

class _InfoSection extends StatelessWidget {
  const _InfoSection({required this.booking});

  final BookingModel booking;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _SectionTitle('الشاليه'),
        const SizedBox(height: 8),
        Card(
          margin: EdgeInsets.zero,
          child: ListTile(
            leading: const Icon(Icons.home_work_outlined),
            title: Text(booking.chalet?.name ?? 'شاليه غير معروف'),
            subtitle: Text(booking.chalet?.location ?? ''),
          ),
        ),
        const SizedBox(height: 16),
        if (booking.bookingDetails != null) ...[
          _SectionTitle('تفاصيل الحجز'),
          const SizedBox(height: 8),
          Card(
            margin: EdgeInsets.zero,
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: booking.bookingDetails!.entries
                    .map((entry) => Padding(
                          padding: const EdgeInsets.symmetric(vertical: 4),
                          child: Row(
                            children: [
                              Expanded(
                                  child: Text(entry.key.toString(),
                                      style: Theme.of(context)
                                          .textTheme
                                          .bodySmall)),
                              const SizedBox(width: 12),
                              Text(entry.value.toString()),
                            ],
                          ),
                        ))
                    .toList(),
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class _PaymentTile extends StatelessWidget {
  const _PaymentTile({required this.payment});

  final PaymentModel payment;

  @override
  Widget build(BuildContext context) {
    final statusColor = _statusColor(payment.status, context);
    final paidAt = payment.paidAt != null
        ? DateFormat('yyyy-MM-dd HH:mm').format(payment.paidAt!)
        : '—';
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 6),
      child: ListTile(
        leading: Icon(Icons.payments_outlined, color: statusColor),
        title: Text('${payment.amount.toStringAsFixed(2)} ر.س'),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('الطريقة: ${payment.paymentMethod}'),
            Text('الحالة: ${payment.status}'),
            Text('تاريخ الدفع: $paidAt'),
          ],
        ),
      ),
    );
  }

  Color _statusColor(String status, BuildContext context) {
    switch (status) {
      case 'completed':
        return Colors.green;
      case 'failed':
        return Colors.red;
      case 'pending':
      default:
        return Theme.of(context).colorScheme.primary;
    }
  }
}

class _PriceSummary extends StatelessWidget {
  const _PriceSummary({required this.booking});

  final BookingModel booking;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _SectionTitle('الملخص المالي'),
            const SizedBox(height: 12),
            _SummaryRow(
              label: 'عدد الليالي',
              value: '${booking.totalNights}',
            ),
            _SummaryRow(
              label: 'إجمالي الإقامة',
              value: '${booking.totalAmount.toStringAsFixed(2)} ر.ي',
            ),
            _SummaryRow(
              label: 'إجمالي الخصم',
              value: '${booking.discountAmount.toStringAsFixed(2)} ر.ي',
            ),
            _SummaryRow(
              label: 'الإجمالي النهائي',
              value: '${booking.finalAmount.toStringAsFixed(2)} ر.ي',
              isBold: true,
            ),
          ],
        ),
      ),
    );
  }
}

class _CancelButton extends StatelessWidget {
  const _CancelButton({required this.controller});

  final BookingDetailController controller;

  @override
  Widget build(BuildContext context) {
    return Obx(() {
      if (!controller.canCancel) {
        return const SizedBox.shrink();
      }
      return SizedBox(
        width: double.infinity,
        child: ElevatedButton.icon(
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.redAccent,
          ),
          onPressed: controller.isCancelling.value
              ? null
              : () => _showCancelDialog(context, controller),
          icon: controller.isCancelling.value
              ? const SizedBox(
                  width: 16,
                  height: 16,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.white,
                  ),
                )
              : const Icon(Icons.cancel_outlined),
          label: const Text('إلغاء الحجز'),
        ),
      );
    });
  }

  void _showCancelDialog(
      BuildContext context, BookingDetailController controller) {
    controller.cancelReasonCtrl.clear();
    Get.defaultDialog(
      title: 'إلغاء الحجز',
      radius: 12,
      contentPadding: const EdgeInsets.all(16),
      content: Column(
        children: [
          const Text('يرجى كتابة سبب الإلغاء'),
          const SizedBox(height: 12),
          TextField(
            controller: controller.cancelReasonCtrl,
            maxLines: 3,
            decoration: const InputDecoration(
              border: OutlineInputBorder(),
              hintText: 'سبب الإلغاء',
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: Obx(() => ElevatedButton(
                  onPressed: controller.isCancelling.value
                      ? null
                      : () async {
                          final success =
                              await controller.requestCancellation();
                          if (success) {
                            Get.back();
                            Get.snackbar('تم', 'تم إلغاء الحجز بنجاح',
                                snackPosition: SnackPosition.BOTTOM);
                          } else {
                            final msg = controller.errorMessage.value;
                            if (msg.isNotEmpty) {
                              Get.snackbar('خطأ', msg,
                                  snackPosition: SnackPosition.BOTTOM,
                                  backgroundColor: Colors.redAccent,
                                  colorText: Colors.white);
                            }
                          }
                        },
                  child: controller.isCancelling.value
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                              strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('تأكيد الإلغاء'),
                )),
          ),
        ],
      ),
    );
  }
}

class _ReviewSection extends StatelessWidget {
  const _ReviewSection({required this.controller});

  final BookingDetailController controller;

  @override
  Widget build(BuildContext context) {
    return Obx(() {
      final review = controller.review;
      if (review != null) {
        return Card(
          margin: EdgeInsets.zero,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _SectionTitle('تقييمك'),
                const SizedBox(height: 12),
                Row(
                  children: List.generate(5, (index) {
                    final filled = index < review.rating;
                    return Icon(
                      filled ? Icons.star : Icons.star_border,
                      color: filled ? Colors.amber : Colors.grey,
                    );
                  }),
                ),
                if (review.comment?.isNotEmpty == true) ...[
                  const SizedBox(height: 12),
                  Text(review.comment!),
                ],
                const SizedBox(height: 12),
                Text(
                  review.isApproved
                      ? 'تمت الموافقة على التقييم'
                      : 'قيد المراجعة',
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(color: Colors.grey[600]),
                ),
              ],
            ),
          ),
        );
      }

      if (!controller.canReview) {
        return const SizedBox.shrink();
      }

      return Card(
        margin: EdgeInsets.zero,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _SectionTitle('أضف تقييمك'),
              const SizedBox(height: 12),
              Row(
                children: List.generate(5, (index) {
                  final ratingValue = index + 1;
                  return IconButton(
                    icon: Icon(
                      controller.reviewRating.value >= ratingValue
                          ? Icons.star
                          : Icons.star_border,
                      color: Colors.amber,
                    ),
                    onPressed: () =>
                        controller.reviewRating.value = ratingValue,
                  );
                }),
              ),
              TextField(
                controller: controller.reviewCommentCtrl,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'تعليق (اختياري)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 16),
              Obx(() {
                final error = controller.errorMessage.value;
                if (error.isEmpty) {
                  return const SizedBox.shrink();
                }
                return Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Text(
                    error,
                    style: const TextStyle(color: Colors.red),
                  ),
                );
              }),
              SizedBox(
                width: double.infinity,
                child: Obx(() => ElevatedButton(
                      onPressed: controller.isSubmittingReview.value
                          ? null
                          : () async {
                              final success = await controller.submitReview();
                              if (success) {
                                Get.snackbar('تم', 'تم إرسال التقييم بنجاح',
                                    snackPosition: SnackPosition.BOTTOM);
                              }
                            },
                      child: controller.isSubmittingReview.value
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                  strokeWidth: 2, color: Colors.white),
                            )
                          : const Text('إرسال التقييم'),
                    )),
              ),
            ],
          ),
        ),
      );
    });
  }
}

class _PendingNotice extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.orange.withOpacity(0.1),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            const Icon(Icons.access_time, color: Colors.orange),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                'حجزك قيد المراجعة من قبل مالك الشاليه. سيتم إشعارك عند التأكيد لإتمام الدفع.',
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaymentActions extends StatelessWidget {
  const _PaymentActions({required this.controller});

  final BookingDetailController controller;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _SectionTitle('إتمام الدفع'),
            const SizedBox(height: 12),
            ..._paymentMethods.map((method) => Obx(() {
                  return RadioListTile<String>(
                    value: method.value,
                    groupValue: controller.paymentMethod.value,
                    title: Text(method.label),
                    subtitle: method.description != null
                        ? Text(method.description!)
                        : null,
                    onChanged: (val) {
                      if (val != null) {
                        controller.paymentMethod.value = val;
                      }
                    },
                  );
                })),
            Obx(() {
              if (controller.paymentMethod.value != 'credit_card') {
                return const SizedBox.shrink();
              }
              return _CreditCardFields(controller: controller);
            }),
            const SizedBox(height: 12),
            Obx(() {
              final error = controller.paymentError.value;
              if (error.isEmpty) {
                return const SizedBox.shrink();
              }
              return Text(
                error,
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: Colors.red),
              );
            }),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: Obx(() => ElevatedButton(
                    onPressed: controller.isProcessingPayment.value
                        ? null
                        : () => controller.submitPayment(),
                    child: controller.isProcessingPayment.value
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                                strokeWidth: 2, color: Colors.white),
                          )
                        : const Text('إتمام الدفع الآن'),
                  )),
            ),
          ],
        ),
      ),
    );
  }
}

class _CreditCardFields extends StatelessWidget {
  const _CreditCardFields({required this.controller});

  final BookingDetailController controller;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        TextField(
          controller: controller.cardNumberCtrl,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(
            labelText: 'رقم البطاقة',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: controller.cardHolderCtrl,
          decoration: const InputDecoration(
            labelText: 'اسم صاحب البطاقة',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: controller.expiryMonthCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'شهر الانتهاء',
                  border: OutlineInputBorder(),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: TextField(
                controller: controller.expiryYearCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'سنة الانتهاء',
                  border: OutlineInputBorder(),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        TextField(
          controller: controller.cvcCtrl,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(
            labelText: 'رمز التحقق (CVC)',
            border: OutlineInputBorder(),
          ),
        ),
      ],
    );
  }
}

class _PaymentMethodInfo {
  const _PaymentMethodInfo(this.value, this.label, [this.description]);

  final String value;
  final String label;
  final String? description;
}

const _paymentMethods = <_PaymentMethodInfo>[
  _PaymentMethodInfo('cash', 'دفع نقدي عند الوصول'),
  _PaymentMethodInfo(
      'bank_transfer', 'تحويل بنكي', 'سيتم تأكيد الحجز بعد مراجعة التحويل'),
  _PaymentMethodInfo(
      'digital_wallet', 'محفظة إلكترونية', 'مثل STC Pay أو Apple Pay (محاكاة)'),
  _PaymentMethodInfo('credit_card', 'بطاقة ائتمان/مدى'),
];

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.title);

  final String title;

  @override
  Widget build(BuildContext context) {
    return Text(
      title,
      style: Theme.of(context)
          .textTheme
          .titleMedium
          ?.copyWith(fontWeight: FontWeight.bold),
    );
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
    final style = isBold
        ? Theme.of(context)
            .textTheme
            .titleMedium
            ?.copyWith(fontWeight: FontWeight.bold)
        : Theme.of(context).textTheme.bodyMedium;
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
    required this.label,
    required this.value,
    required this.icon,
  });

  final String label;
  final String value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: Theme.of(context).dividerColor),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, size: 20),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: Theme.of(context).textTheme.bodySmall),
              Text(value, style: Theme.of(context).textTheme.titleMedium),
            ],
          )
        ],
      ),
    );
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
            Text(message, textAlign: TextAlign.center),
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
