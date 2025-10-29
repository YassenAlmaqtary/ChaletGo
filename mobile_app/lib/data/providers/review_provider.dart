import 'api_provider.dart';

class ReviewProvider {
  ReviewProvider(this.api);

  final ApiProvider api;

  Future<Map<String, dynamic>> getReviews({Map<String, dynamic>? params}) {
    return api.get('/reviews', params: params);
  }

  Future<Map<String, dynamic>> createReview(Map<String, dynamic> payload) {
    return api.post('/reviews', payload);
  }

  Future<Map<String, dynamic>> getReview(int id) {
    return api.get('/reviews/$id');
  }
}
