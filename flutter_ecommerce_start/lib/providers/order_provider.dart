import 'package:flutter/material.dart';
import '../models/order_model.dart';
import '../services/order_service.dart';

class OrderProvider with ChangeNotifier {
  final OrderService _orderService = OrderService();

  OrderResponse? _orderResponse;
  bool _isLoading = false;

  OrderResponse? get orderResponse => _orderResponse;
  bool get isLoading => _isLoading;

  Future<void> createOrder(Map<String, dynamic> orderData) async {
    _isLoading = true;
    notifyListeners();

    try {
      _orderResponse = await _orderService.createOrder(orderData);
    } catch (e) {
      print('Error buat order: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  String? get snapToken => _orderResponse?.data.snapToken;
}
