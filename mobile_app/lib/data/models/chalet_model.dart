class ChaletModel {
  final int id;
  final String name;
  final String slug;
  final String description;
  final String location;
  final double pricePerNight;
  final int maxGuests;
  final int bedrooms;
  final int bathrooms;
  final double rating;
  final int totalReviews;
  final List<String> images;

  ChaletModel({
    required this.id,
    required this.name,
    required this.slug,
    required this.description,
    required this.location,
    required this.pricePerNight,
    required this.maxGuests,
    required this.bedrooms,
    required this.bathrooms,
    required this.rating,
    required this.totalReviews,
    required this.images,
  });

  factory ChaletModel.fromJson(Map<String, dynamic> json) {
    final imagesData = json['images'] as List<dynamic>? ?? [];
    return ChaletModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String? ?? '',
      location: json['location'] as String? ?? '',
      pricePerNight: (json['price_per_night'] as num?)?.toDouble() ?? 0,
      maxGuests: json['max_guests'] as int? ?? 0,
      bedrooms: json['bedrooms'] as int? ?? 0,
      bathrooms: json['bathrooms'] as int? ?? 0,
      rating: (json['rating']?['average'] as num?)?.toDouble() ?? 0,
      totalReviews: json['rating']?['total_reviews'] as int? ?? 0,
      images: imagesData
          .map((e) => (e as Map<String, dynamic>)['url'] as String? ?? '')
          .where((url) => url.isNotEmpty)
          .toList(),
    );
  }
}
