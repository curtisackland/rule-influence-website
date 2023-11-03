<script>
  import {ref} from "vue";
  import {watch} from "vue";
  
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

  <!-- <div v-if="tableHead && tableData">
    <table>
      <thead>
        <tr>
          <th v-for="tableHeader in tableHead">
            {{tableHeader}}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in tableData">
          <td v-for="tableData in row">
            <a v-if="tableData === row['org_name']" :href="'/organization/' + encodeURIComponent(tableData)">{{tableData}}</a>
            <span v-if="tableData !== row['org_name']">{{tableData}}</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div> -->
</template>

<style scoped>

</style>
