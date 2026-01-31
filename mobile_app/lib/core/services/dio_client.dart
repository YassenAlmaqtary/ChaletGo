import 'package:dio/dio.dart' hide Response;
import 'package:dio/dio.dart' as dio_package show Response;
import 'package:get/get.dart';
import '../config/app_config.dart';
import '../constants/app_constants.dart';
import 'secure_storage.dart';
import 'auth_service.dart';
import '../../routes/app_pages.dart';

class DioClient {
  late final Dio dio;

  DioClient() {
    final options = BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      connectTimeout: AppConstants.requestTimeout,
      receiveTimeout: AppConstants.requestTimeout,
      headers: {
        'Accept': 'application/json',
      },
    );

    dio = Dio(options);

    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await SecureStorage.getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (e, handler) async {
        // Handle 401 Unauthorized - Token expired or invalid
        if (e.response?.statusCode == 401) {
          // Clear session
          try {
            final authService = Get.find<AuthService>();
            await authService.clearSession();
            
            // Show message to user
            Get.snackbar(
              'انتهت الجلسة',
              'انتهت صلاحية تسجيل الدخول. يرجى تسجيل الدخول مرة أخرى.',
              snackPosition: SnackPosition.BOTTOM,
              duration: const Duration(seconds: 3),
            );
            
            // Redirect to login if not already there
            if (Get.currentRoute != Routes.login) {
              Get.offAllNamed(Routes.login);
            }
          } catch (e) {
            // AuthService might not be initialized yet
            Get.log('Error clearing session: $e');
          }
          
          // Return error to prevent further processing
          return handler.reject(e);
        }
        
        return handler.next(e);
      },
    ));
  }

  Future<dio_package.Response> get(String path, {Map<String, dynamic>? queryParameters}) {
    return dio.get(path, queryParameters: queryParameters);
  }

  Future<dio_package.Response> post(String path, {dynamic data}) {
    return dio.post(path, data: data);
  }

  Future<dio_package.Response> put(String path, {dynamic data}) {
    return dio.put(path, data: data);
  }

  Future<dio_package.Response> delete(String path) {
    return dio.delete(path);
  }
}
