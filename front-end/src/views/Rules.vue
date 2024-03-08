<template>
  <div class="container justify-content-center mt-4">
    <v-row justify="center" align="center" class="my-1">
      <h1>Rules</h1>
    </v-row>
    <v-row class="my-b mx-1">
      <v-text-field id="filter-id-title"
          label="Title/Rule Number"
          v-model="filterTitleAndFRDocNumber"
          :clearable="true"
          class="mr-3"
      ></v-text-field>
      <v-select id="filter-type" v-model="filterType" label="Document Type" :items="filterOptionsType" item-title="title" item-value="value" class="mr-3"></v-select>
      <v-text-field id="filter-topic" v-model="filterAgency" label="Agency" class="mr-3" :clearable="true"></v-text-field>
      <v-text-field id="filter-topic" v-model="filterOrg" label="Organization" class="mr-3" :clearable="true"></v-text-field>
      <v-text-field id="filter-date-start" :model-value="filterStartDateText?.toISOString().split('T')[0]" label="Start Date" append-inner-icon="mdi-calendar" :readonly="true" :clearable="true" @click:clear="filterStartDateText = null" class="mr-3" >
        <v-menu activator="parent" v-model="filterStartDateMenuActive" :close-on-content-click="false" >
          <v-date-picker v-model="filterStartDateText" color="rie-primary-color" format="yyyy-MM-dd" type="date" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterStartDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field id="filter-date-end" :model-value="filterEndDateText?.toISOString().split('T')[0]" label="End Date" append-inner-icon="mdi-calendar" :readonly="true" :clearable="true" @click:clear="filterEndDateText = null">
        <v-menu activator="parent" v-model="filterEndDateMenuActive" :close-on-content-click="false">
          <v-date-picker v-model="filterEndDateText" color="rie-primary-color" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterEndDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
    </v-row>
    <v-row class="mx-1">
      <v-select id="sort-options" v-model="sortBy" :items="sortByOptions" item-title="title" item-value="value" label="Sort Options" class="mr-3"/>
      <v-switch id="sort-order" v-model="sortOrder" true-value="ASC" false-value="DESC" :label="'Sort Order: ' + sortOrder" color="rie-primary-color"/>
      <v-btn id="clear-button" class="mx-2" style="background-color: lightgrey; height: 56px;" @click="clearFilters()">Clear Filters</v-btn>
      <v-btn id="search-button" class="ml-2" color="rie-primary-color" style="height: 56px;" @click="startSearch()">Search</v-btn>
    </v-row>
    <v-row v-if="filterIdArray.length > 0">
      <v-col cols="12">
        <v-btn @click="filterClearRelatedDocs(); startSearch()"> {{sourceDoc ? "Remove filter for documents related to " + sourceDoc : "Remove filter for related documents"}}</v-btn>
      </v-col>
    </v-row>
    <v-row class="mb-4 mx-1">
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>

    <v-card class="my-3" v-for="row in tableData">
      <v-card-title class="px-2 w-100">
        <v-row class="card-header-title">
          <v-card-title class="card-header-text">{{row["type"] ?? "Unknown Type"}}</v-card-title>
          <v-card-title class="rule-id px-4 card-header-text" v-if="row['frdoc_number']">{{row["frdoc_number"] ?? "No Document ID"}}</v-card-title>
        </v-row>
      </v-card-title>
      <v-card-title class="px-4 rule-title wrap-text">{{ row["title"] ?? "No Title" }}</v-card-title>
      <v-divider class="mt-0"></v-divider>
      <v-row class="rule-card-body">
        <v-col cols="3">
          <h5 class="px-4 pb-0">
            Published:
          </h5>
          <div class="px-4 pt-0">
            {{row['publication_date'] ?? "No Date"}}
          </div>
          <h5 class="px-4 pt-4 pb-0">
            By:
          </h5>
          <div class="px-4 pt-0" style="height: 100px; display: flex;">
            <v-virtual-scroll :items="row['agencies']">
              <template v-slot:default="{ item }">
                <p class="mb-2">
                {{ item }}
                </p>
              </template>
            </v-virtual-scroll>
          </div>
          <div class="p-4">
            <v-btn color="rie-primary-color" :stacked="true" density="compact" :href="'https://www.federalregister.gov/d/' + row['frdoc_number']" target="_blank">
              <v-row>
                <v-col cols="10" class="text-center">
                  View on FederalRegister.gov
                </v-col>
                <v-col cols="2" class="d-flex justify-center align-center">
                  <v-icon>mdi-open-in-new</v-icon>
                </v-col>
              </v-row>
            </v-btn>
          </div>
        </v-col>
        <v-col cols="6">
          <h5 class="pb-0">
            Summary:
          </h5>
          <div class="pt-0">
            <div v-if="row['abstract']" style="display: flex; height: 250px;">
              <v-virtual-scroll class="ml-0 pl-0 mb-2 mt-1"  :items="[row['abstract']]">
                <template v-slot:default="{ item }">
                  {{ item }}
                </template>
              </v-virtual-scroll>
            </div>
            <div v-else>
              No abstract
            </div>
          </div>
        </v-col>
        <v-col cols="3">
          <div class="px-4 pt-0 link-space">
            <a v-if="row['prevFRDoc'].length > 0 || row['nextFRDoc'].length > 0" :href="getRulePageSearchPath({'sourceDoc':row['frdoc_number'], 'filterIdArray':[...row['prevFRDoc'], ...row['nextFRDoc']]})" class="w-100" >
              <v-btn color="rie-primary-color" stacked="" :text="'Related Rules (' + (row['prevFRDoc'].length + row['nextFRDoc'].length) + ')'" density="compact" class="w-100 my-2"></v-btn>
            </a>
            <div v-else>
              <v-btn :disabled="true" color="rie-primary-color" stacked="" :text="'Related Rules (' + (row['prevFRDoc'].length + row['nextFRDoc'].length) + ')'" density="compact" class="w-100 my-2"></v-btn>
            </div>
            <RouterLink v-if="parseInt(row['comment_count'], 10) !== 0" :to="{ name: 'comments', query: { frdocNumber: row['frdoc_number'] } }" class="w-100">
              <v-btn color="rie-primary-color" stacked="" :text="'Comments (' + row['comment_count'] + ')'" density="compact" class="my-2 w-100"></v-btn>
            </RouterLink>
            <div v-else>
              <v-btn :disabled="true" color="rie-primary-color" stacked="" :text="'Comments (' + row['comment_count'] + ')'" density="compact" class="my-2 w-100" ></v-btn>
            </div>
            <RouterLink v-if="parseInt(row['response_count'], 10) !== 0" :to="{ name: 'responses', query: { frdocNumber: row['frdoc_number'] } }" class="w-100">
              <v-btn color="rie-primary-color" stacked="" :text="'Responses (' + row['response_count'] + ')'" density="compact" class="my-2 w-100" ></v-btn>
            </RouterLink>
            <div v-else>
              <v-btn :disabled="true" color="rie-primary-color" stacked="" :text="'Responses (' + row['response_count'] + ')'" density="compact" class="my-2 w-100" ></v-btn>
            </div>
            <RouterLink v-if="parseInt(row['response_count'], 10) !== 0" :to="{ name: 'responses', query: { frdocNumber: row['frdoc_number'], detectedChange:'1' } }" class="w-100">
              <v-btn color="rie-primary-color" stacked="" :text="'Changes (' + row['change_count'] + ')'" density="compact" class="my-2 w-100" ></v-btn>
            </RouterLink>
            <div v-else>
              <v-btn :disabled="true" color="rie-primary-color" stacked="" :text="'Changes (' + row['change_count'] + ')'" density="compact" class="my-2 w-100" ></v-btn>
            </div>
          </div>
        </v-col>
      </v-row>
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
  name: "Rules",
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
          commentId: this.filterCommentId,
          titleOrId: this.filterTitleAndFRDocNumber,
          agency: this.filterAgency,
          org: this.filterOrg,
          page: this.currentPage, // has to be an integer || NULL
          itemsPerPage: this.itemsPerPage, // has to be an integer || NULL
          idArray: this.filterIdArray,
        }
      }
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/rules", {params:queryParams, cancelToken: this.axiosCancelSource.token})
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
    getRulePageSearchPath(searchParameters) {
      if (searchParameters === null) {
        return "/rules";
      }

      let queryArray = [];
      for (const param in searchParameters) {
        if (Array.isArray(searchParameters[param])) {
          let arrayData = "[" + searchParameters[param].map((e) => "\"" + e + "\"").toString() + "]";
          queryArray.push(encodeURIComponent(param) + "=" + encodeURIComponent(arrayData));
        } else {
          queryArray.push(encodeURIComponent(param) + "=" + searchParameters[param]);
        }
      }

      let queryString = "?" + queryArray.join("&");
      return (queryArray.length > 0 ? "/rules" + queryString : "/rules");
    },
    filterClearRelatedDocs() {
      this.filterIdArray=[];
      this.sourceDoc=null;
    },
    clearFilters() {
      this.filterFRType = null;
      this.filterType = null;
      this.filterNumChanges = null;
      this.filterStartDateMenuActive = false;
      this.filterEndDateMenuActive = false;
      this.filterStartDateText = null;
      this.filterEndDateText = null;
      this.filterCommentId = null;
      this.frdocNumber = null;
      this.filterAgency = null;
      this.filterOrg = null;
      this.filterTitleAndFRDocNumber = null;
      this.filterClearRelatedDocs();
    },
  },
  data() {
    return {
      tableData: null,
      searchIsLoading: false,
      axiosCancelSource: null,
      errorMessage: null,
      
      // Search filters
      filterOptionsFRType:
          [{title:"None",value:null}, {title:"Correction",value:"Correction"}, {title:"Notice",value:"Notice"}, {title:"Presidential Document",value:"Presidential Document"}, {title:"Proposed Rule",value:"Proposed Rule"}, {title:"Rule",value:"Rule"}, {title:"Sunshine Act Document",value:"Sunshine Act Document"}, {title:"Uncategorized Document",value:"Uncategorized Document"},],
      filterFRType: null,
      filterOptionsType:
          [{title:"None",value:null}, {title:"Advance Notice of Proposed Rule" ,value:"Advance Notice of Proposed Rule" },{title:"Affirmation of Rule" ,value:"Affirmation of Rule" },{title:"Comment Extension" ,value:"Comment Extension" },{title:"Correction" ,value:"Correction" },{title:"Direct Rule" ,value:"Direct Rule" },{title:"Filing Extension" ,value:"Filing Extension" },{title:"Interim Rule" ,value:"Interim Rule" },{title:"Notice" ,value:"Notice" },{title:"Presidential Document" ,value:"Presidential Document" },{title:"Proposed Rule" ,value:"Proposed Rule" },{title:"Regulatory Agenda" ,value:"Regulatory Agenda" },{title:"Rule" ,value:"Rule" },{title:"Sunshine Act Document" ,value:"Sunshine Act Document" },{title:"Supplemental Proposed Rule" ,value:"Supplemental Proposed Rule" },{title:"Uncategorized Document",value:"Uncategorized Document"},],
      filterType: null,
      filterNumChanges: null,
      filterStartDateMenuActive: false,
      filterEndDateMenuActive: false,
      filterStartDateText: null,
      filterEndDateText: null,
      filterAgency: null,
      filterOrg: null,
      filterCommentId: this.$route.query.commentId ?? null,
      frdocNumber: this.$route.query.frdocNumber ?? null,
      filterTitleAndFRDocNumber: null,
      filterIdArray: [],
      sourceDoc: null,
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
    // Read search parameters from URL
    this.filterTitleAndFRDocNumber = this.$route.query.frdocNumber ?? null;
    this.filterOrg = this.$route.query.org ?? null;
    this.sourceDoc = this.$route.query.sourceDoc ?? null;
    this.filterIdArray = this.$route.query.filterIdArray ? JSON.parse(this.$route.query.filterIdArray) : [];

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


<style scoped>
/* Custom scrollbar styles */
.v-virtual-scroll::-webkit-scrollbar {
  width: 3px;
}

.v-virtual-scroll::-webkit-scrollbar-thumb {
  background-color: #888;
}

.v-virtual-scroll::-webkit-scrollbar-track {
  background-color: #f1f1f1;
}

.rule-card-body {
  height: 350px;
}

.link-space {
  height: 100%;
  padding-bottom: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-around;
}

</style>
