<template>
  <v-card>
    <v-card-title>
      <v-row>
        <v-col cols="12" md="4">
          Reports
        </v-col>
        <v-col cols="12" md="8" class="text-md-right">
          <v-btn color="primary" @click="openForm()">Add New Report</v-btn>
        </v-col>
      </v-row>
    </v-card-title>
    <v-card-text>
      <v-row>
        <v-col cols="12" md="4">
          <v-text-field v-model="filters.name" label="Filter by Name" @update:model-value="debouncedLoadReports"></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-text-field v-model="filters.description" label="Filter by Description" @update:model-value="debouncedLoadReports"></v-text-field>
        </v-col>
        <v-col cols="12" md="4">
          <v-text-field v-model="filters.directory_path" label="Filter by Directory" @update:model-value="debouncedLoadReports"></v-text-field>
        </v-col>
      </v-row>
    </v-card-text>

    <v-data-table-server
      :headers="headers"
      :items="reportStore.reports"
      :items-length="reportStore.totalItems"
      :loading="reportStore.isLoading"
      @update:options="loadReports"
    >
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="openForm(item)">mdi-pencil</v-icon>
        <v-icon small class="mr-2" @click="openSubreportForm(item)">mdi-upload-multiple</v-icon>
        <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
      </template>
    </v-data-table-server>

    <!-- Form Dialog -->
    <v-dialog v-model="dialog" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">{{ formTitle }}</span>
        </v-card-title>
        <v-card-text>
          <v-form ref="form">
            <template v-if="!isSubreportMode">
              <v-text-field v-model="editedItem.name" label="Name" :rules="[v => !!v || 'Name is required']"></v-text-field>
              <v-textarea v-model="editedItem.description" label="Description"></v-textarea>
              <v-select
                v-model="editedItem.data_source_id"
                :items="dataSources"
                item-title="name"
                item-value="id"
                label="Connection (Data Source)"
                :rules="[v => !!v || 'Connection is required']"
              ></v-select>
            </template>
            <v-file-input 
              v-model="reportFile" 
              :label="isSubreportMode ? 'Subreport File (.jrxml)' : 'Report File (.jrxml)'" 
              :rules="[v => !!v || (isEditing && !isSubreportMode) || 'File is required']"
            ></v-file-input>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeForm">Cancel</v-btn>
          <v-btn color="blue darken-1" text @click="save">Save</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

     <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title class="headline">Are you sure you want to delete this item?</v-card-title>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="closeDelete">Cancel</v-btn>
          <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-card>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { useReportStore } from '@/stores/reportStore';
import * as reportService from '@/services/reportService';
import type { Report } from '@/types/Report';
import type { DataSource } from '@/types/DataSource';
import debounce from 'lodash.debounce';
import { fetchDataSources } from '@/services/dataSourceService';

const reportStore = useReportStore();

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Description', key: 'description' },
  { title: 'Directory', key: 'directory_path' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const filters = reactive({
  name: '',
  description: '',
  directory_path: '',
});

const dialog = ref(false);
const deleteDialog = ref(false);
const isEditing = ref(false);
const isSubreportMode = ref(false);
const editedItem = ref<Partial<Report>>({});
const itemToDelete = ref<Report | null>(null);
const reportFile = ref<File | null>(null);
const dataSources = ref<DataSource[]>([]);

  onMounted(async () => {
    try {
      const response = await fetchDataSources({});
      dataSources.value = response.data;
    } catch (error) {
      console.error('Failed to fetch data sources:', error);
    }
  });

const formTitle = computed(() => {
  if (isSubreportMode.value) return 'Upload Subreport';
  return isEditing.value ? 'Edit Report' : 'New Report';
});

const loadReports = async (options: any) => {
  const searchParams = { ...filters };
  // Remove empty filters
  (Object.keys(searchParams) as Array<keyof typeof searchParams>).forEach(key => {
    if (!searchParams[key]) delete searchParams[key];
  });

  await reportStore.fetchReports({ ...options, ...searchParams });
};

const debouncedLoadReports = debounce(loadReports, 500);

const openForm = (item: Report | null = null) => {
  isSubreportMode.value = false;
  if (item) {
    isEditing.value = true;
    editedItem.value = { ...item };
  } else {
    isEditing.value = false;
    editedItem.value = {};
  }
  reportFile.value = null;
  dialog.value = true;
};

const openSubreportForm = (item: Report) => {
  isSubreportMode.value = true;
  isEditing.value = false; // Not editing the main report
  editedItem.value = { ...item }; // We just need the ID
  reportFile.value = null;
  dialog.value = true;
};

const closeForm = () => {
  dialog.value = false;
  isSubreportMode.value = false;
};

const form = ref<HTMLFormElement | null>(null);

const save = async () => {
  if (form.value) {
    const { valid } = await form.value.validate();
    if (!valid) {
      return;
    }
  }

  try {
    if (isSubreportMode.value) {
      if (reportFile.value && editedItem.value.id) {
        await reportService.uploadSubreport(editedItem.value.id, reportFile.value);
      }
    } else {
      const formData = new FormData();
      formData.append('name', editedItem.value.name || '');
      formData.append('description', editedItem.value.description || '');
      formData.append('data_source_id', String(editedItem.value.data_source_id || ''));
      if (reportFile.value) {
        formData.append('report_file', reportFile.value);
      }

      if (isEditing.value) {
        await reportService.updateReport(editedItem.value.id!, formData);
      } else {
        await reportService.createReport(formData);
      }
    }
    closeForm();
    loadReports({}); // Refresh the table
  } catch (error) {
    console.error('Failed to save report:', error);
  }
};

const deleteItem = (item: Report) => {
  itemToDelete.value = item;
  deleteDialog.value = true;
};

const closeDelete = () => {
  deleteDialog.value = false;
  itemToDelete.value = null;
};

const deleteItemConfirm = async () => {
  if (itemToDelete.value) {
    try {
      await reportService.deleteReport(itemToDelete.value.id);
      loadReports({}); // Refresh the table
    } catch (error) {
      console.error('Failed to delete report:', error);
    }
    closeDelete();
  }
};

</script>