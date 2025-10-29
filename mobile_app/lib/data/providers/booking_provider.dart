import 'api_provider.dart';

class BookingProvider {
  final ApiProvider api;
  BookingProvider(this.api);

  Future<Map<String, dynamic>> createBooking(Map<String, dynamic> payload) {
    return api.post('/bookings', payload);
  }

  Future<Map<String, dynamic>> getBookings({Map<String, dynamic>? params}) {
    return api.get('/bookings', params: params);
  }

  Future<Map<String, dynamic>> getBooking(String bookingNumber) {
    return api.get('/bookings/$bookingNumber');
  }

  Future<Map<String, dynamic>> cancelBooking(
    String bookingNumber, {
    required String reason,
  }) {
    return api.put('/bookings/$bookingNumber/cancel', {
      'cancellation_reason': reason,
    });
  }

  Future<Map<String, dynamic>> payForBooking(
    int bookingId,
    Map<String, dynamic> payload,
  ) {
    return api.post('/bookings/$bookingId/payment', payload);
  }
}
