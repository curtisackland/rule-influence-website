<template>
  <div class="container justify-content-center mt-5">
    <v-row>
      <v-select></v-select>
      <v-select></v-select>
      <v-select></v-select>
      <v-text-field v-model="startDateTextRange" label="Start Date" prepend-icon="mdi-calendar" readonly>
        <v-menu activator="parent" v-model="startDateMenu" :close-on-content-click="false" >
          <v-date-picker v-model="startDateTextRange" color="rie-primary-color" format="yyyy-MM-dd" type="date" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="startDateMenu = false">Close</v-btn>
        </v-menu>
      </v-text-field>
      <v-text-field v-model="endDateTextRange" label="End Date" prepend-icon="mdi-calendar" readonly>
        <v-menu activator="parent" v-model="endDateMenu" :close-on-content-click="false">
          <v-date-picker v-model="endDateTextRange" color="rie-primary-color" show-adjacent-months range border>
          </v-date-picker>
          <v-btn @click="endDateMenu = false">Close</v-btn>
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
    },
  },
  data() {
    return {
      tableData: null,
      startDateMenu: false,
      endDateMenu: false,
      startDateTextRange: new Date("Jan 1 2000 00:00:00"),
      endDateTextRange: null,
    };
  },
  mounted() {
    this.fetchData();
  }
}
</script>
