<template>
  <v-card>
    <v-card-title>My Account</v-card-title>
    <v-card-text>
      <v-form @submit.prevent="updateAccount">
        <v-text-field
          v-model="email"
          label="Email"
          type="email"
          :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'Email must be valid']"
          required
        ></v-text-field>

        <v-text-field
          v-model="password"
          label="New Password (leave blank to not change)"
          type="password"
        ></v-text-field>

        <v-text-field
          v-model="passwordConfirmation"
          label="Confirm New Password"
          type="password"
          :rules="[v => (v === password) || 'Passwords do not match']"
        ></v-text-field>

        <v-btn type="submit" color="primary" :loading="loading">Update Account</v-btn>
      </v-form>

      <v-divider class="my-4"></v-divider>

      <v-card-subtitle>Access Token (Sanctum)</v-card-subtitle>
      <v-card-text>
        <v-text-field
          v-model="sanctumToken"
          label="Your Sanctum Token"
          readonly
          outlined
          append-icon="mdi-content-copy"
          @click:append="copyToken"
        ></v-text-field>
        <v-alert v-if="copySuccess" type="success" dense text="">Token copied successfully!</v-alert>
      </v-card-text>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import api from '@/services/api';

const authStore = useAuthStore();
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const sanctumToken = ref('');
const loading = ref(false);
const copySuccess = ref(false);

onMounted(async () => {
  // Load user data
  if (authStore.user) {
    email.value = authStore.user.email;
  }
  // Load Sanctum token (assuming it's stored in localStorage or similar)
  sanctumToken.value = localStorage.getItem('token') || '';
});

const updateAccount = async () => {
  loading.value = true;
  try {
    const payload: any = { email: email.value };
    if (password.value) {
      payload.password = password.value;
      payload.password_confirmation = passwordConfirmation.value;
    }
    await api.put('/user/profile-information', payload);
    // Optionally, refresh user data in store
    await authStore.fetchUser();
    alert('Account updated successfully!');
  } catch (error) {
    console.error('Error updating account:', error);
    alert('Error updating account.');
  } finally {
    loading.value = false;
  }
};

const copyToken = async () => {
  try {
    await navigator.clipboard.writeText(sanctumToken.value);
    copySuccess.value = true;
    setTimeout(() => (copySuccess.value = false), 2000);
  } catch (err) {
    console.error('Failed to copy token:', err);
    alert('Failed to copy token.');
  }
};
</script>

<style scoped>
/* Add any specific styles here, if necessary */
</style>