import axios from 'axios';

const api = axios.create({
  baseURL: '/api', // Adjust if your backend URL is different
  
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
});

export default api;
