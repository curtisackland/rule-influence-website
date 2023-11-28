<script>
  import Table from "./TableView.vue"
  import axios from "axios"
  
  export default {
    name: "TopOrgCommentersTable",
    methods: {
      async fetchData() {
        this.tableData = (await axios.get("http://localhost:8080")).data;
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
    <Table :tableHead="tableHead" :tableData="tableData"/>
  </div>
</template>