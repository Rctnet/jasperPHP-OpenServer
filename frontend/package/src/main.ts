import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { router } from './router';
import vuetify from './plugins/vuetify';
import '@/scss/style.scss';
import PerfectScrollbar from 'vue3-perfect-scrollbar';
import VueApexCharts from 'vue3-apexcharts';
import VueTablerIcons from 'vue-tabler-icons';
import { useAuthStore } from '@/stores/authStore';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

const authStore = useAuthStore();

authStore.tryAutoLogin().then(() => {
    app.use(router);
    app.use(PerfectScrollbar);
    app.use(VueTablerIcons);
    app.use(VueApexCharts);
    app.use(vuetify).mount('#app');
});
