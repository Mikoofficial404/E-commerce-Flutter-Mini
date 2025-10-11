import { useEffect, useState } from 'react';
import { deleteProduct, getProduct } from '@/api/product';
import { Button } from '@/components/ui/button';
import EditProductForm from '@/components/products/EditProductForm';

const BASE_URL = 'http://localhost:8000';
interface Product {
  id: number;
  product_name: string;
  description: string;
  price: number;
  stock: number;
  photo_product: string;
}

export default function ProductTable() {
  const [products, setProduct] = useState<Product[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setError(null);
        const productData = await getProduct();
        setProduct(Array.isArray(productData) ? productData : []);
      } catch (error) {
        console.error(error);
        setError('Gagal Memuat Data Product');
        setProduct([]);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  const handleDelete = async (id: number) => {
    try {
      const confirmDelete = window.confirm(
        'Are you sure you want to delete this product?'
      );
      if (confirmDelete) {
        await deleteProduct(id);
        setProduct((prev) => prev.filter((product) => product.id !== id));
      }
    } catch (error) {
      console.error('Gagal menghapus produk:', error);
      alert('Terjadi kesalahan saat menghapus produk.');
    }
  };
  if (loading) {
    return (
      <section className="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div className="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
          <div className="flex items-center justify-center p-8">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span className="ml-2 text-gray-600 dark:text-gray-400">
              Memuat data...
            </span>
          </div>
        </div>
      </section>
    );
  }

  if (error) {
    return (
      <section className="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div className="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
          <div className="flex items-center justify-center p-8">
            <div className="text-center">
              <svg
                className="mx-auto h-12 w-12 text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
              <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                Error
              </h3>
              <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {error}
              </p>
              <button
                onClick={() => window.location.reload()}
                className="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
              >
                Coba Lagi
              </button>
            </div>
          </div>
        </div>
      </section>
    );
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="text-left text-muted-foreground">
            <th className="py-2">Photo</th>
            <th className="py-2">Nama Product</th>
            <th className="py-2">Deskripsi</th>
            <th className="py-2">Price</th>
            <th className="py-2">Stock</th>
            <th className="py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          {products.map((product) => (
            <tr key={product.id} className="border-t">
              <td className="py-3">
                {product.photo_product ? (
                  <img
                    src={
                      product.photo_product.startsWith('http')
                        ? product.photo_product
                        : `${BASE_URL}/${product.photo_product}`
                    }
                    alt={product.product_name}
                    className="h-12 w-12 rounded object-cover border"
                  />
                ) : (
                  <div className="h-12 w-12 flex items-center justify-center bg-gray-100 text-gray-400 rounded">
                    No Image
                  </div>
                )}
              </td>

              <td className="py-3 font-medium">{product.product_name}</td>

              <td className="py-3 max-w-[250px] truncate text-gray-600 dark:text-gray-400">
                {product.description || '-'}
              </td>

              <td className="py-3">
                Rp {product.price.toLocaleString('id-ID')}
              </td>

              <td className="py-3">{product.stock}</td>

              <td className="py-3 text-right">
                <div className="inline-flex gap-2">
                  <EditProductForm productId={product.id}>
                    <Button size="sm" variant="secondary">
                      Edit
                    </Button>
                  </EditProductForm>
                  <Button
                    onClick={() => handleDelete(product.id)}
                    size="sm"
                    variant="destructive"
                  >
                    Delete
                  </Button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
