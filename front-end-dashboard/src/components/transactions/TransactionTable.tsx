import { useEffect, useState } from 'react';
import { getOrders, Order } from '@/api/transactions';

export default function TransactionTable() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchOrders = async () => {
      try {
        setLoading(true);
        const data = await getOrders();
        setOrders(data);
      } catch (err) {
        setError('Gagal memuat data orders');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, []);

  const getStatusBadge = (status: string) => {
    const styles: Record<string, string> = {
      paid: 'bg-green-100 text-green-800',
      unpaid: 'bg-yellow-100 text-yellow-800',
      expired: 'bg-gray-100 text-gray-800',
      cancelled: 'bg-red-100 text-red-800',
    };
    return styles[status] || 'bg-gray-100 text-gray-800';
  };

  if (loading) {
    return <div className="py-4 text-center text-muted-foreground">Loading...</div>;
  }

  if (error) {
    return <div className="py-4 text-center text-red-500">{error}</div>;
  }

  if (orders.length === 0) {
    return <div className="py-4 text-center text-muted-foreground">Tidak ada transaksi</div>;
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="text-left text-muted-foreground">
            <th className="py-2">Order Code</th>
            <th className="py-2">User</th>
            <th className="py-2">Total</th>
            <th className="py-2">Payment Status</th>
            <th className="py-2">Status</th>
            <th className="py-2">Date</th>
          </tr>
        </thead>
        <tbody>
          {orders.map((order) => (
            <tr key={order.id} className="border-t">
              <td className="py-3 font-medium">{order.order_code}</td>
              <td className="py-3">{order.user?.username || '-'}</td>
              <td className="py-3">Rp {order.total_price.toLocaleString('id-ID')}</td>
              <td className="py-3">
                <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusBadge(order.payment_status)}`}>
                  {order.payment_status}
                </span>
              </td>
              <td className="py-3 capitalize">{order.status}</td>
              <td className="py-3">{new Date(order.created_at).toLocaleDateString('id-ID')}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
