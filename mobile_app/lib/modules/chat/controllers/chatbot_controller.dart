import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../data/providers/chat_provider.dart';

class ChatMessage {
  final bool isUser;
  final String text;
  final List<Map<String, dynamic>>? citations;

  ChatMessage({
    required this.isUser,
    required this.text,
    this.citations,
  });
}

class ChatbotController extends GetxController {
  final ChatProvider chatProvider;
  ChatbotController(this.chatProvider);

  final messages = <ChatMessage>[].obs;
  final isLoading = false.obs;
  final error = ''.obs;
  final inputCtrl = TextEditingController();

  String? _conversationId;

  @override
  void onClose() {
    inputCtrl.dispose();
    super.onClose();
  }

  Future<void> send() async {
    final question = inputCtrl.text.trim();
    if (question.isEmpty || isLoading.value) return;

    error.value = '';
    inputCtrl.clear();

    messages.add(ChatMessage(isUser: true, text: question));
    isLoading.value = true;

    final res = await chatProvider.chat(
      question: question,
      language: Get.locale?.languageCode ?? 'ar',
      conversationId: _conversationId,
    );

    isLoading.value = false;

    final success = res['success'] == true;
    if (!success) {
      error.value = (res['message'] ?? 'حدث خطأ').toString();
      messages.add(ChatMessage(
        isUser: false,
        text: error.value,
      ));
      return;
    }

    final data = res['data'];
    if (data is! Map<String, dynamic>) {
      messages.add(ChatMessage(
        isUser: false,
        text: 'استجابة غير متوقعة',
      ));
      return;
    }

    final answer = (data['answer'] ?? '').toString().trim();
    final citations = data['citations'];

    messages.add(ChatMessage(
      isUser: false,
      text: answer.isEmpty ? 'لا توجد إجابة' : answer,
      citations: citations is List ? citations.cast<Map<String, dynamic>>() : null,
    ));
  }
}

