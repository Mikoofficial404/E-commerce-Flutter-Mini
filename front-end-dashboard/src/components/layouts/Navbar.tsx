import { logout } from '@/api/auth';
import { Button } from '@/components/ui/button';
import { useNavigate } from 'react-router-dom';

export default function Navbar() {
  const token = localStorage.getItem('access_token');
  const userInfo = localStorage.getItem('user')
    ? JSON.parse(localStorage.getItem('user') as string)
    : null;
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      if (!token) {
        console.warn('No token found, redirecting to login...');
        navigate('/');
        return;
      }

      await logout({ token });

      localStorage.removeItem('access_token');
      localStorage.removeItem('user');

      navigate('/');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  return (
    <header className="h-16 bg-white shadow flex items-center justify-between px-6">
      <h1 className="text-lg font-semibold">Dashboard</h1>
      <Button variant="destructive" onClick={handleLogout}>
        Logout
      </Button>
    </header>
  );
}
