import '../config/api_config.dart';
import '../models/product_model.dart';
import 'api_service.dart';

class ProductService {
  final ApiService _api = ApiService();

  Future<List<Product>> getProducts() async {
    final response = await _api.get(ApiConfig.product);
    final List data = response.data;
    return data.map((json) => Product.fromJson(json)).toList();
  }

  Future<Product> getProductById(int id) async {
    final response = await _api.get('${ApiConfig.product}/ $id');
    return Product.fromJson(response.data);
  }
}
