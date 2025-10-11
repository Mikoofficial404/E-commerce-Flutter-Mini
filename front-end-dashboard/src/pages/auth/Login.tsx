import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from '@/components/ui/card';
import { useDecodeToken } from '@/api/useDecodeToken';
import { useNavigate } from 'react-router-dom';
import { login } from '@/api/auth';

export default function LoginPage() {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });

  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  const token = localStorage.getItem('access_token');
  const decodeData = useDecodeToken(token);
  const navigate = useNavigate();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await login(formData);

      if (response && response.success) {
        localStorage.setItem('access_token', response.token || '');
        localStorage.setItem('user', JSON.stringify(response.user));

        if (response.user?.role === 'admin') {
          navigate('/dashboard');
        } else {
          navigate('/login');
        }
      } else {
        setError(response.message || 'Login gagal. Silakan coba lagi.');
      }
    } catch (err) {
      console.error(err);
      setError('Terjadi kesalahan pada server.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (token && decodeData && decodeData.success && decodeData.data) {
      console.log('Token is valid, redirecting to dashboard');
      navigate('/dashboard', { replace: true });
    }
  }, [token, decodeData, navigate]);

  const { email, password } = formData;

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <CardTitle className="text-2xl font-bold tracking-tight">
            Admin Login
          </CardTitle>
          <CardDescription>
            Masukkan email dan password Anda untuk masuk.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                name="email"
                type="email"
                placeholder="contoh@email.com"
                required
                value={email}
                onChange={handleChange}
                disabled={loading}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                name="password"
                type="password"
                placeholder="••••••••"
                required
                value={password}
                onChange={handleChange}
                disabled={loading}
              />
            </div>

            {error && (
              <p className="text-red-500 text-sm text-center">{error}</p>
            )}

            <Button type="submit" className="w-full" disabled={loading}>
              {loading ? 'Logging in...' : 'Login'}
            </Button>
          </form>
        </CardContent>

        <CardFooter className="text-center text-sm text-gray-500">
          <p>Lupa password? Hubungi administrator.</p>
        </CardFooter>
      </Card>
    </div>
  );
}
