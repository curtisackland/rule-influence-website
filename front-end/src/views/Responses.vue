<template>
  <div class="container justify-content-center mt-4">
    <v-row justify="center" align="center" class="my-1">
      <h1>Responses</h1>
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
      <v-select
          label="Resulted in a change"
          v-model="resultedInChange"
          :items="resultedInChangeItems"
          item-title="text"
          item-value="value"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-select>
      <v-text-field
          label="FR Doc Search"
          v-model="frdocNumber"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-text-field>
      <v-text-field
          label="Response Id Search"
          v-model="responseId"
          bg-color="rie-primary-color"
          class="mr-3"
      ></v-text-field>
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
    <div v-if="responsesData">
      <v-card class="my-3" v-for="row in responsesData">
        <v-card-text class="p-4 test">
          <v-row>
            <v-col cols="9">
              <v-row class="m-0 p-0">
                <v-col class="p-0 m-0">
                  <v-card-title class="p-0 mx-0">Response ID: {{row["response_id"] ? row["response_id"] : 'No response id'}}</v-card-title>
                </v-col>
              </v-row>
              <v-row class="m-0 p-0">
                <v-col class="p-0">
                  <v-card-title class="p-0 mx-0">FR Document: {{ row["title"] ? row["title"] : 'No Title' }}</v-card-title>
                </v-col>
              </v-row>
            </v-col>
            <v-col>
              <v-row class="m-0 p-0">
                <v-col class="m-0 p-0">
                  <v-card-title class="p-0 mx-0">FR Document Number:</v-card-title>
                </v-col>
              </v-row>
              <v-row class="m-0 p-0">
                <v-col class="m-0 p-0">
                  <v-card-title class="p-0 mx-0">{{row["frdoc_number"] ? row["frdoc_number"] : 'No frdoc number'}}</v-card-title>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row class="mt-1">
            <v-col cols="3">
              <v-card-text class="wrap-text p-0">Number of Linked Comments: {{row["number_of_comments"] ? row["number_of_comments"] : 'Unknown'}}</v-card-text>
              <v-card-text class="ml-0 pl-0">Resulted In Change: {{row["any_change"] ? row["any_change"] : 'Unknown'}}</v-card-text>
            </v-col>
            <v-col cols="6">
              <v-card-text class="p-0">Response Text:</v-card-text>
              <div v-if="row['text']" style="display: flex; height: 200px;">
                <v-virtual-scroll class="ml-0 pl-0 mb-2 mt-1 text-grey" :items="row['text']">
                  <template v-slot:default="{ item }">
                    {{ item }} <br><br>
                  </template>
                </v-virtual-scroll>
              </div>
              <v-card-subtitle v-else class="p-0 mt-2">No response text</v-card-subtitle>
            </v-col>
            <v-col cols="3">
              <div class="link-space">
                <RouterLink :to="{ name: 'frdocs', query: { frdocNumber: row['frdoc_number'] } }" class="pb-2 w-100">
                  <v-btn color="rie-primary-color" stacked="" text="FR Document Page" density="default" class="w-100"></v-btn>
                </RouterLink>
                <RouterLink :to="{ name: 'comments', query: { frdocNumber: row['frdoc_number'], responseId: row['response_id'] } }" class="pb-2 w-100">
                  <v-btn color="rie-primary-color" stacked="" text="Comments Page" density="default" class="w-100"></v-btn>
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
      await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/responses", {
        params: { filters: {
            frdocNumber: this.frdocNumber ? this.frdocNumber : null, // can be a string of a frdoc number
            responseId: this.responseId ? this.responseId : null,
            commentId: this.commentId ? this.commentId : null,
            resultedInChange: this.resultedInChange,
            sortBy: this.sortBy, // sort by a specific column: "numberOfComments" || "date" || NULL
            sortOrder: this.sortOrder, // can be "DESC" || "ASC" || NULL
            page: this.currentPage, // has to be an integer || NULL
            itemsPerPage: this.itemsPerPage // has to be an integer || NULL
          }},
        cancelToken: this.axiosCancelSource.token,
      }).then(response => {
        this.responsesData = response.data.data;
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
      responsesData: null,
      orgName: null,
      frdocNumber: this.$route.query.frdocNumber ? this.$route.query.frdocNumber : null,
      commentId: this.$route.query.commentId ? this.$route.query.commentId : null,
      responseId: null,
      sortBy: null,
      sortByItems: [
        { text: 'None', value: null },
        { text: 'Number of Linked Comments', value: 'numberOfComments' },
        { text: 'Date', value: 'date' }
      ],
      sortOrder: 'DESC',
      sortOrderItems: [
        { text: 'None', value: null },
        { text: 'Asc', value: 'ASC' },
        { text: 'Desc', value: 'DESC' }
      ],
      resultedInChange: null,
      resultedInChangeItems: [
        { text: 'None', value: null },
        { text: 'Response resulted in a change', value: 1 },
        { text: 'Response resulted in no change', value: 0 }
      ],
      errorMessage: null,
      currentPage: 1,
      totalPages: null,
      itemsPerPage: 10,
      pagesToShow: 9,
      axiosCancelSource: null,
    };
  },
  mounted() {
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