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
    if (status == 'completed') return true;
    if (status != 'confirmed') return false;
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final out = data.checkOutDate;
    final checkOutDay = DateTime(out.year, out.month, out.day);
    return !checkOutDay.isAfter(today);
  }

  /// When the user cannot add a review yet (no existing review), explain why.
  String? get reviewUnavailableHint {
    final data = current;
    if (data == null || data.review != null || canReview) return null;
    switch (data.status) {
      case 'pending':
        return 'بعد موافقة مالك الشاليه على الحجز وانتهاء الإقامة يمكنك تقييم الشاليه من هنا.';
      case 'cancelled':
        return 'لا يمكن تقييم حجز ملغى.';
      case 'confirmed':
        final d = DateFormat('dd/MM/yyyy').format(data.checkOutDate);
        return 'يُتاح إضافة التقييم اعتباراً من تاريخ المغادرة ($d).';
      default:
        return 'التقييم غير متاح لهذا الحجز حالياً.';
    }
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
    if (isClosed) return;
    
    try {
      isLoading.value = true;
      errorMessage.value = '';
      final response = await _bookingProvider.getBooking(bookingNumber);
      
      if (isClosed) return;
      
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        if (data != null) {
          booking.value = BookingModel.fromJson(data);
          paymentMethod.value = 'cash';
          paymentError.value = '';
          return;
        }
      }
      if (!isClosed) {
        errorMessage.value =
            response['message']?.toString() ?? 'تعذر تحميل تفاصيل الحجز';
      }
    } catch (_) {
      if (!isClosed) {
        errorMessage.value = 'حدث خطأ أثناء تحميل تفاصيل الحجز';
      }
    } finally {
      if (!isClosed) {
        isLoading.value = false;
      }
    }
  }

  Future<bool> requestCancellation() async {
    if (isClosed) return false;
    
    errorMessage.value = '';
    if (isClosed) return false;
    final reason = cancelReasonCtrl.text.trim();
    if (reason.isEmpty) {
      if (!isClosed) {
        errorMessage.value = 'يرجى إدخال سبب الإلغاء';
      }
      return false;
    }

    try {
      if (isClosed) return false;
      isCancelling.value = true;
      final response = await _bookingProvider.cancelBooking(
        bookingNumber,
        reason: reason,
      );
      
      if (isClosed) return false;
      
      if (response['success'] == true) {
        await fetchBooking();
        _hasMutations = true;
        if (!isClosed) {
          cancelReasonCtrl.clear();
        }
        return true;
      }
      if (!isClosed) {
        errorMessage.value =
            response['message']?.toString() ?? 'تعذر إلغاء الحجز';
      }
      return false;
    } catch (_) {
      if (!isClosed) {
        errorMessage.value = 'حدث خطأ أثناء إلغاء الحجز';
      }
      return false;
    } finally {
      if (!isClosed) {
        isCancelling.value = false;
      }
    }
  }

  Future<bool> submitReview() async {
    if (isClosed) return false;
    
    errorMessage.value = '';
    final data = current;
    if (data == null) {
      if (!isClosed) {
        errorMessage.value = 'لا توجد بيانات للحجز';
      }
      return false;
    }

    if (reviewRating.value < 1 || reviewRating.value > 5) {
      if (!isClosed) {
        errorMessage.value = 'يرجى اختيار تقييم من 1 إلى 5';
      }
      return false;
    }

    try {
      if (isClosed) return false;
      isSubmittingReview.value = true;
      if (isClosed) return false;
      final comment = reviewCommentCtrl.text.trim();
      final payload = <String, dynamic>{
        'booking_id': data.id,
        'rating': reviewRating.value,
      };
      // Always include comment field if not empty (API will handle null)
      if (comment.isNotEmpty) {
        payload['comment'] = comment;
      }
      final response = await _reviewProvider.createReview(payload);

      if (isClosed) return false;

      if (response['success'] == true) {
        reviewRating.value = 0;
        if (!isClosed) {
          reviewCommentCtrl.clear();
        }
        await fetchBooking();
        _hasMutations = true;
        return true;
      }

      // Handle error response
      if (!isClosed) {
        String errorMsg = response['message']?.toString() ?? 'تعذر إرسال التقييم';
        
        // Check for validation errors
        final errors = response['errors'];
        if (errors != null && errors is Map) {
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            errorMsg = firstError.first.toString();
          } else if (firstError is String) {
            errorMsg = firstError;
          }
        }
        
        errorMessage.value = errorMsg;
      }
      return false;
    } catch (e) {
      if (!isClosed) {
        errorMessage.value = e.toString().contains('Exception:')
            ? e.toString().replaceFirst('Exception: ', '')
            : 'حدث خطأ أثناء إرسال التقييم';
      }
      return false;
    } finally {
      if (!isClosed) {
        isSubmittingReview.value = false;
      }
    }
  }

  Future<bool> submitPayment() async {
    if (isClosed) return false;
    
    paymentError.value = '';
    final data = current;
    if (data == null) {
      if (!isClosed) {
        paymentError.value = 'لا توجد بيانات للحجز';
      }
      return false;
    }

    final method = paymentMethod.value;
    final payload = {
      'payment_method': method,
      'amount': data.finalAmount,
    };

    if (method == 'credit_card') {
      if (isClosed) return false;
      final cardNumber = cardNumberCtrl.text.trim();
      final cardHolder = cardHolderCtrl.text.trim();
      final expiryMonthStr = expiryMonthCtrl.text.trim();
      final expiryYearStr = expiryYearCtrl.text.trim();
      final cvc = cvcCtrl.text.trim();
      
      if (cardNumber.length < 13) {
        if (!isClosed) {
          paymentError.value = 'يرجى إدخال رقم البطاقة بشكل صحيح';
        }
        return false;
      }
      if (cardHolder.isEmpty) {
        if (!isClosed) {
          paymentError.value = 'يرجى إدخال اسم صاحب البطاقة';
        }
        return false;
      }
      final month = int.tryParse(expiryMonthStr);
      final year = int.tryParse(expiryYearStr);
      if (month == null || month < 1 || month > 12) {
        if (!isClosed) {
          paymentError.value = 'يرجى إدخال شهر انتهاء صالح';
        }
        return false;
      }
      if (year == null || year < DateTime.now().year) {
        if (!isClosed) {
          paymentError.value = 'يرجى إدخال سنة انتهاء صالحة';
        }
        return false;
      }
      if (cvc.length < 3) {
        if (!isClosed) {
          paymentError.value = 'يرجى إدخال رمز التحقق الصحيح';
        }
        return false;
      }
      if (isClosed) return false;
      payload.addAll({
        'card_number': cardNumber,
        'card_holder_name': cardHolder,
        'expiry_month': month,
        'expiry_year': year,
        'cvc': cvc,
      });
    }

    if (method == 'digital_wallet') {
      payload['wallet_reference'] =
          'WALLET-${DateTime.now().millisecondsSinceEpoch}';
    }

    try {
      if (isClosed) return false;
      isProcessingPayment.value = true;
      final response = await _bookingProvider.payForBooking(data.id, payload);
      
      if (isClosed) return false;
      
      if (response['success'] == true) {
        _hasMutations = true;
        await fetchBooking();
        if (!isClosed) {
          cardNumberCtrl.clear();
          cardHolderCtrl.clear();
          expiryMonthCtrl.clear();
          expiryYearCtrl.clear();
          cvcCtrl.clear();
          Get.snackbar('تم', 'تم تسجيل عملية الدفع بنجاح',
              snackPosition: SnackPosition.BOTTOM);
        }
        return true;
      }
      if (!isClosed) {
        paymentError.value =
            response['message']?.toString() ?? 'تعذر إتمام الدفع';
      }
      return false;
    } catch (_) {
      if (!isClosed) {
        paymentError.value = 'حدث خطأ أثناء معالجة الدفع';
      }
      return false;
    } finally {
      if (!isClosed) {
        isProcessingPayment.value = false;
      }
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
