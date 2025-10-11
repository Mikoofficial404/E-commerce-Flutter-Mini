import { API } from '../api/axios';

export interface Orders {
  order_code: string;
  total_price: number;
  payment_status: string;
  status: string;
}
export const getOrders = async (): Promise<Orders[]> => {
  const { data } = await API.get<{ data: Orders[] }>('/orders');
  return data.data;
};
