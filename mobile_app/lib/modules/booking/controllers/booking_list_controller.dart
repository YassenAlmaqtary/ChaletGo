import 'package:get/get.dart';

import '../../../data/models/booking_model.dart';
import '../../../data/providers/booking_provider.dart';

class BookingListController extends GetxController {
  BookingListController(this._bookingProvider);

  final BookingProvider _bookingProvider;

  final bookings = <BookingModel>[].obs;
  final isLoading = false.obs;
  final isRefreshing = false.obs;
  final errorMessage = ''.obs;

  @override
  void onInit() {
    super.onInit();
    loadBookings();
  }

  Future<void> loadBookings() async {
    if (isLoading.value) return;
    errorMessage.value = '';
    try {
      isLoading.value = true;
      final response = await _bookingProvider.getBookings();
      final success = response['success'] == true;
      if (!success) {
        errorMessage.value =
            response['message']?.toString() ?? 'تعذر تحميل الحجوزات';
        bookings.clear();
        return;
      }

      final data = response['data'];
      Iterable items;
      if (data is List) {
        items = data;
      } else if (data is Map<String, dynamic>) {
        final nested = data['bookings'] ?? data['data'] ?? data['items'];
        if (nested is List) {
          items = nested;
        } else {
          items = const [];
        }
      } else {
        items = const [];
      }

      final models = items
          .whereType<Map<String, dynamic>>()
          .map(BookingModel.fromJson)
          .toList();
      bookings.assignAll(models);
    } catch (_) {
      errorMessage.value = 'تعذر تحميل الحجوزات';
      bookings.clear();
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> refreshBookings() async {
    if (isRefreshing.value) return;
    isRefreshing.value = true;
    try {
      await loadBookings();
    } finally {
      isRefreshing.value = false;
    }
  }
}
