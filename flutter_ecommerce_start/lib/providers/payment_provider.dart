import 'package:flutter/material.dart';
import '../services/midtrans_service.dart';

class PaymentProvider with ChangeNotifier {
  final MidtransService _midtransService = MidtransService();
  bool _isInitialized = false;

  bool get isInitialized => _isInitialized;

  Future<void> initPayment() async {
    if (_isInitialized) return;
    await _midtransService.init();
    _isInitialized = true;
  }

  Future<void> startPayment(String snapToken) async {
    await _midtransService.startPayment(snapToken);
  }
}
