import { defineStore } from 'pinia';
import { ref } from 'vue';
import * as reportService from '@/services/reportService';
import type { Report } from '@/types/Report';

export const useReportStore = defineStore('reports', () => {
  const reports = ref<Report[]>([]);
  const totalItems = ref(0);
  const isLoading = ref(false);
  const reportUrl = ref<string | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchReports(options: any) {
    isLoading.value = true;
    try {
      const response = await reportService.getReports(options);
      reports.value = response.data.data;
      totalItems.value = response.data.total;
    } catch (error) {
      console.error('Failed to fetch reports:', error);
    } finally {
      isLoading.value = false;
    }
  }

  async function executeReport(data: any) {
    loading.value = true;
    error.value = null;
    reportUrl.value = null;
    try {
      const response = await reportService.executeReport(data);
      const blob = new Blob([response.data], { type: response.headers['content-type'] });
      reportUrl.value = URL.createObjectURL(blob);
    } catch (e: any) {
      console.error('Failed to execute report:', e);
      error.value = e.response?.data?.message || 'Failed to execute report';
    } finally {
      loading.value = false;
    }
  }

  return { reports, totalItems, isLoading, fetchReports, reportUrl, loading, error, executeReport };
});
