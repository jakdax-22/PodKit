<template>
    <v-form @submit.prevent="validateForm" ref="podcastForm" v-model="isValid">
        <!-- Título -->
        <v-text-field
            v-model="formData.title"
            label="Título"
            :rules="[v => !!v || 'El título es obligatorio']"
            required
        ></v-text-field>

        <!-- Descripción -->
        <v-textarea
            v-model="formData.description"
            label="Descripción"
            :rules="[v => !!v || 'La descripción es obligatoria']"
            required
        ></v-textarea>

        <!-- Categoría -->
        <v-select
            v-model="category"
            :items="categories"
            label="Categoría"
            :rules="[v => !!v || 'Debes seleccionar una categoría']"
            required
        ></v-select>

        <!-- Tipo de contenido -->
        <v-select
            v-model="contentOption"
            :items="contentOptions"
            label="Tipo de contenido"
            :rules="[v => !!v || 'Debes seleccionar un tipo de contenido']"
            required
        ></v-select>

        <!-- Thumbnail -->
        <v-file-input
            v-model="thumbnailFile"
            label="Thumbnail"
            accept="image/*"
            required
            :error-messages="thumbnailError"
            @change="validateThumbnail"
        ></v-file-input>

        <!-- Archivo de podcast local (si aplica) -->
        <v-file-input
            v-if="contentOption === 'Podcast (guardado en el equipo)'"
            v-model="formData.localFile"
            label="Podcast (si lo tienes guardado en tu equipo)"
            accept="video/*"
            required
            :error-messages="localFileError"
            @change="validateLocalFile"
        ></v-file-input>

        <!-- Enlace de YouTube (si aplica) -->
        <v-text-field
            v-if="contentOption === 'Enlace de YouTube'"
            v-model="formData.youtubeLink"
            label="Enlace de YouTube"
            :rules="[v => isValidYoutubeUrl(v) || 'Ingresa un enlace de YouTube válido']"
        ></v-text-field>

        <!-- Botón de enviar -->
        <v-btn type="submit" color="#00897B">Subir</v-btn>
    </v-form>
</template>

<script setup>
import { ref } from 'vue';
import Swal from 'sweetalert2';
import podcastService from '@/services/podcastService';

const isValid = ref(false);

const categories = [
  'Medio Ambiente',
  'Ecología',
  'Sostenibilidad',
  'Conservación de la Naturaleza',
  'Cambio Climático',
  'Reciclaje',
  'Energías Renovables'
];

const formData = ref({
  title: '',
  description: '',
  localFile: null,
  youtubeLink: ''
});

const category = ref(categories[0]);

const contentOptions = ['Podcast (guardado en el equipo)', 'Enlace de YouTube'];
const contentOption = ref(contentOptions[0]);

const thumbnailFile = ref(null);

const thumbnailError = ref('');
const localFileError = ref('');

const fileSizeLimit = (file) => {
  if (!file) return true;
  const maxSize = 1024 * 1024 * 1024; // 1 GB
  return file.size <= maxSize || 'El archivo debe ser menor de 1 GB';
};

const fileTypeLimit = (file) => {
  if (!file) return true;
  const acceptedTypes = ['video/mp4', 'video/mkv', 'video/webm'];
  return acceptedTypes.includes(file.type) || 'Solo se permiten archivos de video';
};

const validateThumbnail = () => {
  thumbnailError.value = thumbnailFile.value ? '' : 'Debes subir una imagen.';
};

const validateLocalFile = () => {
  if (contentOption.value === 'Podcast (guardado en el equipo)') {
    if (!formData.value.localFile) {
      localFileError.value = 'Debes subir un archivo de video.';
    } else {
      const file = formData.value.localFile;
      localFileError.value = fileSizeLimit(file) === true ? '' : fileSizeLimit(file);
      if (!localFileError.value) {
        localFileError.value = fileTypeLimit(file) === true ? '' : fileTypeLimit(file);
      }
    }
  } else {
    localFileError.value = '';
  }
};

const validateForm = async () => {
  validateThumbnail();
  validateLocalFile();

  const form = await document.querySelector('form');
  const isValid = form.checkValidity();

  if (isValid && !thumbnailError.value && !localFileError.value) {
    insertPodcast();
  }
};

const isValidYoutubeUrl = (url) => {
  const pattern = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/;
  return !url || pattern.test(url);
};

const insertPodcast = async () => {
  const formDataToSend = new FormData();
  formDataToSend.append('title', formData.value.title);
  formDataToSend.append('description', formData.value.description);
  formDataToSend.append('category', category.value);

  if (thumbnailFile.value) {
    formDataToSend.append('thumbnailFile', thumbnailFile.value);
  }

  if (formData.value.localFile) {
    formDataToSend.append('localFile', formData.value.localFile);
  }

  if (formData.value.youtubeLink) {
    formDataToSend.append('youtubeLink', formData.value.youtubeLink);
  }

  try {
    const response = await podcastService.uploadPodcast(formDataToSend);
    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: 'Podcast insertado correctamente.',
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Hubo un problema al insertar el podcast.',
      });
    }
  } catch (error) {
    console.error('Error al realizar la solicitud:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Hubo un problema al realizar la solicitud.',
    });
  }
};
</script>
