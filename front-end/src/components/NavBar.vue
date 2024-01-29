<template>
  <v-app-bar
      color="teal-darken-4"
      image="/ivey-banner.jpeg"
      class="min-width"
  >
    <!--Gradient effect for the Ivey Banner -->
    <template v-slot:image>
        <v-img
            gradient="to top right, rgba(40,60,60,.9), rgba(40,60,60,.5)"
            class="custom-gradient"
        ></v-img>
    </template>

    <!-- Hamburger menu icon that goes before the title when the screen is too small -->
    <template v-slot:prepend>
      <v-app-bar-nav-icon variant="text" @click.stop="sidebar = !sidebar" class="enable-side-menu"></v-app-bar-nav-icon>
    </template>

    <v-app-bar-title>
      <RouterLink to="/" class="nav-link title-text">{{ title }}</RouterLink>
    </v-app-bar-title>

    <!-- Navigation items that link to different pages -->
    <v-row class="w-25 display-nav-items">
      <RouterLink :to="item.path" class="nav-link p-2" active-class="active" v-for="item in menuItems">
        <v-btn class="nav-link" active-class="active">{{ item.title }}</v-btn>
      </RouterLink>
    </v-row>

    <!-- Ivey Logo -->
    <div class="d-flex logo-size align-content-center">
      <v-img
          src="/icon.png"
      ></v-img>
    </div>
  </v-app-bar>

  <!-- Left side menu navigation that pops out when clicking the hamburger menu (sidebar=true) when the screen is too small -->
  <v-navigation-drawer v-model="sidebar" class="enable-side-menu" temporary="" color="rie-primary-color">
    <v-list>
      <v-list-item
          v-for="item in menuItems"
          :key="item.title"
          :to="item.path">
        <v-list-item-title>{{ item.title }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>

</template>

<script>
export default {
  name: "Navbar",
  data() {
    return {
      sidebar: false,
      title: 'Rulemaking Influence Explorer',
      menuItems: [
        { title: 'Organizations', path: '/' },
        { title: 'About', path: '/about'},
        { title: 'FR Docs', path: '/frdocs'},
        { title: 'Comments', path: '/comments'},
        { title: 'Responses', path: '/responses'},
      ],
    }
  },
}
</script>

<style scoped>
  @media (max-width: 950px) {
    .display-nav-items {
      display: none !important;
    }
  }

  @media (min-width: 950px) {
    .enable-side-menu {
      display: none !important;
    }
  }

  .custom-gradient {
    position: relative;
  }

  .custom-gradient::before {
    content: "";
    position: absolute;
    top: 0;
    left: 50%;
    width: 50%;
    height: 100%;
    background: linear-gradient(to left, rgba(235, 220, 210,.3), rgba(235, 220, 210,0));
  }

  .min-width {
    min-width: 300px!important;
  }

  .logo-size {
    height: 80%;
    width: 5%;
    min-width: 60px;
  }

  .title-text {
    font-weight: 500;
  }

  a.active {
    font-weight: 700;
  }
</style>

