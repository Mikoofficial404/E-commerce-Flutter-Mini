import { NavLink } from 'react-router-dom';
import { cn } from '@/lib/utils';

export default function Sidebar() {
  const menu = [
    { name: 'Dashboard', path: '/dashboard' },
    { name: 'Products', path: '/products' },
    { name: 'Transactions', path: '/transactions' },
    { name: 'Users', path: '/users' },
  ];

  return (
    <aside className="w-64 bg-gray-900 text-gray-100 flex flex-col p-4">
      <h2 className="text-xl font-bold mb-6">Admin Panel</h2>
      <nav className="flex flex-col gap-2">
        {menu.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              cn(
                'rounded-md px-3 py-2 hover:bg-gray-700 transition-colors',
                isActive && 'bg-gray-800'
              )
            }
          >
            {item.name}
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
