class ReviewModel {
  final int id;
  final int rating;
  final String? comment;
  final bool isApproved;
  final DateTime createdAt;

  const ReviewModel({
    required this.id,
    required this.rating,
    required this.isApproved,
    required this.createdAt,
    this.comment,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) {
    return ReviewModel(
      id: json['id'] as int,
      rating: json['rating'] as int? ?? 0,
      comment: json['comment'] as String?,
      isApproved: json['is_approved'] as bool? ?? false,
      createdAt:
          DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now(),
    );
  }
}
