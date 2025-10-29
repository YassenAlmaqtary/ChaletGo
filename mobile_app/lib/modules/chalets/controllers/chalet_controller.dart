import 'package:get/get.dart';

import '../../../data/models/chalet_model.dart';
import '../../../data/providers/chalet_provider.dart';

class ChaletController extends GetxController {
  final ChaletProvider chaletProvider;

  var chalets = <ChaletModel>[].obs;
  var isLoading = false.obs;
  var errorMessage = ''.obs;

  ChaletController(this.chaletProvider);

  @override
  void onInit() {
    super.onInit();
    fetchChalets();
  }

  Future<void> fetchChalets() async {
    errorMessage.value = '';
    try {
      isLoading.value = true;
      final res = await chaletProvider.fetchChalets();
      chalets.assignAll(
          res.map((e) => ChaletModel.fromJson(e as Map<String, dynamic>)));
    } catch (e) {
      errorMessage.value = 'تعذر تحميل الشاليهات';
    } finally {
      isLoading.value = false;
    }
  }
}
