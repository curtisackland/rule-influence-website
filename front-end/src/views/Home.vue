<script setup>
  import {ref, onMounted, computed } from 'vue';
  import axios from "axios";
  import router from '../router/index.js';
  import bubbleChart from "../components/charts/BubbleChart.vue";

  const tableData = ref(null);
  const searchIsLoading = ref(false);
  const orgName = ref(null);
  const sortBy = ref('yCount');
  const sortOrder = ref('DESC');
  const selectedCriteria = ref('n_frdocs');
  const criteriaOptions = ['n_frdocs', 'y_count'];

  const tableHeaders = [
      { title: 'Organization Name', key: 'org_name' },
      { title: 'Y Count', key: 'y_count' },
      { title: 'FR Docs', key: 'n_frdocs' },
  ];

  const sortByOptions = ref([
    { title: 'y count', value: 'yCount' },
    { title: 'organization name', value: 'orgName' },
    { title: 'fr docs', value: 'frdocs' },
    { title: 'None', value: null }
  ]);

  const startSearch = async () => {
    searchIsLoading.value = true;

    try {
      const response = await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/home", { 
        params: { 
          filters: {
            orgName: orgName.value, // can be a string of the Organization name
            sortBy: sortBy.value, // sort by a specific column: "orgName" || "yCount" || "frdocs" || NULL
            sortOrder: sortOrder.value // can be "DESC" || "ASC" || NULL
          }
        }
       });

      tableData.value = response.data;
    } catch (error) {
      console.error('Error fetching data:', error);
    }

    searchIsLoading.value = false;
  };

  onMounted(startSearch);

  const handleCellClick = (value) => { 
      if (router) {
        console.error('router exisys');
        const encodedOrganizationName = encodeURIComponent(value);  
        const apiUrl = `organization/${encodedOrganizationName}`;
        
        router.push(apiUrl);
      }
  }
</script>

<template>
  <h1 class="welcome-banner">Welcome to Rule-Explorer!</h1>
  <v-container class="d-flex justify-center mt-5">
    <v-row class="my-b mx-1">
      <v-select bg-color="rie-primary-color" v-model="sortBy" :items="sortByOptions" item-title="title" item-value="value" label="Sort Options" class="mr-3"/>
      <v-text-field label="Organization Search" v-model="orgName" bg-color="rie-primary-color" class="mr-3"></v-text-field>
      <v-switch v-model="sortOrder" true-value="ASC" false-value="DESC" :label="'Sort Order: ' + sortOrder" color="rie-primary-color"/>
      <v-btn color="rie-primary-color" @click="startSearch()">Search</v-btn>
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>
  </v-container>
  
  <v-container v-container class="d-flex justify-center mt-5"> 
    <v-container class="table-container mr-2" style="width: 70%;">
      <v-data-table v-if="tableData" 
        :items="tableData" 
        :headers="tableHeaders"
        :items-per-page="-1"
        class="elevation-1">
        <template v-slot:top>
          <v-toolbar flat>
            <v-toolbar-title>Top Influential Organizations</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
          </v-toolbar>
        </template>
        <template v-slot:item.org_name="{ item }">
          <td class="clickable-cell" @click="handleCellClick(item.org_name)">{{ item.org_name }}</td>
        </template>
      </v-data-table>
    </v-container>

    <v-divider vertical></v-divider>
 
    <v-container v-if="tableData" style="width: 30%;">
      <v-row>
        <v-col>
        <v-select v-model="selectedCriteria" :items="criteriaOptions" label="Select Criteria" outlined></v-select>
        </v-col>
      </v-row>

      <v-row>
          <v-col>
            <bubbleChart :data="tableData" :selectedCriteria = "selectedCriteria" />
          </v-col>
      </v-row>
    </v-container>
</v-container>
</template>

<style scoped>
  .table-container {
    margin-left: -12px;
    width: 70%;
  }
  .clickable-cell {
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
  }
  .clickable-cell:hover {
    color: #4e8585;
  }
  .welcome-banner {
    color: "rie-primary-color";
    text-align: center;
  }
</style>