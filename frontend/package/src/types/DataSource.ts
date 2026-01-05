export interface DataSource {
  id?: number;
  name: string;
  type: string;
  connection_string?: string;
  created_at?: string;
  updated_at?: string;
  configuration?: object;
}