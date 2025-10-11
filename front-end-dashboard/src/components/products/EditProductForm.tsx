import { showProduct, updateProduct } from '@/api/product';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Sheet,
  SheetTrigger,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetFooter,
  SheetDescription,
  SheetClose,
} from '@/components/ui/sheet';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import type { ReactNode } from 'react';

type ProductFormData = {
  product_name: string;
  description: string;
  price: number;
  stock: number;
  photo_product: File | null;
  _method: string;
};

type EditProductProps = {
  productId: string | number;
  children?: ReactNode;
};

export default function EditProduct({ productId, children }: EditProductProps) {
  const id = String(productId);
  const navigate = useNavigate();
  const [imagePreview, setImagePreview] = useState<string | null>(null);

  const [formData, setFormData] = useState<ProductFormData>({
    product_name: '',
    description: '',
    price: 0,
    stock: 0,
    photo_product: null,
    _method: 'PUT',
  });

  useEffect(() => {
    const fetchData = async () => {
      if (!id) return;
      try {
        const productsData = await showProduct(id);
        setFormData({
          product_name: productsData.name ?? '',
          description: productsData.description ?? '',
          price: productsData.price ?? 0,
          stock: productsData.stock ?? 0,
          photo_product: null,
          _method: 'PUT',
        });

        if (productsData.photo_product) {
          setImagePreview(productsData.photo_product);
        }
      } catch (err) {
        console.error('Gagal memuat data produk:', err);
      }
    };
    fetchData();
  }, [id]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: e.target.type === 'number' ? Number(value) : (value as string),
    }));
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setFormData((prev) => ({
      ...prev,
      photo_product: file,
    }));
    setImagePreview(file ? URL.createObjectURL(file) : null);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!id) return;
    try {
      const payload = new FormData();
      Object.entries(formData).forEach(([key, value]) => {
        if (value !== null) {
          payload.append(key, value as Blob | string);
        }
      });

      await updateProduct(id, payload);
      alert('Produk berhasil diupdate!');
      navigate('/products');
    } catch (error) {
      console.error(error);
      alert('Terjadi kesalahan saat mengedit produk.');
    }
  };

  return (
    <Sheet>
      <SheetTrigger asChild>
        {children ?? <Button>Edit Product</Button>}
      </SheetTrigger>
      <SheetContent side="right">
        <SheetHeader>
          <SheetTitle>Edit Product</SheetTitle>
          <SheetDescription>
            Ubah detail produk yang sudah ada.
          </SheetDescription>
        </SheetHeader>

        <form onSubmit={handleSubmit} className="p-4 space-y-4">
          <div className="space-y-2">
            <Label htmlFor="product_name">Product Name</Label>
            <Input
              id="product_name"
              name="product_name"
              value={formData.product_name}
              onChange={handleChange}
              placeholder="e.g. Hoodie"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="description">Description</Label>
            <Input
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              placeholder="Deskripsi produk"
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="price">Price</Label>
              <Input
                id="price"
                name="price"
                type="number"
                value={formData.price}
                onChange={handleChange}
                placeholder="0"
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="stock">Stock</Label>
              <Input
                id="stock"
                name="stock"
                type="number"
                value={formData.stock}
                onChange={handleChange}
                placeholder="0"
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="photo_product">Image</Label>
            <Input
              id="photo_product"
              name="photo_product"
              type="file"
              accept="image/*"
              onChange={handleImageChange}
            />
            {imagePreview && (
              <img
                src={imagePreview}
                alt="Preview"
                className="mt-2 h-24 w-24 object-cover rounded border"
              />
            )}
          </div>

          <SheetFooter>
            <div className="flex gap-2">
              <SheetClose asChild>
                <Button type="button" variant="secondary">
                  Cancel
                </Button>
              </SheetClose>
              <Button type="submit">Save Changes</Button>
            </div>
          </SheetFooter>
        </form>
      </SheetContent>
    </Sheet>
  );
}
