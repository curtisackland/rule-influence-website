<script setup>
</script>

<template>
  <nav class="navbar navbar-expand-lg ">
    <RouterLink to="/" style="padding: 10px;"><img src="/icon.png" height="64" width="64" style="border-radius: 10px"/></RouterLink>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav">
        <li><RouterLink to="/about">About</RouterLink></li>
      </ul>
    </div>
  </nav>
</template>

<style scoped>

nav {
  background-color: darkslategray;
}

nav ul li a img {
  height: 60px;
}
</style>

