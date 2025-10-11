import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import TransactionTable from '@/components/transactions/TransactionTable';

export default function TransactionsPage() {
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold tracking-tight">Transactions</h1>
      <Card>
        <CardHeader>
          <CardTitle>Recent Transactions</CardTitle>
        </CardHeader>
        <CardContent>
          <TransactionTable />
        </CardContent>
      </Card>
    </div>
  );
}


