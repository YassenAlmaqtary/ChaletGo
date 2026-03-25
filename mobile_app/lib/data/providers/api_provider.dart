import 'package:dio/dio.dart';

import '../../core/services/dio_client.dart';

class ApiProvider {
  final DioClient dioClient;

  ApiProvider(this.dioClient);

  Future<Map<String, dynamic>> get(String path,
      {Map<String, dynamic>? params}) async {
    try {
      final res = await dioClient.get(path, queryParameters: params);
      return _castResponse(res);
    } on DioException catch (e) {
      // If response has error data, return it as a map instead of throwing
      if (e.response != null && e.response!.data is Map<String, dynamic>) {
        final errorData = e.response!.data as Map<String, dynamic>;
        return {
          'success': false,
          'message': errorData['message']?.toString() ?? _getErrorMessage(e),
          'errors': errorData['errors'],
        };
      }
      return {
        'success': false,
        'message': _getErrorMessage(e),
      };
    } catch (e) {
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }

  Future<Map<String, dynamic>> post(
      String path, Map<String, dynamic> body) async {
    try {
      final res = await dioClient.post(path, data: body);
      return _castResponse(res);
    } on DioException catch (e) {
      // If response has error data, return it as a map instead of throwing
      if (e.response != null && e.response!.data is Map<String, dynamic>) {
        final errorData = e.response!.data as Map<String, dynamic>;
        // Return error response in the same format as success response
        return {
          'success': false,
          'message': errorData['message']?.toString() ?? _getErrorMessage(e),
          'errors': errorData['errors'],
        };
      }
      // For other errors, return error map
      return {
        'success': false,
        'message': _getErrorMessage(e),
      };
    } catch (e) {
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }

  String _getErrorMessage(DioException e) {
    final data = e.response?.data;
    if (data is Map<String, dynamic>) {
      return data['message']?.toString() ?? e.message ?? 'خطأ في الاتصال';
    }
    return e.message ?? 'خطأ في الاتصال';
  }

  Future<Map<String, dynamic>> put(
      String path, Map<String, dynamic> body) async {
    try {
      final res = await dioClient.put(path, data: body);
      return _castResponse(res);
    } on DioException catch (e) {
      // If response has error data, return it as a map instead of throwing
      if (e.response != null && e.response!.data is Map<String, dynamic>) {
        final errorData = e.response!.data as Map<String, dynamic>;
        return {
          'success': false,
          'message': errorData['message']?.toString() ?? _getErrorMessage(e),
          'errors': errorData['errors'],
        };
      }
      return {
        'success': false,
        'message': _getErrorMessage(e),
      };
    } catch (e) {
      return {
        'success': false,
        'message': e.toString(),
      };
    }
  }

  Map<String, dynamic> _castResponse(Response response) {
    if (response.data is Map<String, dynamic>) {
      return response.data as Map<String, dynamic>;
    }
    return {'success': false, 'message': 'استجابة غير متوقعة'};
  }

  Exception _wrapError(DioException e) {
    String? message;
    final data = e.response?.data;
    if (data is Map<String, dynamic>) {
      message = data['message']?.toString();
    }
    message ??= e.message;
    message ??= 'خطأ في الاتصال';
    return Exception(message);
  }
}
