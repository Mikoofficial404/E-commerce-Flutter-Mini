export default function TransactionTable() {
  const rows = [
    { id: 'INV-001', user: 'Rina', amount: 320000, status: 'Paid', date: '2025-10-01' },
    { id: 'INV-002', user: 'Budi', amount: 150000, status: 'Pending', date: '2025-10-02' },
    { id: 'INV-003', user: 'Andi', amount: 540000, status: 'Failed', date: '2025-10-03' },
  ];

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="text-left text-muted-foreground">
            <th className="py-2">Invoice</th>
            <th className="py-2">User</th>
            <th className="py-2">Amount</th>
            <th className="py-2">Status</th>
            <th className="py-2">Date</th>
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.id} className="border-top">
              <td className="py-3 font-medium">{row.id}</td>
              <td className="py-3">{row.user}</td>
              <td className="py-3">Rp {row.amount.toLocaleString('id-ID')}</td>
              <td className="py-3">{row.status}</td>
              <td className="py-3">{row.date}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

