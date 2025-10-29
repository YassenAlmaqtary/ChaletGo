class BookingExtraModel {
  final int? id;
  final String name;
  final double price;
  final int quantity;
  final double totalPrice;

  const BookingExtraModel({
    this.id,
    required this.name,
    required this.price,
    required this.quantity,
    required this.totalPrice,
  });

  factory BookingExtraModel.fromJson(Map<String, dynamic> json) {
    final price = (json['price'] ?? json['extra_price']) as num? ?? 0;
    final qty = json['quantity'] as int? ?? 1;
    final total = (json['total_price'] as num?) ?? price * qty;
    return BookingExtraModel(
      id: json['id'] as int?,
      name: json['name'] as String? ?? json['extra_name'] as String? ?? '',
      price: price.toDouble(),
      quantity: qty,
      totalPrice: total.toDouble(),
    );
  }

  Map<String, dynamic> toPayload() {
    return {
      'name': name,
      'price': price,
      'quantity': quantity,
    };
  }
}
