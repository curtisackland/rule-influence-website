<template>
  <div class="container justify-content-center mt-5">
    <v-card class="my-3" v-for="row in tableData">
      <v-card-text>
        <v-row class="d-flex">
          <v-col cols="3">
            <v-card-title>{{row["frdoc_number"]}}</v-card-title>
            <v-card-subtitle>{{row["fr_type"]}}</v-card-subtitle>
            <v-virtual-scroll class="mx-3 mb-2" :items="row['agencies']">
              <template v-slot:default="{ item }">
                {{ item }}
              </template>
            </v-virtual-scroll>
            <v-btn>federalregister.gov</v-btn>
          </v-col>
          <v-col cols="9">
            <v-card-title>{{ row["title"] }}</v-card-title>
            <v-card-subtitle>{{ row["publication_date"] }} &bull; {{ row["action"] }}</v-card-subtitle>
            <v-card-text>{{ row["abstract"] }}</v-card-text>
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
