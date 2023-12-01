<template>
  <div class="w-50">

    <v-card>
      <v-card-title>AGENCY</v-card-title>
      <v-card-text>
        <v-row>
          <v-col>
            COL
          </v-col>
          <v-col>
            COL2
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
        "agency": ["TEST_AGENCY"],
        "publication_date": "1995-01-03",
        "fr_type": "FR_TYPE",
        "frdoc_number": "FRDOC_NUM",
        "title": "TEST_TITLE",
        "abstract": "TEST_ABSTRACT",
        "action": "TEST_ACTION"
      }]
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
