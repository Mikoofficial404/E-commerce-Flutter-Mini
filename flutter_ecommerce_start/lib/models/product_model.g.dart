// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Product _$ProductFromJson(Map<String, dynamic> json) => Product(
  id: (json['id'] as num?)?.toInt(),
  productName: json['product_name'] as String?,
  description: json['description'] as String?,
  price: json['price'] as String?,
  stock: (json['stock'] as num?)?.toInt(),
  photoProduct: json['photo_product'] as String?,
  deletedAt: json['deleted_at'],
  createdAt: json['created_at'] == null
      ? null
      : DateTime.parse(json['created_at'] as String),
  updatedAt: json['updated_at'] == null
      ? null
      : DateTime.parse(json['updated_at'] as String),
);

Map<String, dynamic> _$ProductToJson(Product instance) => <String, dynamic>{
  'id': instance.id,
  'product_name': instance.productName,
  'description': instance.description,
  'price': instance.price,
  'stock': instance.stock,
  'photo_product': instance.photoProduct,
  'deleted_at': instance.deletedAt,
  'created_at': instance.createdAt?.toIso8601String(),
  'updated_at': instance.updatedAt?.toIso8601String(),
};
