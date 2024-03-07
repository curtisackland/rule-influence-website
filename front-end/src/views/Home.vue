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
  const sortBy = ref('org_changes');
  const sortOrder = ref('DESC');
  const selectedCriteria = ref('org_changes');

  const criteriaOptions = [
    { title: 'Number of Changes', value: 'org_changes' },
    { title: 'Number of Responses', value: 'org_responses' },
    { title: 'Number of Rules', value: 'org_rules' },  
  ];

  const tableHeaders = [
      { title: 'Organization Name', key: 'org_name' },
      { title: 'Changes', key: 'org_changes' },
      { title: 'Responses', key: 'org_responses' },
      { title: 'Rules', key: 'org_rules' },
  ];

  const sortByOptions = ref([
    { title: 'Organization Name', value: 'org_name' },
    { title: 'Changes', value: 'org_changes' },
    { title: 'Responses', value: 'org_responses' },
    { title: 'Rules', value: 'org_rules' },
    { title: 'None', value: null }
  ]);

  const fetchData = async () => {
    searchIsLoading.value = true;

    try {
      const response = await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/home", { 
        params: { 
          filters: {
            orgName: orgName.value, // can be a string of the Organization name
            sortBy: sortBy.value, // sort by a specific column: "org_name" || "org_changes" || "org_responses" || "org_rules" ||NULL
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

  const calculateBarHeight = (value, criteria) => {
    const numericValue = parseFloat(value);
    if (isNaN(numericValue) || numericValue === 0) {
    } else {
        const maxValue = Math.max(...tableData.value.map(item => parseFloat(item[criteria])));
        return `${(numericValue / maxValue) * 100}%`;
    }
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
  
  <v-container class="d-flex justify-center mt-5"> 
    <v-container class="mr-2" style="width: 100%;">
      <v-toolbar flat>
          <v-toolbar-title>Top Influential Organizations</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
      </v-toolbar>
      <v-data-table
        id="organization-table"
        v-if="tableData"
        v-model:items-per-page="itemsPerPage"
        :items="tableData" 
        :headers="tableHeaders"
        :page.sync="currentPage"
        class="elevation-1"
        hide-default-footer
      >
        <template v-slot:item="{ item }">
          <tr>
            <td class="org-name-cell">
              <router-link :to="{ name: 'organization', params: { orgName: item.org_name } }" tag="span" class="clickable-cell">
                {{ item.org_name }}
              </router-link>
            </td>
            <td>
              <div class="cell-container">
                <div class="number">{{ item.org_changes }}</div>
                <div class="bar-graph changes" :style="{ height: calculateBarHeight(item.org_changes, 'org_changes') }"></div>
              </div>
            </td>
            <td>
              <div class="cell-container">
              <div class="number">{{ item.org_responses }}</div>
              <div class="bar-graph responses" :style="{ height: calculateBarHeight(item.org_responses, 'org_responses') }"></div>
            </div>
            </td>
            <td>
              <div class="cell-container">
              <div class="number">{{ item.org_rules }}</div>
              <div class="bar-graph rules" :style="{ height: calculateBarHeight(item.org_rules, 'org_rules') }"></div>
            </div>
            </td>
          </tr>
        </template>
        <template v-slot:bottom>
          <v-container>
              <PaginationBar 
                class="pagination"
                :current-page.sync="currentPage"
                :total-pages="totalPages"
                :pages-to-show="pagesToShow"
                :items-per-page="itemsPerPage"
                :is-loading="searchIsLoading"
                @update:current-page="updateCurrentPage"
                @update:items-per-page="updateItemsPerPage"
              />
            </v-container>
        </template>
      </v-data-table>
    </v-container>
  </v-container>
</template>

<style scoped>
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
  .clickable-cell {
    color: black;
    text-decoration: none;
  }

  .cell-container {
    position: relative;
    display: flex;
    align-items: flex-end;
    height: 100%;
    margin-top: 10px;
    margin-bottom: 0;
  }
  .number {
    margin-bottom: 0;
    width: 35px;
  }
  .bar-graph {
    width: 35px;
    background-repeat: repeat-y;
    margin-left: 5px;
    margin-bottom: 5px;
  }
  .changes {
    background-color:  #575858;
  }
  .responses {
    background-color:  #475349;
  }
  .rules {
    background-color: #2d382f;
  }
  .org-name-cell {
    margin-right: 10px;
    max-width: 200px;
  }

</style>