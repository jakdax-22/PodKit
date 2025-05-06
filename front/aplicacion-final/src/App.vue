<template>
  <v-app >
    <NavBar/>
    <v-main>
      <router-view />
    </v-main>
    <Footer/>
  </v-app>
</template>

<script setup>
  import { useRoute } from 'vue-router';
  import NavBar from './components/NavBar.vue';
  import router from './router';
  import Footer from './components/Footer.vue';

  const route = useRoute();
  //
</script>
