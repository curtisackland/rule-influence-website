<template>
  <div class="container justify-content-center mt-5">
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
      this.tableData = (await axios.get("http://localhost:8080/api/frdocs")).data;
    }
  },
  data() {
    return {
      tableData: null
    };
  },
  mounted() {
    this.fetchData();
  }
}
</script>
