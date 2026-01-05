import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { User } from '@/types/User';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('token'));
  const isLoading = ref(false);

  const isAuthenticated = computed(() => !!user.value && !!token.value);

  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
  }

  async function fetchUser() {
    try {
      const response = await api.get('/user');
      user.value = response.data;
    } catch (error) {
      user.value = null;
      token.value = null;
      localStorage.removeItem('token');
      delete api.defaults.headers.common['Authorization'];
    }
  }

  async function handleLogin(credentials: { email: string; password: string }) {
    isLoading.value = true;
    try {
      const response = await api.post('/login', credentials);
      const responseToken = response.data.token;
      if (responseToken) {
        token.value = responseToken;
        localStorage.setItem('token', responseToken);
        api.defaults.headers.common['Authorization'] = `Bearer ${responseToken}`;
        await fetchUser();
      } else {
        throw new Error('Token not provided in login response');
      }
    } finally {
      isLoading.value = false;
    }
  }

  async function handleRegister(form: any) {
    isLoading.value = true;
    try {
      const response = await api.post('/register', form);
      const responseToken = response.data.token;
      if (responseToken) {
        token.value = responseToken;
        localStorage.setItem('token', responseToken);
        api.defaults.headers.common['Authorization'] = `Bearer ${responseToken}`;
        await fetchUser();
      } else {
        throw new Error('Token not provided in registration response');
      }
    } finally {
      isLoading.value = false;
    }
  }

  async function logout() {
    isLoading.value = true;
    try {
      await api.post('/logout');
    } finally {
      user.value = null;
      token.value = null;
      localStorage.removeItem('token');
      delete api.defaults.headers.common['Authorization'];
      isLoading.value = false;
    }
  }

  async function tryAutoLogin() {
    if (token.value) {
      await fetchUser();
    }
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    fetchUser,
    handleLogin,
    handleRegister,
    logout,
    tryAutoLogin,
  };
});
