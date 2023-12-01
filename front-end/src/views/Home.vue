<script setup>
  import TopOrgCommentersTable from "../components/TopOrgCommentersTable.vue";
  import SortBar from "../components/SortBar.vue";
  import {ref} from 'vue';

  const sortBy = ref("yCount")
  const orderBy = ref("DESC")

  function sortByChanged(valueIn) {
    sortBy.value = valueIn
    console.log(valueIn)
  }

  function orderByChanged(valueIn) {
    orderBy.value = valueIn
    console.log(valueIn)
  }
</script>


<template>
  <div>
    <header>  
      <h1 style="color:#DB9B92;" >Welcome to rule-making influence explorer!</h1>
      <SortBar @sortByChanged="sortByChanged" @orderByChanged="orderByChanged"/>
    </header>
    <TopOrgCommentersTable :sortByProp="sortBy" :sortOrderProp="orderBy" :key="[sortBy, orderBy].toString()"/>
  </div>
</template>

<style scoped>

  header {
    margin-bottom: 10px;
    margin-top: 30px;
    display: block;
    align-items: center;
  }

  header h1 {
    font-weight: bold;
    font-size: 30px;
    margin-right: 30px;
    text-align: center;
  }

  header input {
    border: none;
    background-color: #DB9B92;
    border-radius: 5px;
    padding: 10px;
    display: flex;
    width: 60%;
    margin-left: 30px;
    
    margin-bottom: 10px;
    margin-top: 25px;
  }

</style>
