import api from './api';
import type { Report } from '@/types/Report';

export const getReports = (options: any) => {
  return api.get('/reports', { params: options });
};

export const createReport = (report: FormData) => {
  return api.post('/reports', report, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
};

export const updateReport = (id: number, report: FormData) => {
  return api.post(`/reports/${id}`, report, { // Use POST with _method=PUT for FormData
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    params: { _method: 'PUT' },
  });
};

export const deleteReport = (id: number) => {
  return api.delete(`/reports/${id}`);
};

export const executeReport = (data: any) => {
  return api.post('/reports/execute', data, {
    responseType: 'blob', // Important for handling file downloads
  });
};

export const uploadSubreport = (reportId: number, subreportFile: File) => {
  const formData = new FormData();
  formData.append('subreport_file', subreportFile);

  return api.post(`/reports/${reportId}/subreports`, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
};
