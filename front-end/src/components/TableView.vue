<script>
export default {
 props: {
  tableHead: [String],
  tableData: [String]
 }
}
</script>

<template>
    <table id="myTable">
      <th v-for="tableHeader in tableHead">
        {{tableHeader}}
      </th>
      <tr v-for="row in tableData">
          <td>{{row.org_name}}</td>
          <td>{{row.y_count}}</td>
          <td>{{row.n_frdocs}}</td>
      </tr>
      </table>
</template>

<style scoped>
    #myTable {
      font-family: Arial, Helvetica, sans-serif;
      border-collapse: collapse;
      width: 100%;
      margin-top: 50px;
      margin-left: 30px;
      width: 60%;
    }
  
  #myTable td, #myTable th {
    border: 1px solid #ddd;
    padding: 8px;
  }
  
  #myTable tr:nth-child(even){background-color: #f2f2f2;}
  
  #myTable tr:hover {background-color: #ddd;}
  
  #myTable th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #898C87;
    color: white;
  }
</style>