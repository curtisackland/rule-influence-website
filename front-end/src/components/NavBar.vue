<script setup>
  import {RouterLink} from "vue-router"
</script>

<template>
  <!-- <nav class="navbar navbar-expand-lg" style="background-color: #bdd4f9;">
    <RouterLink to="/" style="padding: 10px;"><img src="/icon.png" height="64" width="64" style="border-radius: 10px"/></RouterLink>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav">
        <li><RouterLink to="/">Home</RouterLink></li>
        <li><RouterLink to="/about">About</RouterLink></li>
      </ul>
    </div>
  </nav> -->
  <nav class="navbar navbar-expand" style="background-color: #283C3C"> 
    <div class="collapse navbar-collapse" id="myNav">
      <ul class="navbar-nav mr-auto" id="navigation">
        <li class="nav-item active">
          <a class="nav-link" active-class="active" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" active-class="active" href="/explore">Explore Data</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" active-class="active" href="/about">About</a>
        </li>
      </ul>
      <div class="image-container">
        <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fwww.wemakescholars.com%2Fadmin%2Fuploads%2Fproviders%2F0CVU1PN3tZla9Z6d1rhIPEO1Mx5roQla.png&f=1&nofb=1&ipt=25f07c18225a27dbf7523bc0a9b8698b2346f64ff1df094429d1966f49d06029&ipo=images" alt="">
      </div>
    </div>
  </nav>
</template>

<style scoped>
  a {
    color: #EBDCD2!important;
  }
  
  nav {
    background-color: darkslategray;
  }

  .image-container {
    background-color: #EBDCD2;
    margin-left: auto;
    width: 160px;
    height: 60px;
    display:flex;
    justify-content:space-between;
    align-items: center;
    border: 1px black solid;
  }

  .image-container img {
    width: 100%;
    height: 100px;
    margin-top: 25px;
    margin-left: auto;
    margin-right: auto;
  }

  a:active {
    font-weight: 900;
}
</style>

