<template>
  <div class="container justify-content-center mt-4">
    <v-row justify="center" align="center" class="my-1">
      <h1>FR Documents</h1>
    </v-row>
    <v-row class="my-b mx-1">
      <v-select v-model="filterFRType" label="fr type" :items="filterOptionsFRType" class="mr-3"></v-select>
      <v-select v-model="filterType" label="type" :items="filterOptionsType" class="mr-3"></v-select>
      <v-combobox label="topic" class="mr-3"></v-combobox>
      <v-text-field :model-value="filterStartDateText?.toISOString().split('T')[0]" label="Start Date" append-inner-icon="mdi-calendar" readonly="true" class="mr-3">
        <v-menu activator="parent" v-model="filterStartDateMenuActive" :close-on-content-click="false" >
          <v-date-picker v-model="filterStartDateText" color="rie-primary-color" format="yyyy-MM-dd" type="date" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterStartDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field :model-value="filterEndDateText?.toISOString().split('T')[0]" label="End Date" append-inner-icon="mdi-calendar" readonly="true">
        <v-menu activator="parent" v-model="filterEndDateMenuActive" :close-on-content-click="false">
          <v-date-picker v-model="filterEndDateText" color="rie-primary-color" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterEndDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field
          label="FR Doc Number"
          v-model="frdocNumber"
          class="ml-3"
      ></v-text-field>
    </v-row>
    <v-row class="mx-1">
      <v-select v-model="sortBy" :items="sortByOptions" item-title="title" item-value="value" label="Sort Options" class="mr-3"/>
      <v-switch v-model="sortOrder" true-value="ASC" false-value="DESC" :label="'Sort Order: ' + sortOrder" color="rie-primary-color"/>
      <v-btn color="rie-primary-color" @click="startSearch()">Search</v-btn>
    </v-row>
    <v-row class="mb-4 mx-1">
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>

    <v-card class="my-3" v-for="row in tableData">
      <v-card-text>
        <v-row class="d-flex">
          <v-col cols="3">
            <v-card-title v-if="row['frdoc_number']">{{row["frdoc_number"]}}</v-card-title>
            <v-card-title v-else>No frdoc number</v-card-title>
            <v-card-subtitle v-if="row['fr_type']">{{row["fr_type"]}}</v-card-subtitle>
            <v-card-subtitle v-else>No fr type</v-card-subtitle>
            <v-virtual-scroll class="mx-3 mb-2" :items="row['agencies']">
              <template v-slot:default="{ item }">
                {{ item }}
              </template>
            </v-virtual-scroll>
            <a :href="'https://www.federalregister.gov/d/' + row['frdoc_number']" target="_blank" class="px-3 w-100">
              <v-btn color="rie-primary-color" stacked="" text="FR Document on Federal Register" density="compact" class="w-75"></v-btn>
            </a>
          </v-col>
          <v-col cols="9">
            <v-card-title v-if="row['title']">{{ row["title"] }}</v-card-title>
            <v-card-title v-else>No Title</v-card-title>
            <v-card-subtitle v-if="row['publication_date'] && row['action']">{{ row["publication_date"] }} &bull; {{ row["action"] }}</v-card-subtitle>
            <v-card-subtitle v-else>No publication date or action</v-card-subtitle>
            <v-card-text v-if="row['abstract']">{{ row["abstract"] }}</v-card-text>
            <v-card-text v-else>No abstract</v-card-text>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            Previous Document {{row["prevFRDoc"]}}
          </v-col>
          <v-col>
            Next Document {{row["nextFRDoc"]}}
          </v-col>
          <v-col>
            Response Count: {{row["response_count"]}}
          </v-col>
          <v-col>
            Comment Count: {{row["comment_count"]}}
          </v-col>
          <v-col>
            Change Count: {{row["change_count"]}}
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <PaginationBar
        :current-page.sync="currentPage"
        :total-pages="totalPages"
        :pages-to-show="pagesToShow"
        :items-per-page="itemsPerPage"
        :is-loading="searchIsLoading"
        @update:current-page="updateCurrentPage"
        @update:items-per-page="updateItemsPerPage"
    />
  </div>
</template>



<script>
import axios from "axios"
import PaginationBar from "@/components/PaginationBar.vue";
export default {
  name: "FRDocs",
  components: {
    PaginationBar
  },
  methods: {
    async startSearch() {
      // Cancel the previous request if it exists
      if (this.axiosCancelSource) {
        this.axiosCancelSource.cancel('Request canceled by the user');
      }

      // Create a new CancelToken source for the current request
      this.axiosCancelSource = axios.CancelToken.source();

      this.errorMessage = null;
      this.searchIsLoading = true;
      this.scrollToTop();
      const queryParams = {
        filters: {
          start_date: this.filterStartDateText?.toISOString().split('T')[0],
          end_date: this.filterEndDateText?.toISOString().split('T')[0],
          fr_type: this.filterFRType,
          type: this.filterType,
          sortBy: this.sortBy,
          sortOrder: this.sortOrder,
          frdocNumber: this.frdocNumber,
          page: this.currentPage, // has to be an integer || NULL
          itemsPerPage: this.itemsPerPage, // has to be an integer || NULL
        }
      }
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/frdocs", {params:queryParams, cancelToken: this.axiosCancelSource.token})
          .then(response => {
            this.tableData = response.data.data;
            this.totalPages = response.data.totalPages;
          }).catch(error => {
            if (!axios.isCancel(error)) {
              if (error.response.data.error) {
                this.errorMessage = error.response.data.error;
              } else {
                this.errorMessage = "Unable to load page."
              }
            }
          });
      this.searchIsLoading = false;
    },
    async searchData() {
      this.currentPage = 1;
      await this.startSearch();
    },
    updateCurrentPage(newPage) {
      this.currentPage = newPage;
      this.startSearch();
    },
    updateItemsPerPage(newItemsPerPage) {
      this.itemsPerPage = newItemsPerPage;
      this.searchData();
    },
    handleEnterKey(event) {
      // Check if the pressed key is Enter (key code 13)
      if (event.key === 'Enter') {
        this.searchData();
      }
    },
    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    },
  },
  data() {
    return {
      tableData: null,
      searchIsLoading: false,
      axiosCancelSource: null,
      errorMessage: null,
      
      // Search filters
      filterOptionsFRType: [null, "Correction", "Notice", "Presidential Document", "Proposed Rule", "Rule", "Sunshine Act Document", "Uncategorized Document",],
      filterFRType: null,
      filterOptionsType: [null, "Advance Notice of Proposed Rule" ,"Affirmation of Rule" ,"Comment Extension" ,"Correction" ,"Direct Rule" ,"Filing Extension" ,"Interim Rule" ,"Notice" ,"Presidential Document" ,"Proposed Rule" ,"Regulatory Agenda" ,"Rule" ,"Sunshine Act Document" ,"Supplemental Proposed Rule" ,"Uncategorized Document"],
      filterType: null,
      filterNumChanges: null,
      filterStartDateMenuActive: false,
      filterEndDateMenuActive: false,
      filterStartDateText: new Date("2000-01-01"),
      filterEndDateText: null,
      frdocNumber: null,
      currentPage: 1,
      totalPages: null,
      itemsPerPage: 10,
      pagesToShow: 9,

      
      // Search ranking
      sortBy: null,
      sortOrder: "DESC",
      sortByOptions: [
        {title:"None", value:null},
        {title:"Date", value:"date"},
        {title:"Number of Comments", value:"num_comments"},
        {title:"Number of Responses", value:"num_responses"},
        {title:"Number of Changes", value:"num_changes"},
      ]
    };
  },
  mounted() {
    this.frdocNumber = this.$route.params.frdocNumber ? this.$route.params.frdocNumber : null;
    this.startSearch();

    // Listen for the Enter key press on the document
    document.addEventListener('keyup', this.handleEnterKey);
  },
  beforeDestroy() {
    // Remove the event listener when the component is destroyed
    document.removeEventListener('keyup', this.handleEnterKey);
  },
}
</script>
