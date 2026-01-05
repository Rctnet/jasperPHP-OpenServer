<template>
    <div class="logo d-flex align-center">
        <RouterLink to="/" class="d-flex align-center text-decoration-none">
            <img src="@/assets/images/logos/jasperLogo.png" alt="Jasper Logo" height="26" class="mr-2" />
            <span class="text-h6 font-weight-bold">QuilhaSoft/JasperPHP</span>
        </RouterLink>
    </div>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
const primary = ref('rgb(var(--v-theme-primary))');
const secondary = ref('rgb(var(--v-theme-secondary))');
</script>
