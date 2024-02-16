<template>
  <div class="container justify-content-center mt-4">
    <v-row justify="center" align="center" class="my-1">
      <h1>Responses</h1>
    </v-row>
    <v-row class="d-flex justify-center my-3 mx-1">
      <v-select
          label="Sort by"
          id="sort-drop-down"
          v-model="sortBy"
          :items="sortByItems"
          item-title="text"
          item-value="value"
          bg-color="rie-primary-color"
          class="sort-by-field px-1"
      ></v-select>
      <v-select
          label="Sort Order"
          v-model="sortOrder"
          :items="sortOrderItems"
          item-title="text"
          item-value="value"
          bg-color="rie-primary-color"
          class="sort-order-field px-1"
      ></v-select>
      <v-select
          label="Change Detected"
          v-model="resultedInChange"
          :items="resultedInChangeItems"
          item-title="text"
          item-value="value"
          bg-color="rie-primary-color"
          class="change-detected-field px-1"
      ></v-select>
      <v-text-field
          label="Response Id Search"
          v-model="responseId"
          :clearable="true"
          bg-color="rie-primary-color"
          class="text-field px-1"
      ></v-text-field>
      <v-text-field
          label="Comment Id Search"
          v-model="commentId"
          :clearable="true"
          bg-color="rie-primary-color"
          class="text-field px-1"
      ></v-text-field>
      <v-text-field
          label="Rule Search"
          v-model="frdocNumberOrTitle"
          :clearable="true"
          bg-color="rie-primary-color"
          class="text-field px-1"
      ></v-text-field>
      <div class="d-flex justify-center px-1">
        <v-btn id="clear-filter-button" style="background-color: lightgrey" class="button-height" @click="clearFilters">Clear Filters</v-btn>
      </div>
      <div class="d-flex justify-center px-1">
        <v-btn id="submit-button" color="rie-primary-color" class="button-height" @click="searchData">Submit</v-btn>
      </div>
    </v-row>
    <v-row class="mx-1 mb-4">
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>
    <div v-if="responsesData">
      <v-card class="my-3" v-for="row in responsesData">
        <v-card-title class="px-2 w-100">
          <v-row class="card-header-title">
            <v-card-title class="px-4">Response</v-card-title>
            <v-card-title class="px-4">{{row["frdoc_number"] ? row["frdoc_number"] : 'No Rule Number'}} #{{row["response_id"] ? row["response_id"] : 'No Response ID'}}</v-card-title>
          </v-row>
        </v-card-title>
        <div class="p-4">
          <v-row>
            <v-card-title class="wrap-text w-100">
              <p><strong>Response #{{row["response_id"] ? row["response_id"] : 'No Response ID'}}</strong> <em>from</em> <strong>{{ row["title"] ? row["title"] : 'No Title' }}</strong></p>
            </v-card-title>
          </v-row>
          <v-row class="mt-1">
            <v-col cols="2" class="d-flex flex-column align-center justify-center">
              <v-icon v-if="row['any_change'] === 'Y'" icon="mdi-checkbox-outline" color="#2F4F4F" class="icon-size"></v-icon>
              <v-card-text v-if="row['any_change'] === 'Y'" class="d-flex align-center justify-center p-0">
                Change Detected&nbsp;
                <v-tooltip text="Our algorithm has detected that the regulator is making a policy change in response to the related comments. The algorithm is correct approximately 80% of the time."
                           location="bottom"
                           width="25%"
                >
                  <template v-slot:activator="{ props }">
                    <v-icon icon="mdi-information" v-bind="props"></v-icon>
                  </template>
                </v-tooltip>
              </v-card-text>
              <v-icon v-if="row['any_change'] === 'N'" icon="mdi-close-box-outline" color="#2F4F4F" class="icon-size"></v-icon>
              <v-card-text v-if="row['any_change'] === 'N'" class="d-flex align-center justify-center p-0">
                No Change Detected&nbsp;
                <v-tooltip text="Our algorithm has not detected the regulator making any policy changes in this response. The algorithm is correct approximately 80% of the time."
                           location="bottom"
                           width="25%"
                >
                  <template v-slot:activator="{ props }">
                    <v-icon icon="mdi-information" v-bind="props"></v-icon>
                  </template>
                </v-tooltip>
              </v-card-text>
            </v-col>
            <v-col cols="7" class="pt-1">
              <h6>Response Text:</h6>
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
                <RouterLink :to="{ name: 'rules', query: { frdocNumber: row['frdoc_number'] } }" class="pb-2 w-100">
                  <v-btn color="rie-primary-color" stacked="" text="View Rule" density="default" class="w-100"></v-btn>
                </RouterLink>
                <RouterLink v-if="row['number_of_comments'] !== '0'" :to="{ name: 'comments', query: { frdocNumber: row['frdoc_number'], responseId: row['response_id'] } }" class="pb-2 w-100">
                  <v-btn color="rie-primary-color" stacked="" :text="row['number_of_comments'] ? 'Comments (' + row['number_of_comments'] + ')' : 'Comments'" density="default" class="w-100"></v-btn>
                </RouterLink>
                <v-btn v-else :disabled="true" color="rie-primary-color" stacked="" :text="row['number_of_comments'] ? 'Comments (' + row['number_of_comments'] + ')' : 'Comments'" density="default" class="w-100"></v-btn>
              </div>
            </v-col>
          </v-row>
        </div>
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
            frdocNumberOrTitle: this.frdocNumberOrTitle ? this.frdocNumberOrTitle : null, // can be a string of a frdoc number
            responseId: this.responseId ? this.responseId : null,
            commentId: this.commentId ? this.commentId : null,
            resultedInChange: this.resultedInChange,
            sortBy: this.sortBy, // sort by a specific column: "numberOfComments" || NULL
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
    clearFilters() {
      this.sortBy = null;
      this.sortOrder = null;
      this.frdocNumberOrTitle = null;
      this.commentId = null;
      this.responseId = null;
      this.resultedInChange = null;
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
      frdocNumberOrTitle: this.$route.query.frdocNumber ? this.$route.query.frdocNumber : null,
      commentId: this.$route.query.commentId ? this.$route.query.commentId : null,
      responseId: null,
      sortBy: null,
      sortByItems: [
        { text: 'None', value: null },
        { text: 'Number of Linked Comments', value: 'numberOfComments' },
      ],
      sortOrder: 'DESC',
      sortOrderItems: [
        { text: 'None', value: null },
        { text: 'Asc', value: 'ASC' },
        { text: 'Desc', value: 'DESC' }
      ],
      resultedInChangeItems: [
        { text: 'None', value: null },
        { text: 'Change Detected in Response', value: 1 },
        { text: 'No Change Detected in Response', value: 0 }
      ],
      resultedInChange: this.$route.query.detectedChange === '1' ? 1 : null,
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

.sort-by-field {
  width: 250px;
}

.sort-order-field {
  width: 125px;
}

.change-detected-field {
  width: 275px;
}

.text-field {
  min-width: 250px;
}

.link-space {
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  align-items: center;
  height: 225px;
}

.icon-size {
  font-size: 8vw;
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