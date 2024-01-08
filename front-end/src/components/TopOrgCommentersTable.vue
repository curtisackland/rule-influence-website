<script>
  import Table from "./TableView.vue"
  import axios from "axios"
  
  export default {
    name: "TopOrgCommentersTable",
    props: {
      orgNameProp: String,
      sortByProp: String,
      sortOrderProp: String
    },
    methods: {
      async fetchData() {
        this.tableData = (await axios.get(import.meta.env.VITE_BACKEND_URL + "/api/home",{
          params: { 
            filters: {
              orgName: this.orgNameProp, // can be a string of the Organization name
              sortBy: this.sortByProp, // sort by a specific column: "orgName" || "yCount" || "frdocs" || NULL
              sortOrder: this.sortOrderProp // can be "DESC" || "ASC" || NULL
            }}
        })).data.data;

        this.tableHead = Object.keys(this.tableData[0]);
      }
    },
    components: {
      Table
    },
    data() {
      return {
        tableHead: null,
        tableData: null
      };
    },
    mounted() {
      this.fetchData();
    }
  }
</script>

<template>
  <div v-if="tableHead && tableData">
    <Table :tableHead="tableHead" :tableData="tableData" />
  </div>
</template>