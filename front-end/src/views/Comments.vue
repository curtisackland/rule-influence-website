<template>
  <div class="container justify-content-center mt-5">
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
      <div class="d-flex justify-center">
        <v-btn color="rie-primary-color" class="button-height" @click="fetchData">Submit</v-btn>
      </div>
      <v-progress-linear color="rie-primary-color" height="6" rounded :indeterminate="searchIsLoading"></v-progress-linear>
    </v-row>

    <v-card class="my-3" v-for="row in commentData">
      <v-card-text>
        <v-row class="d-flex">
          <v-col cols="3">
            <v-card-title v-if="row['frdoc_number']">FR Doc: {{row["frdoc_number"]}}</v-card-title>
            <v-card-title v-else>No frdoc number</v-card-title>
            <v-card-text class="pb-0">Comment ID:</v-card-text>
            <v-card-text class="py-0">{{row["comment_id"] ? row["comment_id"] : 'No comment id'}}</v-card-text>
            <v-card-subtitle class="mt-2">Agencies:</v-card-subtitle>
            <v-virtual-scroll class="mx-3 mb-2 text-grey" :items="row['agencies']">
              <template v-slot:default="{ item }">
                {{ item }}
              </template>
            </v-virtual-scroll>
          </v-col>
          <v-col cols="6">
            <v-card-title>{{ row["title"] ? row["title"] : 'No Title' }}</v-card-title>
            <v-row class="overflow-x-auto mx-3 mt-1" style="overflow:auto">
              <v-card-subtitle class="px-0 overflow-x-auto" style="white-space: nowrap">
                {{ orgString(row['orgs']) }}
              </v-card-subtitle>
            </v-row>
            <v-card-text class="mt-1">{{ row["abstract"] ? row["abstract"] : 'No comment summary' }}</v-card-text>
          </v-col>
          <v-col cols="3">
            <v-card-title>Statistics</v-card-title>
            <v-card-text>Date Published: {{ row["publication_date"] ? row["publication_date"] : 'No date' }}</v-card-text>
            <v-card-text>Number of Changes: {{row["count"]}}</v-card-text>
            <v-card-text>Number of linked responses: {{row["linked_responses"]}}</v-card-text>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Comments",
  methods: {
    async fetchData() {
      this.searchIsLoading = true;
      this.commentData = (await axios.get("http://localhost:8080/api/comments", {
        params: { filters: {
            orgName: this.orgName ? this.orgName : null, // can be a string of an org name
            agency: this.agency  ? this.agency : null, // can be a string of an agency
            sortBy: this.sortBy, // sort by a specific column: "numberOfChanges" || "linkedResponses" || NULL
            sortOrder: this.sortOrder // can be "DESC" || "ASC" || NULL
          }}
      })).data;
      this.searchIsLoading = false;
    },
    orgString(orgs) {
      // orgs[0] because when there are no orgs an array with a null in it is returned like: [null]
      return orgs[0] ? 'Organizations: ' + orgs.join(', ') : 'No organizations';
    },
    handleEnterKey(event) {
      // Check if the pressed key is Enter (key code 13)
      if (event.key === 'Enter') {
        // Trigger the click event of the button
        this.fetchData();
      }
    },
  },
  data() {
    return {
      searchIsLoading: false,
      commentData: null,
      orgName: null,
      agency: null,
      sortBy: null,
      sortByItems: [
        { text: 'None', value: null },
        { text: 'Number of Changes', value: 'numberOfChanges' },
        { text: 'Number of Linked Responses', value: 'linkedResponses' }
      ],
      sortOrder: 'DESC',
      sortOrderItems: [
        { text: 'None', value: null},
        { text: 'Asc', value: 'ASC'},
        { text: 'Desc', value: 'DESC'}
      ]
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
</style>