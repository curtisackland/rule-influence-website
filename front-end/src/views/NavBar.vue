<script setup>
</script>

<template>
  <nav class="topnav">
    <ul>
      <li><RouterLink to="/"><img src="https://media.tenor.com/Nfct9RreQfUAAAAd/dog-meme.gif" alt="logo"/></RouterLink></li>
      <li><RouterLink to="/">Home</RouterLink></li>
    </ul>
  </nav>
</template>

<style scoped>
img {
  height: 100%;
  object-fit: contain;
}
nav.topnav {
  display: block;
  position: fixed;
  background-color: darkslategray;
  width: 100%;
}
nav.topnav ul {
  padding: 0;
  margin: 0;
  list-style-type: none;
}

nav.topnav ul li {
  text-align: center;
  align-content: center;
  margin: auto;
  padding: 20px;
  float:left;

  border: 2px black;
  display: inline-block;
}
</style>

