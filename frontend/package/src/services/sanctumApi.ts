import axios from 'axios';

// All defaults are already set in api.ts, but we can create
// a separate instance if we need specific headers for Sanctum calls.
// In this case, it's largely the same, but we'll keep it for consistency.

const sanctumApi = axios.create({
  // The baseURL and other defaults are inherited from the global setup in api.ts
});

/**
 * Requests a CSRF cookie from the server. This is the first step in establishing
 * a secure session with the Laravel backend.
 */
export const getCsrfCookie = async () => {
  try {
    await sanctumApi.get('/sanctum/csrf-cookie');
  } catch (error) {
    console.error('Failed to obtain CSRF cookie:', error);
    // Optionally, handle the error more gracefully (e.g., show a message to the user)
  }
};

export default sanctumApi;
