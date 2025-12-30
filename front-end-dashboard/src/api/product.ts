import { API } from '../api/axios';

export interface Product {
  id: number;
  name?: string;
  product_name?: string;
  price: number;
  description?: string;
  stock: number;
  photo_product: string;
}

export const getProduct = async (): Promise<Product[]> => {
  const { data } = await API.get<{ data: Product[] }>('/products');
  return data.data;
};

export const createProduct = async (payload: FormData): Promise<Product> => {
  try {
    const { data } = await API.post<{ data: Product }>('/products', payload);
    return data.data;
  } catch (error) {
    console.error('Create product error:', error);
    throw error;
  }
};

export const showProduct = async (id: string | number): Promise<Product> => {
  try {
    const { data } = await API.get<{ data: Product }>(`/products/${id}`);
    return data.data;
  } catch (error) {
    console.error('Show product error:', error);
    throw error;
  }
};

export const updateProduct = async (
  id: string | number,
  data: FormData
): Promise<Product> => {
  try {
    const response = await API.post(`/products/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  } catch (error) {
    console.log(error);
    throw error;
  }
};

export const deleteProduct = async (id: number): Promise<void> => {
  try {
    await API.delete(`/products/${id}`);
  } catch (error) {
    console.error('Delete product error:', error);
    throw error;
  }
};
