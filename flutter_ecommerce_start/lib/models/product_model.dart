import 'package:json_annotation/json_annotation.dart';

part 'product_model.g.dart';

@JsonSerializable()
class Product {
  final int? id;
  final String? productName;
  final String? description;
  final String? price;
  final int? stock;
  final String? photoProduct;
  final dynamic deletedAt;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  Product({
    this.id,
    this.productName,
    this.description,
    this.price,
    this.stock,
    this.photoProduct,
    this.deletedAt,
    this.createdAt,
    this.updatedAt,
  });

  factory Product.fromJson(Map<String, dynamic> json) =>
      _$ProductFromJson(json);
  Map<String, dynamic> toJson() => _$ProductToJson(this);
}
