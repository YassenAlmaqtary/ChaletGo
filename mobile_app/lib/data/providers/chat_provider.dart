import 'api_provider.dart';

class ChatProvider {
  final ApiProvider api;
  ChatProvider(this.api);

  Future<Map<String, dynamic>> chat({
    required String question,
    String? language,
    String? conversationId,
  }) async {
    return api.post('/chat', {
      'question': question,
      if (language != null) 'language': language,
      if (conversationId != null) 'conversation_id': conversationId,
    });
  }
}

