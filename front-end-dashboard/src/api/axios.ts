import axios from 'axios';

const url = 'http://127.0.0.1:8000';

export const API = axios.create({
  baseURL: `${url}/api`,
  withCredentials: true,
});

API.interceptors.request.use(
  (config) => {
    const localstorage = localStorage.getItem('access_token');
    if (localstorage) {
      config.headers.Authorization = `Bearer ${localstorage}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export const PostsImageStorage = `${url}/storage`;
