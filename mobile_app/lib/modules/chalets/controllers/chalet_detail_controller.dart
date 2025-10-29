import 'package:get/get.dart';

import '../../../data/models/chalet_model.dart';
import '../../../data/providers/chalet_provider.dart';

class ChaletDetailController extends GetxController {
  final ChaletProvider chaletProvider;

  final chalet = Rxn<ChaletModel>();
  final isLoading = false.obs;
  final errorMessage = ''.obs;
  final currentImageIndex = 0.obs;
  String? _chaletSlug;

  ChaletDetailController(this.chaletProvider);

  @override
  void onInit() {
    super.onInit();
    final args = Get.arguments;
    ChaletModel? initial;
    String? slug;

    if (args is ChaletModel) {
      initial = args;
      slug = args.slug;
    } else if (args is Map<String, dynamic>) {
      if (args['chalet'] is ChaletModel) {
        initial = args['chalet'] as ChaletModel;
      }
      final argSlug = args['slug'];
      if (argSlug is String && argSlug.isNotEmpty) {
        slug = argSlug;
      }
    } else if (Get.parameters['slug'] != null) {
      slug = Get.parameters['slug'];
    }

    if (initial != null) {
      chalet.value = initial;
      currentImageIndex.value = 0;
      _chaletSlug = initial.slug;
    }

    if (slug != null) {
      _chaletSlug = slug;
      loadChalet(slug);
    } else if (initial == null) {
      errorMessage.value = 'الشاليه غير متاح';
    }
  }

  Future<void> loadChalet(String slug) async {
    try {
      errorMessage.value = '';
      isLoading.value = true;
      final data = await chaletProvider.fetchChalet(slug);
      if (data != null) {
        chalet.value = ChaletModel.fromJson(data);
        _chaletSlug = chalet.value?.slug;
        currentImageIndex.value = 0;
      } else if (chalet.value == null) {
        errorMessage.value = 'تعذر تحميل تفاصيل الشاليه';
      }
    } catch (_) {
      if (chalet.value == null) {
        errorMessage.value = 'تعذر تحميل تفاصيل الشاليه';
      }
    } finally {
      isLoading.value = false;
    }
  }

  void onImageChanged(int index) {
    currentImageIndex.value = index;
  }

  String? get chaletSlug => _chaletSlug ?? chalet.value?.slug;
}
