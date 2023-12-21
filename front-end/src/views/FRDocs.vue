<template>
  <div class="container justify-content-center mt-5">
    <v-row>
      <v-select v-model="filterFRType" label="fr type" :items="filterOptionsFRType"></v-select>
      <v-select v-model="filterType" label="type" :items="filterOptionsType"></v-select>
      <v-combobox label="topic"></v-combobox>
      <v-text-field :model-value="filterStartDateText?.toISOString().split('T')[0]" label="Start Date" prepend-icon="mdi-calendar" readonly>
        <v-menu activator="parent" v-model="filterStartDateMenuActive" :close-on-content-click="false" >
          <v-date-picker v-model="filterStartDateText" color="rie-primary-color" format="yyyy-MM-dd" type="date" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterStartDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field :model-value="filterEndDateText?.toISOString().split('T')[0]" label="End Date" prepend-icon="mdi-calendar" readonly>
        <v-menu activator="parent" v-model="filterEndDateMenuActive" :close-on-content-click="false">
          <v-date-picker v-model="filterEndDateText" color="rie-primary-color" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="filterEndDateMenuActive = false">Close</v-btn>
        </v-menu>
      </v-text-field>
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
            <v-btn>federalregister.gov</v-btn>
          </v-col>
          <v-col cols="9">
            <v-card-title v-if="row['title']">{{ row["title"] }}</v-card-title>
            <v-card-title v-else>No Title</v-card-title>
            <v-card-subtitle v-if="row['publication_date'] && row['action']">{{ row["publication_date"] }} &bull; {{ row["action"] }}</v-card-subtitle>
            <v-card-subtitle v-else>No publication date or action</v-card-subtitle>
            <v-card-text v-if="row['abstract']">{{ row["abstract"] }}</v-card-text>
            <v-card-text v-else>No abstract</v-card-text>
            <v-row>
              <v-col>
                {{row["prevFRDoc"]}}
              </v-col>
              <v-col>
                {{row["nextFRDoc"]}}
              </v-col>
            </v-row>
            <div>

            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>



<script>
import axios from "axios"
export default {
  name: "FRDocs",
  methods: {
    async fetchData() {

      const queryParams = {
        filters: {
          start_date: this.filterStartDateText?.toISOString().split('T')[0],
          end_date: this.filterEndDateText?.toISOString().split('T')[0],
        }
      }
      this.tableData = (await axios.get("http://localhost:8080/api/frdocs", {params:queryParams, timeout:6000000})).data;
    },
  },
  data() {
    return {
      tableData: null,
      
      // Search filters
      filterOptionsFRType: ["Correction", "Notice", "Presidential Document", "Proposed Rule", "Rule", "Sunshine Act Document", "Uncategorized Document",],
      filterFRType: null,
      filterOptionsType: ["Advance Notice of Proposed Rule" ,"Affirmation of Rule" ,"Comment Extension" ,"Correction" ,"Direct Rule" ,"Filing Extension" ,"Interim Rule" ,"Notice" ,"Presidential Document" ,"Proposed Rule" ,"Regulatory Agenda" ,"Rule" ,"Sunshine Act Document" ,"Supplemental Proposed Rule" ,"Uncategorized Document"],
      filterType: null,
      filterNumChanges: null,
      filterStartDateMenuActive: false,
      filterEndDateMenuActive: false,
      filterStartDateText: new Date("2000-01-01"),
      filterEndDateText: null,
      
      // Search ranking
    };
  },
  mounted() {
    this.fetchData();
  }
}
</script>
