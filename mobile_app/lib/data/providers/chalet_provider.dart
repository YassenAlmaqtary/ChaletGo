import 'api_provider.dart';

class ChaletProvider {
  final ApiProvider api;
  ChaletProvider(this.api);

  Future<List<dynamic>> fetchChalets({Map<String, dynamic>? params}) async {
    final res = await api.get('/chalets', params: params);
    if (res['data'] is List) {
      return res['data'] as List<dynamic>;
    }
    if (res['data']?['data'] is List) {
      return res['data']['data'] as List<dynamic>;
    }
    return [];
  }

  Future<Map<String, dynamic>?> fetchChalet(String slug) async {
    final res = await api.get('/chalets/$slug');
    if (res['data'] is Map<String, dynamic>) {
      return res['data'] as Map<String, dynamic>;
    }
    if (res['data']?['data'] is Map<String, dynamic>) {
      return res['data']['data'] as Map<String, dynamic>;
    }
    return null;
  }

  Future<Map<String, dynamic>> checkAvailability(
    String slug, {
    required String checkInDate,
    required String checkOutDate,
  }) async {
    return api.get('/chalets/$slug/availability', params: {
      'check_in_date': checkInDate,
      'check_out_date': checkOutDate,
    });
  }
}
