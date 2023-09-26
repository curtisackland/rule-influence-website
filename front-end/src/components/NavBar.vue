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
  align-content: center;

  margin: auto;
  padding: 10px 20px;
  float:left;
  height: 40px;
  border-right: 1px black solid;
  display: inline-block;
}

nav.topnav ul li a {
  text-align: center;

  vertical-align: center;
}

nav.topnav ul li a img {
  height: 100%;
  object-fit: contain;
}

a {
  font-size: 20pt;
}
</style>

