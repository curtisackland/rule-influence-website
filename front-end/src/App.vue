<script setup>
import NavBar from "./components/NavBar.vue";

</script>

<template>
  <body>
    <NavBar/>
    <RouterView/>
  </body>
</template>

<style scoped>
html {
    background-color: #F4EFEC;
    height: 100%;
  }
</style>
