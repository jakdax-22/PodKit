<template>
  <v-main class="bg-teal accent-2 fill-screen">
    <v-container>
      <v-row v-if="podcasts.length > 0">
        <!-- Agrupación por Categorías -->
        <template v-for="(categoryGroup, category) in groupedPodcasts" :key="category">
          <v-col cols="12" class="mt-4">
            <v-divider></v-divider>
            <div class="d-flex align-center justify-space-between mt-3">
              <!-- Título de la categoría -->
              <h2 class="text-h5 font-weight-bold">{{ category }}</h2>
              <!-- Contenedor de lupa y búsqueda -->
              <div class="d-flex align-center">
                <!-- Campo de búsqueda -->
                <v-text-field
                  v-if="searchVisible[category]"
                  v-model="searchQuery[category]"
                  class="search-input"
                  label="Buscar por título"
                  single-line
                  autofocus
                  @input="filterPodcasts(category)"
                ></v-text-field>
                <!-- Icono de lupa -->
                <v-icon
                  class="search-icon"
                  @click="toggleSearch(category)"
                >
                  mdi-magnify
                </v-icon>
              </div>
            </div>
          </v-col>

          <!-- Mostrar los primeros 6 podcasts o todos según el estado -->
          <v-col
            v-for="(podcast, index) in (expandedCategories[category] ? filteredPodcasts[category] : filteredPodcasts[category].slice(0, 6))"
            :key="podcast.ID_podcast"
            cols="12"
            sm="6"
            md="4"
          >
            <v-hover v-slot="{ isHovering, props }">
              <v-card
                class="elevation-10 rounded-xl podcast"
                v-bind="props"
                @click="$router.push(`/podcast/${podcast.ID_podcast}`)"
              >
                <!-- Thumbnail -->
                <div class="thumbnail-container">
                  <img
                    :src="`${api_url}/${podcast.thumbnail}`"
                    alt="Thumbnail"
                    class="thumbnail"
                  />
                </div>
                
                <!-- Título visible siempre -->
                <div class="podcast-title text-center">
                  <h3 class="text-h6">{{ podcast.titulo }}</h3>
                </div>

                <!-- Datos visibles en hover -->
                <div v-if="isHovering" class="preview-overlay">
                  <div class="text-center text-white">
                    <p>{{ podcast.descripcion.substring(0,50) }}</p>
                    <small>{{ podcast.fecha_subida }}</small>
                  </div>
                </div>
              </v-card>
            </v-hover>
          </v-col>

          <!-- Botón redondo para mostrar más/menos -->
          <v-col cols="12" v-if="filteredPodcasts[category].length > 6 && !expandedCategories[category]" class="text-center mt-3">
            <v-btn class="rounded-button" @click="toggleCategory(category)">
              {{ expandedCategories[category] ? '-' : '+' }}
            </v-btn>
          </v-col>
        </template>
      </v-row>

      <!-- Mensaje bonito cuando no hay podcasts -->
      <v-row v-else class="empty-state">
        <v-col cols="12" class="text-center">
          <v-icon size="80" color="white">mdi-podcast</v-icon>
          <h2 class="text-h4 font-weight-bold text-white mt-3">¡No hay podcasts todavía!</h2>
          <p class="text-white text-subtitle-1">
            Cuando haya podcasts disponibles, aparecerán aquí.
          </p>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>

<script>
import { useAuthStore } from "@/store";
import { API_URL } from "@/main";

export default {
  data() {
    return {
      podcasts: [], // Todos los podcasts
      expandedCategories: {}, // Estado de expansión por categoría
      searchVisible: {}, // Estado de visibilidad de búsqueda por categoría
      searchQuery: {}, // Términos de búsqueda por categoría
      filteredPodcasts: {}, // Podcasts filtrados por categoría
      api_url:API_URL
    };
  },
  computed: {
    // Agrupa los podcasts por categoría
    groupedPodcasts() {
      return this.podcasts.reduce((groups, podcast) => {
        const category = podcast.categoria;
        if (!groups[category]) {
          groups[category] = [];
        }
        groups[category].push(podcast);
        return groups;
      }, {});
    },
  },
  created() {
    this.loadPodcasts(); // Carga los podcasts al inicializar el componente
  },
  methods: {
    async loadPodcasts() {
      try {
        const authStore = useAuthStore();

        // Obtener podcasts desde el store
        await authStore.fetchPodcasts();

        // Ordenar por fecha de subida más reciente
        this.podcasts = authStore.podcasts.sort((a, b) => {
          return new Date(b.fecha_subida) - new Date(a.fecha_subida);
        });

        // Inicializar los podcasts filtrados para cada categoría
        this.filteredPodcasts = Object.keys(this.groupedPodcasts).reduce((acc, category) => {
          acc[category] = this.groupedPodcasts[category];
          return acc;
        }, {});

        console.log("Podcasts ordenados y obtenidos:", this.podcasts);
      } catch (error) {
        console.error("Error al cargar podcasts:", error);
      }
    },
    toggleCategory(category) {
      // Cambiar el estado de expansión de la categoría
      if (this.expandedCategories[category]) {
        delete this.expandedCategories[category];
      } else {
        this.expandedCategories[category] = true;
      }
    },
    toggleSearch(category) {
      // Cambiar el estado de visibilidad de la búsqueda por categoría
      if (this.searchVisible[category]) {
        this.searchQuery[category] = ''; // Vaciar el campo de búsqueda
        this.searchVisible[category] = false; // Ocultar el campo de búsqueda
        this.filteredPodcasts[category] = this.groupedPodcasts[category]; // Restaurar todos los podcasts de la categoría
      } else {
        this.searchVisible[category] = true; // Mostrar el campo de búsqueda
        this.$nextTick(() => { // Foco automático
          this.$refs[`searchInput-${category}`][0].focus();
        });
      }
    },
    filterPodcasts(category) {
      // Filtrar los podcasts de la categoría seleccionada
      if (this.searchQuery[category]) {
        this.filteredPodcasts[category] = this.groupedPodcasts[category].filter(podcast =>
          podcast.titulo.toLowerCase().includes(this.searchQuery[category].toLowerCase())
        );
      } else {
        this.filteredPodcasts[category] = this.groupedPodcasts[category]; // Restaurar la lista completa de la categoría
      }
    },
  },
};
</script>

<style>
/* Asegura que el fondo cubra toda la pantalla */
.fill-screen {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Mensaje bonito cuando no hay podcasts */
.empty-state {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

/* Estilo para la superposición al hacer hover */
.preview-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 10px;
}

.preview-overlay h3 {
  margin: 0;
  font-size: 18px;
  font-weight: bold;
}

.preview-overlay p {
  margin: 5px 0;
  font-size: 14px;
}

.preview-overlay small {
  font-size: 12px;
  color: #d1d1d1;
}

/* Contenedor para la miniatura */
.thumbnail-container {
  position: relative;
  width: 100%;
  height: 180px; /* Altura fija para mantener consistencia */
  overflow: hidden;
}

/* Miniatura ajustada al contenedor */
.thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ajusta la imagen al tamaño del contenedor */
}

.podcast {
  cursor: pointer;
  overflow: hidden;
}

h2 {
  color: #ffffff;
  text-transform: capitalize;
}

.v-divider {
  background-color: #ffffff;
}

/* Botón redondo */
.rounded-button {
  background-color: #9e9e9e; /* Color gris */
  color: #ffffff; /* Texto blanco */
  border-radius: 50%; /* Redondear el botón */
  width: 40px; /* Anchura */
  height: 40px; /* Altura */
  font-size: 24px; /* Tamaño del texto */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0; /* Eliminar relleno adicional */
  min-width: unset; /* Evitar que el botón se expanda automáticamente */
}

.rounded-button:hover {
  background-color: #757575; /* Color gris oscuro al hacer hover */
}

/* Estilo del icono de la lupa */
.search-icon {
  cursor: pointer;
  color: white;
}

/* Estilo del input de búsqueda */
.search-input {
  width: 200px;
  margin-right: 10px;
}
</style>
