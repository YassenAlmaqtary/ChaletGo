import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:intl/intl.dart';

import '../../../data/models/booking_model.dart';
import '../../../data/models/payment_model.dart';
import '../../../data/models/review_model.dart';
import '../../../data/providers/booking_provider.dart';
import '../../../data/providers/review_provider.dart';

class BookingDetailController extends GetxController {
  BookingDetailController(
    this._bookingProvider,
    this._reviewProvider, {
    required this.bookingNumber,
    BookingModel? initialBooking,
  }) {
    if (initialBooking != null) {
      booking.value = initialBooking;
    }
  }

  final BookingProvider _bookingProvider;
  final ReviewProvider _reviewProvider;
  final String bookingNumber;

  final booking = Rxn<BookingModel>();
  final isLoading = false.obs;
  final isCancelling = false.obs;
  final isSubmittingReview = false.obs;
  final isProcessingPayment = false.obs;
  final errorMessage = ''.obs;
  final cancelReasonCtrl = TextEditingController();
  final reviewCommentCtrl = TextEditingController();
  final reviewRating = 0.obs;
  final paymentMethod = 'cash'.obs;
  final paymentError = ''.obs;
  final cardNumberCtrl = TextEditingController();
  final cardHolderCtrl = TextEditingController();
  final expiryMonthCtrl = TextEditingController();
  final expiryYearCtrl = TextEditingController();
  final cvcCtrl = TextEditingController();
  bool _hasMutations = false;

  BookingModel? get current => booking.value;

  bool get canCancel => current?.canBeCancelled ?? false;

  bool get hasCompletedPayment {
    final payments = current?.payments ?? const [];
    return payments.any((p) => p.status == 'completed');
  }

  bool get canReview {
    final data = current;
    if (data == null) return false;
    if (data.review != null) return false;
    final status = data.status;
    final checkOut = data.checkOutDate;
    final now = DateTime.now();
    return status == 'completed' ||
        (status == 'confirmed' && !checkOut.isAfter(now));
  }

  List<PaymentModel> get payments => current?.payments ?? const [];

  ReviewModel? get review => current?.review;
  bool get hasMutations => _hasMutations;
  bool get requiresPayment =>
      (current?.status == 'confirmed') && !hasCompletedPayment;

  @override
  void onInit() {
    super.onInit();
    fetchBooking();
  }

  Future<void> fetchBooking() async {
    try {
      isLoading.value = true;
      errorMessage.value = '';
      final response = await _bookingProvider.getBooking(bookingNumber);
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        if (data != null) {
          booking.value = BookingModel.fromJson(data);
          paymentMethod.value = 'cash';
          paymentError.value = '';
          return;
        }
      }
      errorMessage.value =
          response['message']?.toString() ?? 'تعذر تحميل تفاصيل الحجز';
    } catch (_) {
      errorMessage.value = 'حدث خطأ أثناء تحميل تفاصيل الحجز';
    } finally {
      isLoading.value = false;
    }
  }

  Future<bool> requestCancellation() async {
    errorMessage.value = '';
    final reason = cancelReasonCtrl.text.trim();
    if (reason.isEmpty) {
      errorMessage.value = 'يرجى إدخال سبب الإلغاء';
      return false;
    }

    try {
      isCancelling.value = true;
      final response = await _bookingProvider.cancelBooking(
        bookingNumber,
        reason: reason,
      );
      if (response['success'] == true) {
        await fetchBooking();
        _hasMutations = true;
        cancelReasonCtrl.clear();
        return true;
      }
      errorMessage.value =
          response['message']?.toString() ?? 'تعذر إلغاء الحجز';
      return false;
    } catch (_) {
      errorMessage.value = 'حدث خطأ أثناء إلغاء الحجز';
      return false;
    } finally {
      isCancelling.value = false;
    }
  }

  Future<bool> submitReview() async {
    errorMessage.value = '';
    final data = current;
    if (data == null) {
      errorMessage.value = 'لا توجد بيانات للحجز';
      return false;
    }

    if (reviewRating.value < 1 || reviewRating.value > 5) {
      errorMessage.value = 'يرجى اختيار تقييم من 1 إلى 5';
      return false;
    }

    try {
      isSubmittingReview.value = true;
      final response = await _reviewProvider.createReview({
        'booking_id': data.id,
        'rating': reviewRating.value,
        'comment': reviewCommentCtrl.text.trim().isEmpty
            ? null
            : reviewCommentCtrl.text.trim(),
      });

      if (response['success'] == true) {
        reviewRating.value = 0;
        reviewCommentCtrl.clear();
        await fetchBooking();
        _hasMutations = true;
        return true;
      }

      errorMessage.value =
          response['message']?.toString() ?? 'تعذر إرسال التقييم';
      return false;
    } catch (_) {
      errorMessage.value = 'حدث خطأ أثناء إرسال التقييم';
      return false;
    } finally {
      isSubmittingReview.value = false;
    }
  }

  Future<bool> submitPayment() async {
    paymentError.value = '';
    final data = current;
    if (data == null) {
      paymentError.value = 'لا توجد بيانات للحجز';
      return false;
    }

    final method = paymentMethod.value;
    final payload = {
      'payment_method': method,
      'amount': data.finalAmount,
    };

    if (method == 'credit_card') {
      if (cardNumberCtrl.text.trim().length < 13) {
        paymentError.value = 'يرجى إدخال رقم البطاقة بشكل صحيح';
        return false;
      }
      if (cardHolderCtrl.text.trim().isEmpty) {
        paymentError.value = 'يرجى إدخال اسم صاحب البطاقة';
        return false;
      }
      final month = int.tryParse(expiryMonthCtrl.text.trim());
      final year = int.tryParse(expiryYearCtrl.text.trim());
      if (month == null || month < 1 || month > 12) {
        paymentError.value = 'يرجى إدخال شهر انتهاء صالح';
        return false;
      }
      if (year == null || year < DateTime.now().year) {
        paymentError.value = 'يرجى إدخال سنة انتهاء صالحة';
        return false;
      }
      if (cvcCtrl.text.trim().length < 3) {
        paymentError.value = 'يرجى إدخال رمز التحقق الصحيح';
        return false;
      }
      payload.addAll({
        'card_number': cardNumberCtrl.text.trim(),
        'card_holder_name': cardHolderCtrl.text.trim(),
        'expiry_month': month,
        'expiry_year': year,
        'cvc': cvcCtrl.text.trim(),
      });
    }

    if (method == 'digital_wallet') {
      payload['wallet_reference'] =
          'WALLET-${DateTime.now().millisecondsSinceEpoch}';
    }

    try {
      isProcessingPayment.value = true;
      final response = await _bookingProvider.payForBooking(data.id, payload);
      if (response['success'] == true) {
        _hasMutations = true;
        await fetchBooking();
        cardNumberCtrl.clear();
        cardHolderCtrl.clear();
        expiryMonthCtrl.clear();
        expiryYearCtrl.clear();
        cvcCtrl.clear();
        Get.snackbar('تم', 'تم تسجيل عملية الدفع بنجاح',
            snackPosition: SnackPosition.BOTTOM);
        return true;
      }
      paymentError.value =
          response['message']?.toString() ?? 'تعذر إتمام الدفع';
      return false;
    } catch (_) {
      paymentError.value = 'حدث خطأ أثناء معالجة الدفع';
      return false;
    } finally {
      isProcessingPayment.value = false;
    }
  }

  String formatDate(DateTime? date) {
    if (date == null) return '-';
    return DateFormat('yyyy-MM-dd').format(date);
  }

  @override
  void onClose() {
    cancelReasonCtrl.dispose();
    reviewCommentCtrl.dispose();
    cardNumberCtrl.dispose();
    cardHolderCtrl.dispose();
    expiryMonthCtrl.dispose();
    expiryYearCtrl.dispose();
    cvcCtrl.dispose();
    super.onClose();
  }
}
