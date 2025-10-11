import { API } from '../api/axios';

interface LogoutParams {
  token: string;
}

interface LoginParams {
  email: string;
  password: string;
}

interface LoginResponse {
  token: string;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

interface LogoutResponse {
  message?: string;
  success?: boolean;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export const login = async ({
  email,
  password,
}: LoginParams): Promise<LoginResponse> => {
  try {
    const { data } = await API.post<LoginResponse>('/login', {
      email,
      password,
    });
    if (!data) throw new Error('No data returned from server');
    return data;
  } catch (e) {
    console.error(e);
    throw e;
  }
};

export const logout = async ({
  token,
}: LogoutParams): Promise<LogoutResponse> => {
  try {
    const { data } = await API.post<LogoutResponse>(
      '/logout',
      { token },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('access_token')}`,
        },
      }
    );

    localStorage.removeItem('access_token');
    localStorage.removeItem('user');

    return data;
  } catch (error) {
    console.error('Logout error:', error);
    throw error;
  }
};
