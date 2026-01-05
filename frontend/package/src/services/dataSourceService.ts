import api from '@/services/api';
import type { DataSource } from '@/types/DataSource';

export const fetchDataSources = async (options: any): Promise<{ data: DataSource[], total: number }> => {
  try {
    const response = await api.get('/datasources', { params: options });
    return response.data;
  } catch (error) {
    console.error('Error fetching data sources:', error);
    throw error;
  }
};

export const createDataSource = async (dataSource: Partial<DataSource>): Promise<DataSource> => {
  try {
    const response = await api.post('/datasources', dataSource);
    return response.data;
  } catch (error) {
    console.error('Error creating data source:', error);
    throw error;
  }
};

export const updateDataSource = async (id: number, dataSource: Partial<DataSource>): Promise<DataSource> => {
  try {
    const response = await api.put(`/datasources/${id}`, dataSource);
    return response.data;
  } catch (error) {
    console.error('Error updating data source:', error);
    throw error;
  }
};

export const deleteDataSource = async (id: number): Promise<void> => {
  try {
    await api.delete(`/datasources/${id}`);
  } catch (error) {
    console.error('Error deleting data source:', error);
    throw error;
  }
};