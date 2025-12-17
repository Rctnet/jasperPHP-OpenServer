export interface Report {
  id: number;
  name: string;
  slug?: string;
  description: string;
  directory_path: string;
  main_jrxml_name: string;
  created_at: string;
  updated_at: string;
  data_source_id?: number;
}
