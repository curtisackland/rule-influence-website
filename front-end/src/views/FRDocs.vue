<template>
  <div class="container justify-content-center">
    <v-card class="datarow" v-for="row in tableData">
      <v-card-text>
        <v-row class="d-flex">
          <v-col class="d-flex" cols="3">
            <v-row>
            <v-virtual-scroll :items="row['agencies']">
              <template v-slot:default="{ item }">
                {{ item }}
              </template>
            </v-virtual-scroll>
            </v-row>
            <v-row>
            <v-card-actions class="align-self-end" ><v-btn>asd</v-btn></v-card-actions>
            </v-row>
          </v-col>
          <v-col cols="9">
            <v-card-title>{{ row["title"] }}</v-card-title>
            <v-card-subtitle>{{ row["frdoc_number"] }}</v-card-subtitle>
            <v-card-text class="">{{ row["abstract"] }}</v-card-text>
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
      /*
      this.tableData = (await axios.get("http://localhost:8080/api/frdocs/" + this.orgName, {
        params: { filters: {
            commentID: null, // can be a string of the comment id
            frdocNumber: null, // can be a string of the frdoc number
            sortBy: null, // sort by a specific column: "responseID" || "score" || "normScore" || NULL
            sortOrder: null // can be "DESC" || "ASC" || NULL
          }}
      })).data;
      this.tableHead = Object.keys(this.tableData[0]);*/

      this.tableData = [{
        "agencies": ["TEST_AGENCY", "TEST_AGENCY2"],
        "publication_date": "1995-01-03",
        "fr_type": "FR_TYPE",
        "frdoc_number": "FRDOC_NUM",
        "title": "TEST_TITLE",
        "abstract": "TEST_ABSTRACT",
        "action": "TEST_ACTION"
      },
        {
        "agencies": ["TEST_AGENCY"],
          "publication_date": "1995-01-03",
          "fr_type": "FR_TYPE",
          "frdoc_number": "FRDOC_NUM",
          "title": "TEST_TITLE",
          "abstract": "This final rule increases the interest charge from 1 percent to 1\\1/2\\ percent per month and adds a late payment charge of 5 percent on delinquent assessments owed by handlers under Marketing Order No. 929 covering cranberries grown in ten states. This rule contributes to the efficient operation of the program by ensuring that adequate funds are available to cover budgeted expenses incurred under the marketing order.",
          "action": "TEST_ACTION"
    },
      ]
    }
  },
  props: ["orgName"],
  data() {
    return {
      tableHead: ["org_name"],
      tableData: null
    };
  },
  mounted() {
    this.fetchData();
  }
}
</script>

<style scoped>
.datarow {
  //height:200px;
}
</style>
