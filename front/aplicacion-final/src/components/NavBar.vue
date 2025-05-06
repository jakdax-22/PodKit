<template>
  <!-- Barra de la aplicación -->
  <v-app-bar color="teal-darken-4" class="d-flex justify-space-between">
    <!-- Logo y nombre de la página alineado a la izquierda -->
    <v-toolbar-title class="page-title text-left ml-4">
      <span class="titulo" @click="goToIndex">
        <v-icon class="mr-2">mdi-leaf</v-icon> <!-- Icono de la web -->
        Susurros Vegetales
      </span>
    </v-toolbar-title>
    
    <!-- Botón de inicio de sesión o icono de usuario alineado a la derecha -->
    <div class="mr-4">
      <ProfileIcon v-if="isLoggedIn"/>
      <template v-else>
        <v-btn to="/login">Inicio de Sesión</v-btn>
      </template>
    </div>
  </v-app-bar>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store'

const router = useRouter()
const authStore = useAuthStore() 

const isLoggedIn = computed(() => authStore.isLoggedIn)

const goToIndex = () => {
  router.push({ path: '/' }) 
}
</script>

<style scoped>
.page-title {
  font-family: 'Roboto', sans-serif;
  font-size: 24px;
  padding: 8px 0;
}

.titulo {
  color: white;
  cursor: pointer;
}
</style>
