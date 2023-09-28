
<template>
  <h1>{{$route.params.orgName}}</h1>
  <h2>Most Important Statistic</h2>
  <svg width="80%" height="100">
    <rect width="1000" height="100" fill="gray"/>
  </svg>
  <h2>Rule Influence Table</h2>

</template>

<script setup>
import TopOrgCommentersTable from "../components/TopOrgCommentersTable.vue";
</script>