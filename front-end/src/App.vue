<script setup>
import NavBar from "./components/NavBar.vue";
</script>

<template>
  <v-app>
    <!--Navigation bar seen at the top of the page-->
    <NavBar />
    <!-- v-main is needed to tell the nav bar not to cover our main content-->
    <v-main>
      <RouterView/>
    </v-main>
  </v-app>
</template>