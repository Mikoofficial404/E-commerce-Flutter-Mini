import { useEffect, useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { getProduct } from '@/api/product';
import { getOrders, Order } from '@/api/transactions';
import { getUser } from '@/api/user';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';

// Helper untuk format waktu relatif
const getTimeAgo = (dateString: string): string => {
  const now = new Date();
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  if (diffInSeconds < 60) return `${diffInSeconds}s ago`;
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
  return `${Math.floor(diffInSeconds / 86400)}d ago`;
};

// Helper untuk status text
const getStatusText = (order: Order): string => {
  if (order.payment_status === 'paid') return 'Payment completed';
  if (order.payment_status === 'unpaid') return 'Waiting for payment';
  if (order.status === 'pending') return 'Order pending';
  if (order.status === 'processing') return 'Order processing';
  if (order.status === 'completed') return 'Order completed';
  return 'New order';
};

// Helper untuk nama bulan
const getMonthName = (month: number): string => {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return months[month];
};

// Interface untuk chart data
interface ChartData {
  name: string;
  sales: number;
}

export default function Dashboard() {
  const [totalProducts, setTotalProducts] = useState<number>(0);
  const [totalTransactions, setTotalTransactions] = useState<number>(0);
  const [totalUsers, setTotalUsers] = useState<number>(0);
  const [recentOrders, setRecentOrders] = useState<Order[]>([]);
  const [chartData, setChartData] = useState<ChartData[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const [products, orders, users] = await Promise.all([
          getProduct(),
          getOrders(),
          getUser(),
        ]);
        setTotalProducts(products.length);
        setTotalTransactions(orders.length);
        setTotalUsers(users.length);

        // Ambil 5 order terbaru
        const sortedOrders = orders.sort(
          (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        );
        setRecentOrders(sortedOrders.slice(0, 5));

        // Generate chart data - sales per bulan (6 bulan terakhir)
        const salesByMonth: Record<string, number> = {};
        const now = new Date();
        
        // Initialize 6 bulan terakhir dengan 0
        for (let i = 5; i >= 0; i--) {
          const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
          const key = `${date.getFullYear()}-${date.getMonth()}`;
          salesByMonth[key] = 0;
        }

        // Hitung total sales per bulan (hanya yang paid)
        orders.forEach((order) => {
          if (order.payment_status === 'paid') {
            const orderDate = new Date(order.created_at);
            const key = `${orderDate.getFullYear()}-${orderDate.getMonth()}`;
            if (salesByMonth[key] !== undefined) {
              salesByMonth[key] += Number(order.total_price) || 0;
            }
          }
        });

        // Convert ke array untuk chart
        const chartDataArray: ChartData[] = [];
        for (let i = 5; i >= 0; i--) {
          const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
          const key = `${date.getFullYear()}-${date.getMonth()}`;
          chartDataArray.push({
            name: getMonthName(date.getMonth()),
            sales: salesByMonth[key] || 0,
          });
        }
        setChartData(chartDataArray);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold tracking-tight">Dashboard</h1>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardHeader>
            <CardTitle>Total Products</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <Skeleton className="h-9 w-16" />
            ) : (
              <p className="text-3xl font-bold">{totalProducts}</p>
            )}
            <p className="text-sm text-muted-foreground">Produk tersedia</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Total Transactions</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <Skeleton className="h-9 w-16" />
            ) : (
              <p className="text-3xl font-bold">{totalTransactions}</p>
            )}
            <p className="text-sm text-muted-foreground">Total order</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Total Users</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <Skeleton className="h-9 w-16" />
            ) : (
              <p className="text-3xl font-bold">{totalUsers}</p>
            )}
            <p className="text-sm text-muted-foreground">Pengguna terdaftar</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Sales Overview</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="h-64 flex items-center justify-center">
                <Skeleton className="h-full w-full" />
              </div>
            ) : (
              <div className="h-64">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={chartData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" />
                    <YAxis 
                      tickFormatter={(value) => 
                        value >= 1000000 
                          ? `${(value / 1000000).toFixed(1)}M` 
                          : value >= 1000 
                            ? `${(value / 1000).toFixed(0)}K` 
                            : value
                      }
                    />
                    <Tooltip 
                      formatter={(value) => [
                        `Rp ${Number(value).toLocaleString('id-ID')}`,
                        'Sales'
                      ]}
                    />
                    <Bar dataKey="sales" fill="#3b82f6" radius={[4, 4, 0, 0]} />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            )}
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {loading ? (
                <>
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="flex items-center gap-3">
                      <Skeleton className="h-10 w-10 rounded-full" />
                      <div className="space-y-2 w-full">
                        <Skeleton className="h-3 w-2/3" />
                        <Skeleton className="h-3 w-1/3" />
                      </div>
                    </div>
                  ))}
                </>
              ) : recentOrders.length === 0 ? (
                <p className="text-sm text-muted-foreground text-center py-4">
                  Belum ada transaksi
                </p>
              ) : (
                recentOrders.map((order, index) => (
                  <div key={order.id}>
                    <div className="flex items-center justify-between">
                      <p className="text-sm font-medium">{order.order_code}</p>
                      <span className="text-xs text-muted-foreground">
                        {getTimeAgo(order.created_at)}
                      </span>
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {getStatusText(order)} - Rp {Number(order.total_price).toLocaleString('id-ID')}
                    </p>
                    {index < recentOrders.length - 1 && <Separator className="mt-3" />}
                  </div>
                ))
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
