<template>
  <div class="container justify-content-center mt-4">
    <v-row justify="center" align="center" class="my-1">
      <h1>Comments</h1>
    </v-row>
    <v-row class="my-4 mx-1">
      <v-select
          label="Sort by"
          v-model="sortBy"
          :items="sortByItems"
          item-title="text"
          item-value="value"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-select>
      <v-select
          label="Sort Order"
          v-model="sortOrder"
          :items="sortOrderItems"
          item-title="text"
          item-value="value"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-select>
      <v-text-field
          label="Organization Search"
          v-model="orgName"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-text-field>
      <v-text-field
          label="Agency Search"
          v-model="agency"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-text-field>
      <v-text-field :model-value="filterStartDateText?.toISOString().split('T')[0]" label="Start Date" append-inner-icon="mdi-calendar" readonly="true" class="mr-3">
        <v-menu activator="parent" v-model="filterStartDateMenuActive" :close-on-content-click="false" >
          <v-date-picker v-model="filterStartDateText" color="rie-primary-color" format="yyyy-MM-dd" type="date" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterStartDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field :model-value="filterEndDateText?.toISOString().split('T')[0]" label="End Date" append-inner-icon="mdi-calendar" readonly="true" class="mr-3">
        <v-menu activator="parent" v-model="filterEndDateMenuActive" :close-on-content-click="false">
          <v-date-picker v-model="filterEndDateText" color="rie-primary-color" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterEndDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field
          label="Comment Id Search"
          v-model="commentId"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-text-field>
      <div class="d-flex justify-center">
        <v-btn color="rie-primary-color" class="button-height" @click="searchData">Submit</v-btn>
      </div>
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>
    <div v-if="commentData">
      <v-card class="my-3" v-for="row in commentData">
        <v-card-text class="p-4 test">
          <v-row>
            <v-col cols="9">
              <v-row class="m-0 p-0">
                <v-col class="p-0 m-0">
                  <v-card-title class="p-0 mx-0">Comment ID: {{row["comment_id"] ? row["comment_id"] : 'No comment id'}}</v-card-title>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row class="mt-1">
            <v-col cols="3">
              <v-card-text class="ml-0 pl-0">Agencies:</v-card-text>
              <div style="display: flex; height: 150px;">
                <v-virtual-scroll class="ml-0 pl-0 mb-2 text-grey" :items="row['agencies']">
                  <template v-slot:default="{ item }">
                    {{ item }}
                  </template>
                </v-virtual-scroll>
              </div>
            </v-col>
            <v-col cols="3">
              <v-card-text class="ml-0 pl-0">Organizations:</v-card-text>
              <div v-if="row['orgs']['0'] != null" style="display: flex; height: 150px;">
                <v-virtual-scroll v-if="row['orgs']" class="ml-0 pl-0 mb-2 text-grey" :items="row['orgs']">
                  <template v-slot:default="{ item }">
                    {{ item }}
                  </template>
                </v-virtual-scroll>
              </div>
              <div v-else>
                <v-card-subtitle>No organizations</v-card-subtitle>
              </div>
            </v-col>
            <v-col cols="3">
              <v-card-text>Statistics</v-card-text>
              <div class="stats-space pb-3">
                <v-card-subtitle class="wrap-text">Date Published: {{ row["receive_date"] ? row["receive_date"].split('T')[0] : 'No date' }}</v-card-subtitle>
                <v-card-subtitle class="wrap-text">Changes: {{row["number_of_changes"] ? row["number_of_changes"] : 'Unknown'}}</v-card-subtitle>
                <v-card-subtitle class="wrap-text">Linked responses: {{row["linked_responses"] ? row["linked_responses"] : 'Unknown'}}</v-card-subtitle>
              </div>
            </v-col>
            <v-col cols="3">
              <div class="link-space">
                <RouterLink :to="{ name: 'responses', query: { commentId: row['comment_id'] } }" class="pb-2 w-100">
                  <v-btn color="rie-primary-color" stacked="" text="Responses" density="compact" class="w-100"></v-btn>
                </RouterLink>
                <RouterLink :to="{ name: 'rules', query: { commentId: row['comment_id'] } }" class="pb-2 w-100">
                  <v-btn color="rie-primary-color" stacked="" text="Rules Page" density="compact" class="w-100"></v-btn>
                </RouterLink>
              </div>
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
    <div v-if="errorMessage">
      <a>An error has has occurred. Please try again. Error: {{ this.errorMessage }}</a>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import PaginationBar from "@/components/PaginationBar.vue";

export default {
  name: "Comments",
  components: {
    PaginationBar
  },
  methods: {
    async fetchData() {
      // Cancel the previous request if it exists
      if (this.axiosCancelSource) {
        this.axiosCancelSource.cancel('Request canceled by the user');
      }

      // Create a new CancelToken source for the current request
      this.axiosCancelSource = axios.CancelToken.source();

      this.errorMessage = null;
      this.searchIsLoading = true;
      this.scrollToTop();
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/comments", {
        params: { filters: {
            orgName: this.orgName ? this.orgName : null, // can be a string of an org name
            agency: this.agency  ? this.agency : null, // can be a string of an agency
            startDate: this.filterStartDateText ? this.filterStartDateText.toISOString().split('.')[0] + 'Z' : null,
            endDate: this.filterEndDateText ? this.filterEndDateText.toISOString().split('.')[0] + 'Z' : null,
            frdocNumber: this.frdocNumber ? this.frdocNumber : null,
            responseId: this.responseId ? this.responseId : null,
            sortBy: this.sortBy, // sort by a specific column: "numberOfChanges" || "linkedResponses" || NULL
            sortOrder: this.sortOrder, // can be "DESC" || "ASC" || NULL
            page: this.currentPage, // has to be an integer || NULL
            itemsPerPage: this.itemsPerPage, // has to be an integer || NULL
            commentId: this.commentId,
          }},
        cancelToken: this.axiosCancelSource.token,
      }).then(response => {
        this.commentData = response.data.data;
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
      await this.fetchData();
    },
    updateCurrentPage(newPage) {
      this.currentPage = newPage;
      this.fetchData();
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
      searchIsLoading: false,
      commentData: null,
      orgName: null,
      agency: null,
      frdocNumber: this.$route.query.frdocNumber ? this.$route.query.frdocNumber : null,
      responseId: this.$route.query.responseId ? this.$route.query.responseId : null,
      filterStartDateMenuActive: false,
      filterEndDateMenuActive: false,
      filterStartDateText: null,//new Date("2000-01-01"),
      filterEndDateText: null,
      sortBy: 'numberOfChanges',
      sortByItems: [
        { text: 'None', value: null },
        { text: 'Number of Changes', value: 'numberOfChanges' },
        { text: 'Number of Linked Responses', value: 'linkedResponses' },
        { text: 'Date', value: 'date' },
      ],
      sortOrder: 'DESC',
      sortOrderItems: [
        { text: 'None', value: null},
        { text: 'Asc', value: 'ASC'},
        { text: 'Desc', value: 'DESC'}
      ],
      errorMessage: null,
      currentPage: 1,
      totalPages: null,
      itemsPerPage: 10,
      pagesToShow: 9,
      axiosCancelSource: null,
      commentId: null,
    };
  },
  mounted() {
    this.commentId = this.$route.params.commentId ? this.$route.params.commentId : null;

    this.fetchData();

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
.button-height {
  height: 56px;
}

.v-card-title {
  white-space: normal;
}

.stats-space {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 150px;
}

.link-space {
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  align-items: center;
  height: 225px;
}

.wrap-text {
  white-space: normal !important;
}

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

</style>