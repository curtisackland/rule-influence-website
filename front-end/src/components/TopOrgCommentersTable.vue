<template>
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
          <a v-if="tableData === row['org_name']" :href="'/orgs/' + orglinks[tableData]">{{tableData}}</a>
          <span v-if="tableData !== row['org_name']">{{tableData}}</span>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
  import axios from "axios"
  export default {
    name: "TopOrgCommentersTable",
    methods: {
      async fetchData() {
        //this.tableData = (await axios.get("https://api.coindesk.com/v1/bpi/currentprice.json")).response;
        const data = [{"org_name":"Via","y_count":"100","n_frdocs":"200"}];
        this.tableHead = Object.keys(data[0]);
        this.tableData = data;
      }
    },
    data() {
      return {
        tableHead: ["org_name"],
        tableData: null,
        orglinks: {"Via": "Via"}

      };
    },
    mounted() {
      this.fetchData();
    }
  }
</script>

<style scoped>
thead {

}

th {
  border: 1px solid black;
  background-color: lightgray;
}

td {
  border: 1px solid black;
  background-color: #E0E0FF;
}
</style>