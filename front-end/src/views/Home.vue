<script setup>
  import {ref, onMounted, computed, watchEffect } from 'vue';
  import axios from "axios";
  import bubbleChart from "../components/charts/BubbleChart.vue";
  import PaginationBar from "@/components/PaginationBar.vue";

  const tableData = ref(null);
  const totalPages = ref(null);
  const currentPage = ref(1);
  const itemsPerPage = ref(10);
  const pagesToShow = ref(9);
  const searchIsLoading = ref(false);

  const orgName = ref(null);
  const sortBy = ref('yCount');
  const sortOrder = ref('DESC');
  const selectedCriteria = ref('n_frdocs');

  const criteriaOptions = [
    { title: 'Number of Linked Docs', value: 'n_frdocs' }, 
    { title: 'Number of Changes', value: 'y_count' }
  ];

  const tableHeaders = [
      { title: 'Organization Name', key: 'org_name' },
      { title: 'Number of Changes', key: 'y_count' },
      { title: 'Number of Linked Docs', key: 'n_frdocs' },
  ];

  const sortByOptions = ref([
    { title: 'Number of Changes', value: 'yCount' },
    { title: 'Organization Name', value: 'orgName' },
    { title: 'Number of Linked Docs', value: 'frdocs' },
    { title: 'None', value: null }
  ]);

  const fetchData = async () => {
    searchIsLoading.value = true;

    try {
      const response = await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/home", { 
        params: { 
          filters: {
            orgName: orgName.value, // can be a string of the Organization name
            sortBy: sortBy.value, // sort by a specific column: "orgName" || "yCount" || "frdocs" || NULL
            sortOrder: sortOrder.value, // can be "DESC" || "ASC" || NULL
            page: currentPage.value, // has to be an integer || NULL
            itemsPerPage: itemsPerPage.value // has to be an integer || NULL
          }
        }
       });

      tableData.value = response.data.data;
      totalPages.value = response.data.totalPages;

    } catch (error) {
      console.error('Error fetching data:', error);
    }

    searchIsLoading.value = false;
  };

  const updateCurrentPage = (newPage) => {
      currentPage.value = newPage;
      fetchData();
  }

  const updateItemsPerPage = (newItemsPerPage) => {
      itemsPerPage.value = newItemsPerPage;
      fetchData();
  }
  
  onMounted(fetchData);
</script>

<template>
  <h1 class="welcome-banner mt-4">Welcome to the Rulemaking Influence Explorer!</h1>
  <v-container class="d-flex justify-center mt-5">
    <v-row class="my-b mx-1">
      <v-select id="sort-drop-down" bg-color="rie-primary-color" v-model="sortBy" :items="sortByOptions" item-title="title" item-value="value" label="Sort Options" class="mr-3"/>
      <v-text-field id="organization-search" label="Organization Search" v-model="orgName" bg-color="rie-primary-color" class="mr-3"></v-text-field>
      <v-switch id="sort-toggle" v-model="sortOrder" true-value="ASC" false-value="DESC" :label="'Sort Order: ' + sortOrder" color="rie-primary-color"/>
      <v-btn id="search-button" color="rie-primary-color" @click="fetchData()">Search</v-btn>
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>
  </v-container>
  
  <v-container v-container class="d-flex justify-center mt-5"> 
    <v-container class="table-container mr-2" style="width: 70%;">
      <v-toolbar flat>
          <v-toolbar-title>Top Influential Organizations</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
      </v-toolbar>
      <v-data-table id="organization-table" v-if="tableData"
        v-model:items-per-page="itemsPerPage"
        :items="tableData" 
        :headers="tableHeaders"
        :page.sync="currentPage"
        class="elevation-1">
        <template v-slot:bottom>
        </template>
        <template v-slot:item.org_name="{ item }">
          <router-link :to="{ name: 'organization', params: { orgName: item.org_name } }" tag="td" class="clickable-cell">
            {{ item.org_name }}
          </router-link>
        </template>
      </v-data-table>
      <PaginationBar class="pagination"
          :current-page.sync="currentPage"
          :total-pages="totalPages"
          :pages-to-show="pagesToShow"
          :items-per-page="itemsPerPage"
          :is-loading="searchIsLoading"
          @update:current-page="updateCurrentPage"
          @update:items-per-page="updateItemsPerPage"
      />
    </v-container>

    <v-divider vertical></v-divider>
 
    <v-container class="rightside" v-if="tableData" style="width: 30%;">
      <v-row> <v-select v-model="selectedCriteria" :items="criteriaOptions" label="Select Criteria" item-title="title" item-value="value" outlined></v-select> </v-row>
      <v-row> <bubbleChart :data="tableData" :selectedCriteria = "selectedCriteria"/> </v-row>
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

  .pagination {
    margin-top: 20px;
  }

  .rightside {
    margin-top: 12px;
  }

  .clickable-cell {
    color: black;
    text-decoration: none;
  }
</style>