import 'api_provider.dart';

class AuthProvider {
  final ApiProvider api;
  AuthProvider(this.api);

  Future<Map<String, dynamic>> login(String email, String password) async {
    final res =
        await api.post('/auth/login', {'email': email, 'password': password});
    return res;
  }

  Future<Map<String, dynamic>> register(Map<String, dynamic> data) async {
    final res = await api.post('/auth/register', data);
    return res;
  }

  Future<Map<String, dynamic>> profile() async {
    final res = await api.get('/auth/profile');
    return res;
  }

  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> data) async {
    final res = await api.put('/auth/profile', data);
    return res;
  }

  Future<void> logout() async {
    await api.post('/auth/logout', {});
  }
}
