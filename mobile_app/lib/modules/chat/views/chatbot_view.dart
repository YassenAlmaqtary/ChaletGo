import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../../core/constants/app_colors.dart';
import '../controllers/chatbot_controller.dart';

class ChatbotView extends StatelessWidget {
  const ChatbotView({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<ChatbotController>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('المساعد'),
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: AppColors.getBackgroundGradient(context),
        ),
        child: SafeArea(
          child: Column(
            children: [
              Expanded(
                child: Obx(() {
                  final msgs = controller.messages;
                  return ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: msgs.length + (controller.isLoading.value ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index >= msgs.length) {
                        return const Padding(
                          padding: EdgeInsets.only(top: 8),
                          child: Align(
                            alignment: Alignment.centerLeft,
                            child: _TypingBubble(),
                          ),
                        );
                      }

                      final m = msgs[index];
                      return _MessageBubble(
                        isUser: m.isUser,
                        text: m.text,
                        citations: m.citations,
                      );
                    },
                  );
                }),
              ),
              _Composer(controller: controller),
            ],
          ),
        ),
      ),
    );
  }
}

class _Composer extends StatelessWidget {
  final ChatbotController controller;
  const _Composer({required this.controller});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: controller.inputCtrl,
              minLines: 1,
              maxLines: 4,
              textInputAction: TextInputAction.send,
              onSubmitted: (_) => controller.send(),
              decoration: InputDecoration(
                hintText: 'اكتب سؤالك...',
                filled: true,
                fillColor: Theme.of(context).cardColor,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          Obx(() {
            return IconButton(
              onPressed: controller.isLoading.value ? null : controller.send,
              icon: const Icon(Icons.send_rounded),
              color: AppColors.primary,
            );
          }),
        ],
      ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  final bool isUser;
  final String text;
  final List<Map<String, dynamic>>? citations;

  const _MessageBubble({
    required this.isUser,
    required this.text,
    this.citations,
  });

  @override
  Widget build(BuildContext context) {
    final align = isUser ? Alignment.centerRight : Alignment.centerLeft;
    final bg = isUser ? AppColors.primary : Theme.of(context).cardColor;
    final fg = isUser ? Colors.white : (Theme.of(context).brightness == Brightness.dark ? Colors.white : AppColors.dark);

    return Align(
      alignment: align,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(12),
        constraints: const BoxConstraints(maxWidth: 520),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              text,
              style: TextStyle(color: fg, height: 1.4),
            ),
            if (!isUser && citations != null && citations!.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(
                'المصادر',
                style: TextStyle(
                  color: fg.withOpacity(0.9),
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
              const SizedBox(height: 6),
              ...citations!.take(3).map((c) {
                final source = (c['source'] ?? '').toString();
                final snippet = (c['snippet'] ?? '').toString();
                return Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Text(
                    '- $source: ${snippet.isEmpty ? '' : snippet}',
                    style: TextStyle(color: fg.withOpacity(0.85), fontSize: 12, height: 1.3),
                  ),
                );
              }),
            ],
          ],
        ),
      ),
    );
  }
}

class _TypingBubble extends StatelessWidget {
  const _TypingBubble();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(14),
      ),
      child: const Text('...'),
    );
  }
}

