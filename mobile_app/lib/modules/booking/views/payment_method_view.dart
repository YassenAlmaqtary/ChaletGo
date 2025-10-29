import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/booking_controller.dart';

class PaymentMethodView extends StatelessWidget {
  const PaymentMethodView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<BookingController>(tag: 'booking');
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('اختيار وسيلة الدفع')),
      body: Obx(() {
        final booking = controller.currentBooking.value;
        if (booking == null) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Text(
                'لم يتم إنشاء حجز بعد. يرجى العودة للملخص.',
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyMedium,
              ),
            ),
          );
        }

        return SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('رقم الحجز: ${booking.bookingNumber}',
                  style: theme.textTheme.titleMedium),
              const SizedBox(height: 6),
              Text(
                  'المبلغ المستحق: ${booking.finalAmount.toStringAsFixed(2)} ر.س'),
              const Divider(height: 32),
              Text('طريقة الدفع', style: theme.textTheme.titleMedium),
              const SizedBox(height: 12),
              ..._paymentMethods.map((method) {
                return Obx(() => RadioListTile<String>(
                      value: method.value,
                      groupValue: controller.paymentMethod.value,
                      onChanged: (val) {
                        if (val != null) controller.paymentMethod.value = val;
                      },
                      title: Text(method.label),
                      subtitle: method.description != null
                          ? Text(method.description!,
                              style: theme.textTheme.bodySmall)
                          : null,
                    ));
              }),
              const SizedBox(height: 16),
              Obx(() {
                if (controller.paymentMethod.value != 'credit_card') {
                  return const SizedBox.shrink();
                }
                return _CreditCardForm(controller: controller);
              }),
              const SizedBox(height: 16),
              Obx(() {
                final error = controller.paymentError.value;
                if (error.isEmpty) {
                  return const SizedBox.shrink();
                }
                return Text(
                  error,
                  style: theme.textTheme.bodySmall?.copyWith(color: Colors.red),
                );
              }),
              const SizedBox(height: 20),
              Obx(() {
                final isLoading = controller.isProcessingPayment.value;
                return SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: isLoading ? null : controller.submitPayment,
                    child: isLoading
                        ? const SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('إتمام الدفع'),
                  ),
                );
              }),
            ],
          ),
        );
      }),
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

class _CreditCardForm extends StatelessWidget {
  const _CreditCardForm({required this.controller});

  final BookingController controller;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
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
