<template>
  <div v-if="tableHead && tableData">
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
  </div>
</template>

<script>
  import axios from "axios"
  export default {
    name: "OrgResponsesTable",
    methods: {
      async fetchData() {
        this.tableData = (await axios.get("http://localhost:8080/api/organization/" + this.orgName)).data;
        this.tableHead = Object.keys(this.tableData[0]);
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
