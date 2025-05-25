<template>
    <div class="text-center">
      <v-menu v-model="menu" :close-on-content-click="false" location="end">
        <template v-slot:activator="{ props }">
          <v-btn color="indigo" v-bind="props">
            <v-icon class="mr-2" color="white" size="32">mdi-account</v-icon>
          </v-btn>
        </template>
  
        <v-card min-width="300">
          <v-list v-if="!userData.avatar ||userData.avatar == '0'">
            <v-list-item prepend-avatar="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" :subtitle="userData.role == 1 ? 'Administrador' : 'Usuario'" :title="userData.username">
            </v-list-item>
          </v-list>
          <v-list v-else>
            <v-list-item :prepend-avatar="`${API_URL}/${userData.avatar}`" :subtitle="userData.role == 1 ? 'Administrador' : 'Usuario'" :title="userData.username">
            </v-list-item>
          </v-list>
  
          <v-divider></v-divider>
  
          <v-list>
            <router-link v-if="userData.role === 1" to="/admin" class="link-unstyled">
              <v-list-item>
                <div class="d-flex" @click="menu=false">
                    <v-list-item-icon class="mr-6">
                        <v-icon slot="prependIcon" color="primary">mdi-account-supervisor</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>Administración</v-list-item-title>
                    </v-list-item-content>
                </div>
              </v-list-item>
            </router-link>
            <router-link to="/profile" class="link-unstyled">
              <v-list-item>
                <div class="d-flex" @click="menu=false">
                    <v-list-item-icon class="mr-6">
                        <v-icon color="primary">mdi-account-circle</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>Mi cuenta</v-list-item-title>
                    </v-list-item-content>
                </div>
              </v-list-item>
            </router-link>
            <v-list-item @click="deleteSession">
                <div class="d-flex" @click="menu=false">
                    <v-list-item-icon class="mr-6">
                        <v-icon color="primary">mdi-logout</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content>
                        <v-list-item-title>Cerrar sesión</v-list-item-title>
                    </v-list-item-content>
                </div>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>
    </div>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/store' // Importa el store de Pinia
  import { ref,computed } from 'vue';
  import { API_URL } from "@/main"; 

  const authStore = useAuthStore() // Obtiene el store de Pinia
  const router = useRouter()

  const menu = ref(false)
  const userData = computed(() => authStore.userData)
  
  const deleteSession = () => {
    authStore.logout() // Llama al método de logout del store de Pinia
    router.push({ path: '/' }) // Redirige a la página principal
  }
  </script>
  
  <style scoped>
  .link-unstyled {
    text-decoration: none;
    color: inherit;
    cursor: pointer;
  }
  </style>
