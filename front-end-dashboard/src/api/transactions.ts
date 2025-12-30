import { API } from '../api/axios';

// Interface untuk Product dalam OrderItem
export interface OrderProduct {
  id: number;
  product_name: string;
  price: number;
  stock: number;
  photo_product?: string;
}

// Interface untuk OrderItem
export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product_name: string;
  price: number;
  quantity: number;
  product?: OrderProduct;
}

// Interface untuk User
export interface OrderUser {
  id: number;
  username: string;
  email: string;
}

// Interface untuk Order
export interface Order {
  id: number;
  order_code: string;
  user_id: number;
  total_price: number;
  payment_status: 'unpaid' | 'paid' | 'expired' | 'cancelled';
  status: 'pending' | 'processing' | 'completed' | 'cancelled';
  created_at: string;
  updated_at: string;
  items?: OrderItem[];
  user?: OrderUser;
}

// Interface untuk Create Order Request
export interface CreateOrderItem {
  product_id: number;
  quantity: number;
}

export interface CreateOrderPayload {
  items: CreateOrderItem[];
}

// Interface untuk Create Order Response
export interface CreateOrderResponse {
  order: Order;
  items: OrderItem[];
  snap_token: string;
}

// Get all orders
export const getOrders = async (): Promise<Order[]> => {
  const { data } = await API.get<{ data: Order[]; message: string; success: boolean }>('/orders');
  return data.data;
};

// Get single order by ID
export const getOrderById = async (id: number | string): Promise<Order> => {
  const { data } = await API.get<{ data: Order; message: string; success: boolean }>(`/orders/${id}`);
  return data.data;
};

// Create new order
export const createOrder = async (payload: CreateOrderPayload): Promise<CreateOrderResponse> => {
  const { data } = await API.post<{ data: CreateOrderResponse; message: string; success: boolean }>('/orders', payload);
  return data.data;
};

// Delete order by ID
export const deleteOrder = async (id: number | string): Promise<void> => {
  await API.delete(`/orders/${id}`);
};
