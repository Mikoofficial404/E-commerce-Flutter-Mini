import 'package:flutter/foundation.dart';
import '../config/api_config.dart';
import '../models/product_model.dart';
import 'api_service.dart';

class ProductService {
  final ApiService _api = ApiService();

  Future<List<Product>> getProducts() async {
    final response = await _api.get(ApiConfig.product);
    debugPrint('Response data type: ${response.data.runtimeType}');
    debugPrint('Response data: ${response.data}');

    dynamic data = response.data;
    if (data is Map && data.containsKey('data')) {
      data = data['data'];
    }

    if (data is! List) {
      debugPrint('Data is not a List: $data');
      return [];
    }

    return data.map<Product>((json) => Product.fromJson(json)).toList();
  }

  Future<Product> getProductById(int id) async {
    final response = await _api.get('${ApiConfig.product}/$id');
    dynamic data = response.data;
    if (data is Map && data.containsKey('data')) {
      data = data['data'];
    }
    return Product.fromJson(data);
  }
}
