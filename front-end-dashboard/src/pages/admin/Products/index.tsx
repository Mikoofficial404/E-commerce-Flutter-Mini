import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import ProductTable from '@/components/products/ProductTable';
import ProductForm from '@/components/products/ProductForm';

export default function ProductsPage() {
  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold tracking-tight">Products</h1>
        <ProductForm />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Products List</CardTitle>
        </CardHeader>
        <CardContent>
          <ProductTable />
        </CardContent>
      </Card>
    </div>
  );
}
