<template>
  <v-card>
    <v-card-title>Execute Report</v-card-title>
    <v-card-text>
      <v-form @submit.prevent="executeReport">
        <v-select
          v-model="selectedReportId"
          :items="reportStore.reports"
          item-title="name"
          item-value="id"
          label="Select Report"
          :rules="[v => !!v || 'Report is required']"
          required
        ></v-select>

        <v-select
          v-model="selectedDataSourceId"
          :items="dataSourceStore.dataSources"
          item-title="name"
          item-value="id"
          label="Select Connection (Data Source)"
          :rules="[v => !!v || 'Connection is required']"
          required
        ></v-select>

        <v-text-field
          v-model="reportFormat"
          label="Report Format (pdf, xls, xlsx, docx, txt, html)"
          :rules="[v => !!v || 'Format is required']"
          required
        ></v-text-field>

        <v-textarea
          v-model="jsonData"
          label="JSON / SQL Data (optional)"
          rows="5"
          hint="Enter JSON or SQL data for the report."
        ></v-textarea>

        <v-textarea
          v-model="jsonParams"
          label="JSON Parameters (optional)"
          rows="3"
          hint="Enter a JSON array of parameters for the report."
        ></v-textarea>

        <v-checkbox
          v-model="debugMode"
          label="Debug Mode"
        ></v-checkbox>

        <v-btn type="submit" color="primary" :loading="reportStore.loading">Execute</v-btn>
      </v-form>

      <div v-if="reportStore.loading" class="mt-4">Loading...</div>
      <div v-if="reportStore.error" class="mt-4 text-red">Error: {{ reportStore.error }}</div>
      <div v-if="reportStore.reportUrl" class="mt-4">
        <v-btn :href="reportStore.reportUrl" target="_blank" color="success">Download Report</v-btn>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useDataSourceStore } from '@/stores/dataSourceStore';
import { useReportStore } from '@/stores/reportStore';

const dataSourceStore = useDataSourceStore();
const reportStore = useReportStore();

const selectedReportId = ref<number | null>(null);
const selectedDataSourceId = ref<number | null>(null);
const reportFormat = ref('pdf'); // Default format
const jsonData = ref('');
const jsonParams = ref('');
const debugMode = ref(false);

const showJsonDataField = computed(() => {
  if (!selectedDataSourceId.value) return false;
  const selectedDs = dataSourceStore.dataSources.find(ds => ds.id === selectedDataSourceId.value);
  return selectedDs && (selectedDs.type === 'json' || selectedDs.type === 'array');
});

const executeReport = async () => {
  if (!selectedReportId.value || !selectedDataSourceId.value) {
    alert('Please select the report and connection.');
    return;
  }

  const payload: any = {
    report_id: selectedReportId.value,
    data_source_id: selectedDataSourceId.value,
    format: reportFormat.value,
    debug: debugMode.value,
  };

  if (jsonData.value) {
    try {
      payload.json_data = JSON.parse(jsonData.value);
    } catch (e) {
      alert('Invalid JSON data.');
      return;
    }
  } else {
    payload.json_data = {}; // Envia um objeto vazio se o campo estiver vazio
  }

  if (jsonParams.value) {
    try {
      payload.json_params = JSON.parse(jsonParams.value);
    } catch (e) {
      alert('Invalid JSON for parameters.');
      return;
    }
  }

  await reportStore.executeReport(payload);
};

onMounted(() => {
  dataSourceStore.fetchDataSources({});
  reportStore.fetchReports({});
});

watch(selectedDataSourceId, (newVal) => {
  if (newVal) {
    const selectedDs = dataSourceStore.dataSources.find(ds => ds.id === newVal);
    if (selectedDs && (selectedDs.type === 'json' || selectedDs.type === 'array')) {
      jsonData.value = ''; // Clear data if not JSON/Array type
    }
  }
});
</script>

<style scoped>
/* Add any specific styles here, if necessary */
</style>