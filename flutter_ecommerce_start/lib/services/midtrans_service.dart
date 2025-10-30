import 'package:midtrans_sdk/midtrans_sdk.dart';
import '../config/midtrans_config.dart';

class MidtransService {
  late MidtransSDK midtrans;

  Future<void> init() async {
    midtrans = await MidtransSDK.init(
      config: MidtransConfig(
        clientKey: AppMidtransConfig.clientKey,
        merchantBaseUrl: AppMidtransConfig.merchantBaseUrl,
        enableLog: true,
      ),
    );
  }

  Future<void> startPayment(String snapToken) async {
    await midtrans.startPaymentUiFlow(token: snapToken);
  }

  void dispose() {}
}
