<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const email = ref('');
const password = ref('');
const error = ref<string | null>(null);
const isLoading = ref(false);
const checkbox = ref(true);

const router = useRouter();
const authStore = useAuthStore();

const onLogin = async () => {
  error.value = null;
  isLoading.value = true;
  try {
    await authStore.handleLogin({
      email: email.value,
      password: password.value,
    });
    // Redirect to home or intended page after successful login
    router.push('/');
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
    <form @submit.prevent="onLogin">
        <v-row class="d-flex mb-3">
            <v-col cols="12">
                <v-label class="font-weight-bold mb-1">Email</v-label>
                <v-text-field v-model="email" type="email" variant="outlined" hide-details color="primary"></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-label class="font-weight-bold mb-1">Password</v-label>
                <v-text-field v-model="password" variant="outlined" type="password"  hide-details color="primary"></v-text-field>
            </v-col>
            <v-col cols="12" class="pt-0">
                <div class="d-flex flex-wrap align-center ml-n2">
                    <v-checkbox v-model="checkbox"  color="primary" hide-details>
                        <template v-slot:label class="text-body-1">Remeber this Device</template>
                    </v-checkbox>
                    <div class="ml-sm-auto">
                        <RouterLink to="/"
                            class="text-primary text-decoration-none text-body-1 opacity-1 font-weight-medium">Forgot
                            Password ?</RouterLink>
                    </div>
                </div>
            </v-col>
            <v-col cols="12" class="pt-0">
                <v-btn type="submit" :loading="isLoading" color="primary" size="large" block flat>Sign in</v-btn>
            </v-col>
        </v-row>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
</template>
