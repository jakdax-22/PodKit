<template>
    <!-- Contenedor principal -->
    <v-container fluid>
      <!-- Fila principal -->
      <v-row>
        <!-- Panel de pestañas -->
        <v-col cols="2" class="left-panel">
          <v-card class="left-panel-card">
            <v-list nav dense>
              <!-- Lista de pestañas -->
              <v-list-item v-for="(item, index) in items" :key="index" @click="selected = index">
                <div class="d-flex align-center">
                    <!-- Icono de la pestaña -->
                    <v-icon class="mr-8">{{ item.icon }}</v-icon>
                    <!-- Título de la pestaña -->
                    <v-list-item-title>{{ item.title }}</v-list-item-title>
                </div>
              </v-list-item>
            </v-list>
          </v-card>
        </v-col>
        <!-- Contenido del panel seleccionado -->
        <v-col cols="10">
          <v-card>
            <!-- Título del panel seleccionado -->
            <v-card-title>{{ items[selected].title }}</v-card-title>
            <v-card-text>
              <!-- Contenido específico del panel seleccionado -->
              <component :is="selectedComponent"></component>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>
  
  <!--Estructura de Composition API-->
  <script setup>
  import { ref, computed } from 'vue';
  
  // Importar los componentes específicos para mostrar en cada pestaña
  import UploadPodcast from '@/components/UploadPodcast.vue';
  import AssignRoles from '@/components/AssignRoles.vue';
  import ManageVideos from '@/components/ManageVideos.vue';
  
  // Definir el estado de la pestaña seleccionada
  const selected = ref(0);
  
  // Definir la lista de elementos de pestaña
  const items = ref([
    { title: 'Subir Podcast', icon: 'mdi-upload', component: UploadPodcast },
    { title: 'Asignar Roles', icon: 'mdi-account-group', component: AssignRoles },
    { title: 'Mis Podcasts', icon: 'mdi-video', component: ManageVideos },
  ]);
  
  // Obtener el componente seleccionado
  const selectedComponent = computed(() => items.value[selected.value].component);
  </script>
  
  <style scoped>
  .left-panel {
    height: 100%;
  }
  
  .left-panel-card {
    background-color: #f5f5f5;
  }
  
  </style>
  