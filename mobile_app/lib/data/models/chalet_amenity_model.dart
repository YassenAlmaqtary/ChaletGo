class ChaletAmenityModel {
  final int id;
  final String name;
  final String icon;
  final String category;

  const ChaletAmenityModel({
    required this.id,
    required this.name,
    required this.icon,
    required this.category,
  });

  factory ChaletAmenityModel.fromJson(Map<String, dynamic> json) {
    return ChaletAmenityModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      icon: json['icon'] as String? ?? '',
      category: json['category'] as String? ?? 'general',
    );
  }
}
