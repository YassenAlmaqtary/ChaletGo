import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:intl/intl.dart';

import '../../../data/models/booking_extra_model.dart';
import '../../../data/models/booking_model.dart';
import '../../../data/models/chalet_model.dart';
import '../../../data/providers/booking_provider.dart';
import '../../../data/providers/chalet_provider.dart';
import '../../../routes/app_pages.dart';

class BookingController extends GetxController {
  BookingController(
    this._bookingProvider,
    this._chaletProvider, {
    required ChaletModel chalet,
  }) : _chalet = chalet;

  final BookingProvider _bookingProvider;
  final ChaletProvider _chaletProvider;
  ChaletModel _chalet;

  final startDate = Rxn<DateTime>();
  final endDate = Rxn<DateTime>();
  final guests = 1.obs;
  final isCheckingAvailability = false.obs;
  final availabilityMessage = ''.obs;
  final isSubmittingBooking = false.obs;
  final isProcessingPayment = false.obs;
  final paymentError = ''.obs;
  final bookingError = ''.obs;
  final paymentMethod = 'cash'.obs;
  final specialRequestsCtrl = TextEditingController();
  final cardNumberCtrl = TextEditingController();
  final cardHolderCtrl = TextEditingController();
  final expiryMonthCtrl = TextEditingController();
  final expiryYearCtrl = TextEditingController();
  final cvcCtrl = TextEditingController();

  final currentBooking = Rxn<BookingModel>();

  late final List<BookingExtraOption> extraOptions = [
    BookingExtraOption(
      id: 'cleaning',
      name: 'تنظيف إضافي',
      description: 'خدمة تنظيف بعد المغادرة',
      price: 120,
    ),
    BookingExtraOption(
      id: 'breakfast',
      name: 'إفطار يومي',
      description: 'إفطار عربي لشخصين يومياً',
      price: 80,
    ),
    BookingExtraOption(
      id: 'pickup',
      name: 'توصيل من المطار',
      description: 'توصيل خاص بالمطار لشخصين',
      price: 150,
    ),
  ];

  ChaletModel get chalet => _chalet;

  void updateChalet(ChaletModel chalet) {
    _chalet = chalet;
  }

  void setDateRange(DateTime start, DateTime end) {
    startDate.value = DateTime(start.year, start.month, start.day);
    endDate.value = DateTime(end.year, end.month, end.day);
  }

  void incrementGuests() {
    if (guests.value < chalet.maxGuests) {
      guests.value++;
    }
  }

  void decrementGuests() {
    if (guests.value > 1) {
      guests.value--;
    }
  }

  void toggleExtra(String id) {
    for (final option in extraOptions) {
      if (option.id == id) {
        option.selected.toggle();
        break;
      }
    }
  }

  int get nights {
    final start = startDate.value;
    final end = endDate.value;
    if (start == null || end == null) return 0;
    return end.difference(start).inDays;
  }

  double get baseAmount => nights * chalet.pricePerNight;

  double get extrasTotal =>
      selectedExtras.fold(0, (sum, extra) => sum + extra.totalPrice);

  double get grandTotal => baseAmount + extrasTotal;

  List<BookingExtraModel> get selectedExtras {
    return extraOptions
        .where((element) => element.selected.value)
        .map((e) => BookingExtraModel(
              name: e.name,
              price: e.price,
              quantity: 1,
              totalPrice: e.price,
            ))
        .toList();
  }

  String get checkInDisplay => _formatDate(startDate.value);
  String get checkOutDisplay => _formatDate(endDate.value);

  Future<void> goToSummary() async {
    if (isClosed) return;
    bookingError.value = '';
    final valid = await _validateInitialStep();
    if (!valid || isClosed) return;
    Get.toNamed(Routes.bookingSummary);
  }

  Future<bool> _validateInitialStep() async {
    if (isClosed) return false;
    
    final start = startDate.value;
    final end = endDate.value;

    if (start == null || end == null) {
      if (!isClosed) {
        bookingError.value = 'يرجى اختيار تواريخ الوصول والمغادرة';
        Get.snackbar('تنبيه', bookingError.value);
      }
      return false;
    }

    if (!end.isAfter(start)) {
      if (!isClosed) {
        bookingError.value = 'تاريخ المغادرة يجب أن يكون بعد تاريخ الوصول';
        Get.snackbar('تنبيه', bookingError.value);
      }
      return false;
    }

    if (guests.value > chalet.maxGuests) {
      if (!isClosed) {
        bookingError.value =
            'عدد الضيوف يتجاوز الحد الأقصى (${chalet.maxGuests})';
        Get.snackbar('تنبيه', bookingError.value);
      }
      return false;
    }

    if (nights <= 0) {
      if (!isClosed) {
        bookingError.value = 'الرجاء اختيار مدة إقامة صحيحة';
        Get.snackbar('تنبيه', bookingError.value);
      }
      return false;
    }

    try {
      if (isClosed) return false;
      isCheckingAvailability.value = true;
      availabilityMessage.value = '';
      final response = await _chaletProvider.checkAvailability(
        chalet.slug,
        checkInDate: DateFormat('yyyy-MM-dd').format(start),
        checkOutDate: DateFormat('yyyy-MM-dd').format(end),
      );

      if (isClosed) return false;

      final success = response['success'] == true;
      final data = response['data'] as Map<String, dynamic>?;
      final available = data?['available'] == true;

      availabilityMessage.value = response['message']?.toString() ?? '';

      if (!success || !available) {
        if (!isClosed) {
          bookingError.value = availabilityMessage.value.isNotEmpty
              ? availabilityMessage.value
              : 'الشاليه غير متاح في هذه التواريخ';
          Get.snackbar('تنبيه', bookingError.value);
        }
        return false;
      }
    } catch (e) {
      print("Error checking availability: $e");
      if (!isClosed) {
        bookingError.value = 'تعذر التحقق من التوفر حالياً';
        Get.snackbar('تنبيه', bookingError.value);
      }
      return false;
    } finally {
      if (!isClosed) {
        isCheckingAvailability.value = false;
      }
    }

    return true;
  }

  Future<void> confirmBooking() async {
    if (isClosed) return;
    
    final start = startDate.value;
    final end = endDate.value;
    if (start == null || end == null) {
      if (!isClosed) {
        bookingError.value = 'يرجى اختيار التواريخ أولاً';
        Get.snackbar('تنبيه', bookingError.value);
      }
      return;
    }

    if (isClosed) return;
    if (isClosed) return;
    final specialRequests = specialRequestsCtrl.text.trim();
    final payload = {
      'chalet_id': chalet.id,
      'check_in_date': DateFormat('yyyy-MM-dd').format(start),
      'check_out_date': DateFormat('yyyy-MM-dd').format(end),
      'guests_count': guests.value,
      'special_requests': specialRequests.isEmpty ? null : specialRequests,
      'extras': selectedExtras.map((e) => e.toPayload()).toList(),
    };

    isSubmittingBooking.value = true;
    bookingError.value = '';

    try {
      final response = await _bookingProvider.createBooking(payload);
      
      if (isClosed) return;
      
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        if (data != null) {
          currentBooking.value = BookingModel.fromJson(data);
          final status = currentBooking.value?.status ?? '';
          if (status == 'pending') {
            Get.snackbar(
              'تم استلام طلب الحجز',
              'سيتم تأكيد الحجز من قبل مالك الشاليه قريباً. يمكنك متابعة الحالة من حجوزاتي.',
              snackPosition: SnackPosition.BOTTOM,
              duration: const Duration(seconds: 4),
            );
            resetFlow();
            Get.offAllNamed(Routes.main);
          } else {
            Get.toNamed(Routes.paymentMethod);
          }
          return;
        }
      }

      final message = response['message']?.toString() ?? 'تعذر إنشاء الحجز';
      if (!isClosed) {
        bookingError.value = message;
        Get.snackbar('خطأ', message);
      }
    } catch (e) {
      if (!isClosed) {
        bookingError.value = 'حدث خطأ أثناء إنشاء الحجز';
        Get.snackbar('خطأ', bookingError.value);
      }
    } finally {
      if (!isClosed) {
        isSubmittingBooking.value = false;
      }
    }
  }

  Future<void> submitPayment() async {
    if (isClosed) return;
    
    final booking = currentBooking.value;

    if (booking == null) {
      if (!isClosed) {
        paymentError.value = 'لا يوجد حجز صالح للدفع';
        Get.snackbar('تنبيه', paymentError.value);
      }
      return;
    }

    final method = paymentMethod.value;
    final payload = {
      'payment_method': method,
      'amount': booking.finalAmount,
    };

    if (method == 'credit_card') {
      if (isClosed) return;
      final month = int.tryParse(expiryMonthCtrl.text.trim());
      final year = int.tryParse(expiryYearCtrl.text.trim());
      payload.addAll({
        'card_number': cardNumberCtrl.text.trim(),
        'card_holder_name': cardHolderCtrl.text.trim(),
        'cvc': cvcCtrl.text.trim(),
      });
      if (month != null) {
        payload['expiry_month'] = month;
      }
      if (year != null) {
        payload['expiry_year'] = year;
      }
    }

    if (method == 'digital_wallet') {
      payload['wallet_reference'] =
          'WALLET-${DateTime.now().millisecondsSinceEpoch}';
    }

    if (isClosed) return;
    paymentError.value = '';
    isProcessingPayment.value = true;

    try {
      final response =
          await _bookingProvider.payForBooking(booking.id, payload);

      if (isClosed) return;

      final success = response['success'] == true;
      final message = response['message']?.toString() ??
          (success ? 'تم تسجيل عملية الدفع بنجاح' : 'تعذر إتمام الدفع');

      if (success) {
        Get.offNamedUntil(
          Routes.paymentResult,
          (route) => route.settings.name == Routes.paymentResult,
          arguments: {
            'success': true,
            'message': message,
          },
        );
      } else {
        if (!isClosed) {
          paymentError.value = message;
        }
        Get.toNamed(
          Routes.paymentResult,
          arguments: {
            'success': false,
            'message': message,
          },
        );
      }
    } catch (e) {
      print("Payment error: $e");
      if (!isClosed) {
        paymentError.value = 'حدث خطأ أثناء معالجة الدفع';
      }
      Get.toNamed(
        Routes.paymentResult,
        arguments: {
          'success': false,
          'message': paymentError.value,
        },
      );
    } finally {
      if (!isClosed) {
        isProcessingPayment.value = false;
      }
    }
  }

  void resetFlow() {
    if (isClosed) {
      return;
    }
    startDate.value = null;
    endDate.value = null;
    guests.value = 1;
    if (!isClosed) {
      specialRequestsCtrl.clear();
    }
    bookingError.value = '';
    availabilityMessage.value = '';
    currentBooking.value = null;
    for (final option in extraOptions) {
      option.selected.value = false;
    }
    paymentMethod.value = 'cash';
    if (!isClosed) {
      cardNumberCtrl.clear();
      cardHolderCtrl.clear();
      expiryMonthCtrl.clear();
      expiryYearCtrl.clear();
      cvcCtrl.clear();
    }
  }

  @override
  void onClose() {
    specialRequestsCtrl.dispose();
    cardNumberCtrl.dispose();
    cardHolderCtrl.dispose();
    expiryMonthCtrl.dispose();
    expiryYearCtrl.dispose();
    cvcCtrl.dispose();
    super.onClose();
  }

  String _formatDate(DateTime? date) {
    if (date == null) return '-';
    return DateFormat('yyyy-MM-dd').format(date);
  }
}

class BookingExtraOption {
  BookingExtraOption({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
  });

  final String id;
  final String name;
  final String description;
  final double price;
  final RxBool selected = false.obs;
}
