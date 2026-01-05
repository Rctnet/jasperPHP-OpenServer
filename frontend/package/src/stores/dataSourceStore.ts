import { defineStore } from 'pinia';
import { ref } from 'vue';
import * as dataSourceService from '@/services/dataSourceService';
import type { DataSource } from '@/types/DataSource';

export const useDataSourceStore = defineStore('dataSources', () => {
  const dataSources = ref<DataSource[]>([]);
  const totalItems = ref(0);
  const isLoading = ref(false);

  async function fetchDataSources(options: any) {
    isLoading.value = true;
    try {
      const response = await dataSourceService.fetchDataSources(options);
      dataSources.value = response.data;
      totalItems.value = response.total;
    } catch (error) {
      console.error('Failed to fetch data sources:', error);
    } finally {
      isLoading.value = false;
    }
  }

  return { dataSources, totalItems, isLoading, fetchDataSources };
});