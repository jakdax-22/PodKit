<template>
    <v-footer color="#1E1E1E" class="text-white pa-8">
      <v-container>
        <v-row>
          <!-- Marca y descripción -->
          <v-col cols="12" md="3">
            <div class="d-flex align-center mb-3">
              <v-icon class="me-2" color="light-green-accent-3">mdi-leaf</v-icon>
              <span class="text-h6 font-weight-bold">Susurros Vegetales</span>
            </div>
            <p class="text-caption text-grey-lighten-1">La voz de la naturaleza desde 2024</p>
          </v-col>
  
          <!-- Empresa -->
          <v-col cols="12" md="3">
            <p class="text-subtitle-2 font-weight-bold">EMPRESA</p>
            <v-list-item class="text-white text-body-2" title="Nosotros" />
            <v-list-item class="text-white text-body-2" title="Contacto" />
          </v-col>
  
          <!-- Legal -->
          <v-col cols="12" md="3">
            <p class="text-subtitle-2 font-weight-bold">LEGAL</p>
            <v-list-item class="text-white text-body-2" title="Términos de uso" />
            <v-list-item class="text-white text-body-2" title="Política de privacidad" />
          </v-col>
  
          <!-- Redes Sociales -->
          <v-col cols="12" md="3">
            <p class="text-subtitle-2 font-weight-bold">SÍGUENOS</p>
            <div class="d-flex gap-3 mt-2">
              <v-btn icon class="bg-grey-darken-3" href="https://twitter.com" target="_blank">
                <v-icon color="white">mdi-twitter</v-icon>
              </v-btn>
              <v-btn icon class="bg-grey-darken-3" href="https://youtube.com" target="_blank">
                <v-icon color="white">mdi-youtube</v-icon>
              </v-btn>
              <v-btn icon class="bg-grey-darken-3" href="https://facebook.com" target="_blank">
                <v-icon color="white">mdi-facebook</v-icon>
              </v-btn>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-footer>
  </template>
  
  <script setup>
  </script>
  
  <style scoped>
  .v-footer {
    font-family: 'Roboto', sans-serif;
    border-top: 1px solid #333;
  }
  </style>
  