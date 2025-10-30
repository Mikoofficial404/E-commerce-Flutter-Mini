import '../config/api_config.dart';
import '../models/order_model.dart';
import 'api_service.dart';

class OrderService {
  final ApiService _api = ApiService();

  Future<OrderResponse> createOrder(Map<String, dynamic> OrderData) async {
    final response = await _api.post(ApiConfig.orders, OrderData);
    return OrderResponse.fromJson(response.data);
  }

  Future<Order> getOrderById(int id) async {
    final response = await _api.get('${ApiConfig.orders}/ $id');
    return Order.fromJson(response.data);
  }
}
