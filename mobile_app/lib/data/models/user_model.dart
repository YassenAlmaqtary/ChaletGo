class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String userType;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.userType,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      userType: json['user_type'] as String? ?? 'customer',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'phone': phone,
        'user_type': userType,
      };
}
