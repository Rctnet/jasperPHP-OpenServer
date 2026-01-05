<template>
  <v-card>
    <v-card-title>
      <v-row>
        <v-col cols="12" md="4">
          Data Sources
        </v-col>
        <v-col cols="12" md="8" class="text-md-right">
          <v-btn color="primary" @click="openForm()">Add New Data Source</v-btn>
        </v-col>
      </v-row>
    </v-card-title>
    <v-card-text>
      <v-row>
        <v-col cols="12" md="6">
          <v-text-field v-model="filters.name" label="Filter by Name" @update:model-value="debouncedLoadDataSources"></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
          <v-select
            v-model="filters.type"
            :items="dataSourceTypes"
            label="Filter by Type"
            @update:model-value="debouncedLoadDataSources"
            clearable
          ></v-select>
        </v-col>
      </v-row>
    </v-card-text>

    <v-data-table-server
      :headers="headers"
      :items="dataSourceStore.dataSources"
      :items-length="dataSourceStore.totalItems"
      :loading="dataSourceStore.isLoading"
      @update:options="loadDataSources"
    >
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="openForm(item)">mdi-pencil</v-icon>
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
          <v-form ref="form" @submit.prevent="save">
            <v-text-field v-model="editedItem.name" label="Name" :rules="[v => !!v || 'Name is required']"></v-text-field>
            <v-select
              v-model="editedItem.type"
              :items="dataSourceTypes"
              label="Type"
              :rules="[v => !!v || 'Type is required']"
            ></v-select>
            <div class="d-flex align-center mb-2">
              <span class="v-label">Configuration (JSON)</span>
              <v-spacer></v-spacer>
              <v-btn small variant="text" color="primary" @click="loadSampleJson">Load Sample</v-btn>
            </div>
            <v-textarea
              v-model="configString"
              :rules="[v => !!v || 'Configuration is required', v => isJsonString(v) || 'Invalid JSON']"
              rows="10"
            ></v-textarea>
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
import { ref, reactive, computed, watch } from 'vue';
import { useDataSourceStore } from '@/stores/dataSourceStore';
import * as dataSourceService from '@/services/dataSourceService';
import type { DataSource } from '@/types/DataSource';
import debounce from 'lodash.debounce';

const dataSourceStore = useDataSourceStore();

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Type', key: 'type' },
  { title: 'Actions', key: 'actions', sortable: false },
];

const dataSourceTypes = ['mysql', 'pgsql', 'sqlite', 'json', 'array'];

const filters = reactive({
  name: '',
  type: '',
});

const dialog = ref(false);
const deleteDialog = ref(false);
const isEditing = ref(false);
const editedItem = ref<Partial<DataSource>>({});
const itemToDelete = ref<DataSource | null>(null);
const configString = ref('');

const formTitle = computed(() => (isEditing.value ? 'Edit Data Source' : 'New Data Source'));

const sampleJson = {
  "driver": "mysql",
  "host": "127.0.0.1",
  "port": 3306,
  "database": "db_name",
  "username": "db_user",
  "password": "db_password"
};

const loadSampleJson = () => {
  configString.value = JSON.stringify(sampleJson, null, 2);
};


const isJsonString = (str: string) => {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
};

watch(dialog, (val) => {
  if (val) { // When dialog opens
    configString.value = editedItem.value.configuration ? JSON.stringify(editedItem.value.configuration, null, 2) : '';
  }
});

const loadDataSources = async (options: any) => {
  const searchParams = { ...filters };
  (Object.keys(searchParams) as Array<keyof typeof searchParams>).forEach(key => {
    if (!searchParams[key]) delete searchParams[key];
  });

  await dataSourceStore.fetchDataSources({ ...options, ...searchParams });
};

const debouncedLoadDataSources = debounce(loadDataSources, 500);

const openForm = (item: DataSource | null = null) => {
  if (item) {
    isEditing.value = true;
    editedItem.value = { ...item };
  } else {
    isEditing.value = false;
    editedItem.value = {};
  }
  dialog.value = true;
};

const closeForm = () => {
  dialog.value = false;
};

const save = async () => {
  if (!isJsonString(configString.value)) {
    // Maybe show an error to the user
    return;
  }

  const dataSourceData: Partial<DataSource> = {
    ...editedItem.value,
    configuration: JSON.parse(configString.value),
  };

  try {
    if (isEditing.value) {
      await dataSourceService.updateDataSource(dataSourceData.id!, dataSourceData);
    } else {
      await dataSourceService.createDataSource(dataSourceData);
    }
    closeForm();
    loadDataSources({}); // Refresh the table
  } catch (error) {
    console.error('Failed to save data source:', error);
  }
};

const deleteItem = (item: DataSource) => {
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
      await dataSourceService.deleteDataSource(itemToDelete.value.id!);
      loadDataSources({}); // Refresh the table
    } catch (error) {
      console.error('Failed to delete data source:', error);
    }
    closeDelete();
  }
};

</script>

<style scoped>
/* Add any specific styles here */
</style>
