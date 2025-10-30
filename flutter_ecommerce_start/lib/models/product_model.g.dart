// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Product _$ProductFromJson(Map<String, dynamic> json) => Product(
  id: (json['id'] as num?)?.toInt(),
  productName: json['productName'] as String?,
  description: json['description'] as String?,
  price: json['price'] as String?,
  stock: (json['stock'] as num?)?.toInt(),
  photoProduct: json['photoProduct'] as String?,
  deletedAt: json['deletedAt'],
  createdAt: json['createdAt'] == null
      ? null
      : DateTime.parse(json['createdAt'] as String),
  updatedAt: json['updatedAt'] == null
      ? null
      : DateTime.parse(json['updatedAt'] as String),
);

Map<String, dynamic> _$ProductToJson(Product instance) => <String, dynamic>{
  'id': instance.id,
  'productName': instance.productName,
  'description': instance.description,
  'price': instance.price,
  'stock': instance.stock,
  'photoProduct': instance.photoProduct,
  'deletedAt': instance.deletedAt,
  'createdAt': instance.createdAt?.toIso8601String(),
  'updatedAt': instance.updatedAt?.toIso8601String(),
};
