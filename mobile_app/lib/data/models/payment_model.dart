class PaymentModel {
  final int id;
  final int bookingId;
  final double amount;
  final String status;
  final String paymentMethod;
  final String? transactionId;
  final DateTime? paidAt;

  const PaymentModel({
    required this.id,
    required this.bookingId,
    required this.amount,
    required this.status,
    required this.paymentMethod,
    this.transactionId,
    this.paidAt,
  });

  factory PaymentModel.fromJson(Map<String, dynamic> json) {
    return PaymentModel(
      id: json['id'] as int,
      bookingId:
          json['booking_id'] as int? ?? json['booking']?['id'] as int? ?? 0,
      amount: (json['amount'] as num?)?.toDouble() ?? 0,
      status: json['status'] as String? ?? '',
      paymentMethod: json['payment_method'] as String? ?? '',
      transactionId: json['transaction_id'] as String?,
      paidAt: json['paid_at'] != null
          ? DateTime.tryParse(json['paid_at'].toString())
          : null,
    );
  }
}
