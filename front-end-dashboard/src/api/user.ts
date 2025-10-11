import { API } from '../api/axios';

export interface Users {
  username: string;
  id: number;
  email: string;
  password: string;
  role: string;
}

export const getUser = async (): Promise<Users[]> => {
  const { data } = await API.get<{ data: Users[] }>('/users');
  return data.data;
};
