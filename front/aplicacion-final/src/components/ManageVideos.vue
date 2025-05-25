<template>
  <v-container>
    <v-row align="center">
      <v-col cols="12" md="6">
        <v-text-field v-model="search" label="Buscar" outlined clearable 
        @change="searchPodcasts"
        @click:clearable="clearSearch"></v-text-field>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-data-table
          :headers="headers"
          :items="paginatedPodcasts"
          :items-per-page="pageSize"
          :page.sync="currentPage"
          :total-items="filteredPodcasts.length"
          :pageText="'{0}-{1} de {2}'"
          :items-per-page-text="'Registros por página'"
          class="elevation-1"
        >
          <template v-slot:item.thumbnail="{ item }">
            <v-img :src="`${API_URL}/${item.thumbnail}`" max-height="100" max-width="100"></v-img>
          </template>
          <template v-slot:item.actions="{ item }">
            <div class="d-flex">
              <v-icon @click="editPodcast(item)">mdi-pencil</v-icon>
              <v-icon class="ml-6" @click="deletePodcast(item.ID_podcast)">mdi-delete</v-icon>
            </div>
          </template>
        </v-data-table>
      </v-col>
    </v-row>

    <v-pagination
      v-model="currentPage"
      :length="totalPages"
      @input="changePage"
    ></v-pagination>

    <!-- Modal de edición de podcast -->
    <v-dialog v-model="editDialog" max-width="600px">
      <v-card>
        <v-card-title>Editar Podcast</v-card-title>
        <v-card-text>
          <v-form ref="editForm" @submit.prevent="saveChanges">
            <v-text-field
              v-model="editedPodcast.title"
              label="Título"
              :rules="[v => !!v || 'El título es obligatorio']"
              required
            ></v-text-field>

            <v-textarea
              v-model="editedPodcast.description"
              label="Descripción"
              :rules="[v => !!v || 'La descripción es obligatoria']"
              required
            ></v-textarea>

            <v-select
              v-model="editedPodcast.category"
              :items="categories"
              label="Categoría"
              :rules="[v => !!v || 'Debes seleccionar una categoría']"
              required
            ></v-select>

            <v-select
              v-model="editedPodcast.contentOption"
              :items="['Podcast (guardado en el equipo)', 'Enlace de YouTube']"
              label="Tipo de contenido"
              :rules="[v => !!v || 'Debes seleccionar un tipo de contenido']"
              required
            ></v-select>

            <v-file-input
              v-model="editedPodcast.thumbnailFile"
              label="Thumbnail"
              accept="image/*"
              required
              :error-messages="thumbnailError"
              @change="validateThumbnail"
            ></v-file-input>

            <v-file-input
              v-if="editedPodcast.contentOption === 'Podcast (guardado en el equipo)'"
              v-model="editedPodcast.localFile"
              label="Podcast (si lo tienes guardado en tu equipo)"
              accept="video/*"
              required
              :error-messages="localFileError"
              @change="validateLocalFile"
            ></v-file-input>

            <v-text-field
              v-if="editedPodcast.contentOption === 'Enlace de YouTube'"
              v-model="editedPodcast.youtubeLink"
              label="Enlace de YouTube"
              :rules="[v => isValidYoutubeUrl(v) || 'Ingresa un enlace de YouTube válido']"
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="saveChanges">Aceptar</v-btn>
          <v-btn @click="editDialog = false">Cancelar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/store';
import Swal from 'sweetalert2';
import { API_URL } from "@/main"; 
import podcastService from '@/services/podcastService';

const authStore = useAuthStore();

const search = ref('');
const currentPage = ref(1);
// Número de podcasts por página
const pageSize = 10;

const fetchPodcasts = async () => {
  await authStore.fetchPodcasts();
};

onMounted(fetchPodcasts);

const isValidYoutubeUrl = (url) => {
  const pattern = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/;
  return !url || pattern.test(url);
};

const filteredPodcasts = computed(() => {
  if (search.value && search.value.length > 0){
    return authStore.podcasts.filter(podcast => {
      return podcast.titulo.toLowerCase().includes(search.value.toLowerCase());
    });
  }
  return authStore.podcasts;
});

const totalPages = computed(() => {
  return Math.ceil(filteredPodcasts.value.length / pageSize);
});

const paginatedPodcasts = computed(() => {
  const startIndex = (currentPage.value - 1) * pageSize;
  return filteredPodcasts.value.slice(startIndex, startIndex + pageSize);
});

const changePage = (page) => {
  currentPage.value = page;
};

const searchPodcasts = () => {
  currentPage.value = 1;
};

const clearSearch = () => {
  search.value = '';
  searchPodcasts();
};

const openDialog = () => {
  // Lógica para abrir el diálogo de nuevo podcast
};

// Propiedades del podcast actualmente seleccionado para editar
const editedPodcast = ref({
  id: '',
  title: '',
  description: '',
  category: '',
  thumbnailFile: null,
  localFile: '',
  youtubeLink: ''
});

// Estado del modal de edición
const editDialog = ref(false);

// Lista de categorías
const categories = [
  'Medio Ambiente',
  'Ecología',
  'Sostenibilidad',
  'Conservación de la Naturaleza',
  'Cambio Climático',
  'Reciclaje',
  'Energías Renovables'
];

// Método para abrir el modal de edición y llenar los campos con los valores actuales del podcast seleccionado
const editPodcast = (podcast) => {
  console.log(podcast);
  // Llenar los campos con los valores del podcast seleccionado
  editedPodcast.value.id = podcast.ID_podcast;
  editedPodcast.value.title = podcast.titulo;
  editedPodcast.value.description = podcast.descripcion;
  editedPodcast.value.category = podcast.categoria;
  editedPodcast.value.thumbnailFile = podcast; 
  editedPodcast.value.localFile = podcast.fichero_local;
  editedPodcast.value.youtubeLink = podcast.youtube_link;

  // Abrir el modal de edición
  editDialog.value = true;
};

// Método para guardar los cambios y enviar la solicitud al servidor
const saveChanges = async () => {
  try {
    const formDataToSend = new FormData();
    formDataToSend.append('id', editedPodcast.value.id);
    formDataToSend.append('title', editedPodcast.value.title);
    formDataToSend.append('description', editedPodcast.value.description);
    formDataToSend.append('category', editedPodcast.value.category);

    if (editedPodcast.value.thumbnailFile) {
      formDataToSend.append('thumbnailFile', editedPodcast.value.thumbnailFile);
    }

    if (editedPodcast.value.localFile) {
      formDataToSend.append('localFile', editedPodcast.value.localFile);
    }

    if (editedPodcast.value.youtubeLink) {
      formDataToSend.append('youtubeLink', editedPodcast.value.youtubeLink);
    }

    const response = await podcastService.saveChanges(formDataToSend);

    if (response.status === 200) {
      await Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: 'Los cambios se guardaron correctamente.'
      });
      fetchPodcasts();
    } else {
      throw new Error('Error al guardar los cambios.');
    }
  } catch (error) {
    console.error('Error al guardar los cambios:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Hubo un problema al guardar los cambios.'
    });
  } finally {
    editDialog.value = false;
  }
};



const deletePodcast = async (id) => {
  try {
    const result = await Swal.fire({
      icon: 'warning',
      title: '¿Estás seguro?',
      text: 'Esta acción eliminará permanentemente el podcast. ¿Quieres continuar?',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      const response = await podcastService.deletePodcast(id);

      if (response.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'El podcast ha sido eliminado correctamente.'
        });
        fetchPodcasts();
      } else {
        throw new Error('Error al eliminar el podcast.');
      }
    }
  } catch (error) {
    console.error('Error al eliminar el podcast:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Hubo un problema al eliminar el podcast.'
    });
  }
};



const headers = [
  { title: 'Thumbnail', key: 'thumbnail', sortable: false },
  { title: 'Título', key: 'titulo' },
  { title: 'Acciones', key: 'actions', sortable: false },
];

const filterFunction = (items, search) => {
  if (!search || search.length == 0) return items;

  console.log(items);
  const searchTerm = search.trim().toLowerCase();

  return items.filter(item => {
    return item.titulo.toLowerCase().includes(searchTerm);
  });
};
</script>
