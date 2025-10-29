import 'booking_extra_model.dart';
import 'payment_model.dart';

class BookingModel {
  final int id;
  final String bookingNumber;
  final DateTime checkInDate;
  final DateTime checkOutDate;
  final int guestsCount;
  final int totalNights;
  final double totalAmount;
  final double discountAmount;
  final double finalAmount;
  final String status;
  final String statusLabel;
  final String? specialRequests;
  final Map<String, dynamic>? bookingDetails;
  final bool canBeCancelled;
  final ChaletSummary? chalet;
  final List<BookingExtraModel> extras;
  final List<PaymentModel> payments;

  const BookingModel({
    required this.id,
    required this.bookingNumber,
    required this.checkInDate,
    required this.checkOutDate,
    required this.guestsCount,
    required this.totalNights,
    required this.totalAmount,
    required this.discountAmount,
    required this.finalAmount,
    required this.status,
    required this.statusLabel,
    this.specialRequests,
    this.bookingDetails,
    required this.canBeCancelled,
    this.chalet,
    this.extras = const [],
    this.payments = const [],
  });

  factory BookingModel.fromJson(Map<String, dynamic> json) {
    final extrasJson = json['extras'];
    final paymentsJson = json['payments'];
    return BookingModel(
      id: json['id'] as int,
      bookingNumber: json['booking_number'] as String? ?? '',
      checkInDate: DateTime.parse(json['check_in_date'].toString()),
      checkOutDate: DateTime.parse(json['check_out_date'].toString()),
      guestsCount: json['guests_count'] as int? ?? 0,
      totalNights: json['total_nights'] as int? ?? 0,
      totalAmount: (json['total_amount'] as num?)?.toDouble() ?? 0,
      discountAmount: (json['discount_amount'] as num?)?.toDouble() ?? 0,
      finalAmount: (json['final_amount'] as num?)?.toDouble() ?? 0,
      status: json['status'] as String? ?? '',
      statusLabel: json['status_label'] as String? ?? '',
      specialRequests: json['special_requests'] as String?,
      bookingDetails: json['booking_details'] as Map<String, dynamic>?,
      canBeCancelled: json['can_be_cancelled'] as bool? ?? false,
      chalet: json['chalet'] is Map<String, dynamic>
          ? ChaletSummary.fromJson(json['chalet'] as Map<String, dynamic>)
          : null,
      extras: extrasJson is List
          ? extrasJson
              .whereType<Map<String, dynamic>>()
              .map(BookingExtraModel.fromJson)
              .toList()
          : const [],
      payments: paymentsJson is List
          ? paymentsJson
              .whereType<Map<String, dynamic>>()
              .map(PaymentModel.fromJson)
              .toList()
          : const [],
    );
  }
}

class ChaletSummary {
  final int id;
  final String name;
  final String? slug;
  final String? location;
  final double? pricePerNight;
  final String? primaryImage;

  const ChaletSummary({
    required this.id,
    required this.name,
    this.slug,
    this.location,
    this.pricePerNight,
    this.primaryImage,
  });

  factory ChaletSummary.fromJson(Map<String, dynamic> json) {
    return ChaletSummary(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String?,
      location: json['location'] as String?,
      pricePerNight: (json['price_per_night'] as num?)?.toDouble(),
      primaryImage: json['primary_image'] as String?,
    );
  }
}
