import 'package:flutter/foundation.dart';
import '../models/product_model.dart';
import '../services/product_service.dart';

class ProductProvider with ChangeNotifier {
  final ProductService _productService = ProductService();
  List<Product> _products = [];
  bool _isLoading = false;
  String? _error;

  List<Product> get products => _products;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> fetchProducts() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      debugPrint('Fetching products...');
      _products = await _productService.getProducts();
      debugPrint('Products fetched: ${_products.length}');
      for (var p in _products) {
        debugPrint('Product: ${p.productName} - ${p.price}');
      }
    } catch (e, stackTrace) {
      _error = e.toString();
      debugPrint('Error fetching products: $e');
      debugPrint('Stack trace: $stackTrace');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
