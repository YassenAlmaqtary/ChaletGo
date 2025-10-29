import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../routes/app_pages.dart';
import '../controllers/booking_controller.dart';

class PaymentResultView extends StatelessWidget {
  const PaymentResultView({super.key});

  @override
  Widget build(BuildContext context) {
    final args = (Get.arguments as Map?) ?? <String, dynamic>{};
    print("args: $args");
    final success = args['success'] == true;
    final message = args['message']?.toString() ??
        (success ? 'تم تسجيل عملية الدفع بنجاح' : 'تعذر إتمام عملية الدفع');
    final controller = Get.isRegistered<BookingController>(tag: 'booking')
        ? Get.find<BookingController>(tag: 'booking')
        : null;

    return Scaffold(
      appBar: AppBar(title: const Text('نتيجة الدفع')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              success ? Icons.check_circle : Icons.error_outline,
              size: 90,
              color: success ? Colors.green : Colors.red,
            ),
            const SizedBox(height: 20),
            Text(
              success ? 'تمت عملية الدفع بنجاح' : 'فشلت عملية الدفع',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 12),
            Text(
              message,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  if (success) {
                    controller?.resetFlow();
                    Get.offAllNamed(Routes.bookingList);
                  } else {
                    Get.back();
                  }
                },
                child: Text(success ? 'عرض حجوزاتي' : 'إعادة المحاولة'),
              ),
            ),
            if (success) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () {
                    controller?.resetFlow();
                    Get.offAllNamed(Routes.splash);
                  },
                  child: const Text('العودة للواجهة الرئيسية'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
