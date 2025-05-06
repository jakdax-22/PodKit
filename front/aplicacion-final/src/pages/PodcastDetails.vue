<template>
  <v-main>
    <v-container>
      <!-- Spinner de carga -->
      <v-row v-if="loading" justify="center" align="center" class="mt-5">
        <v-col cols="12" class="text-center">
          <v-progress-circular indeterminate color="primary" size="70" />
        </v-col>
      </v-row>

      <!-- Mensaje de error -->
      <v-row v-if="error" justify="center" align="center" class="mt-5">
        <v-col cols="12" class="text-center">
          <h2 class="error-message">{{ errorMessage }}</h2>
        </v-col>
      </v-row>

      <!-- Video Player -->
      <v-row v-if="!loading && !error">
        <v-col cols="12">
          <div v-if="isYouTubeVideo">
            <iframe
              :src="videoUrl"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
              class="video-player"
            ></iframe>
          </div>
          <div v-else>
            <video :src="videoUrl" controls class="video-player" />
          </div>
        </v-col>
      </v-row>

      <!-- Video Info -->
      <v-row v-if="!loading && !error" class="mt-4">
        <v-col cols="12">
          <div class="video-info">
            <h1 class="video-title">{{ podcast.titulo }}</h1>
            <v-btn icon @click="copyLink">
              <v-icon>mdi-link-variant</v-icon>
            </v-btn>
          </div>
          <p class="video-description">{{ podcast.descripcion }}</p>
        </v-col>
      </v-row>

      <!-- Comment Section -->
      <v-row v-if="!loading && !error" class="mt-4">
        <!-- Aquí le pasamos el idPodcast como prop al componente Comments -->
        <Comments :idPodcast="podcastId" />
      </v-row>
    </v-container>
  </v-main>
</template>


<script>
import podcastService from "@/services/podcastService";
import { API_URL } from "@/main"; 

export default {
  data() {
    return {
      podcast: {},
      videoUrl: "",
      isYouTubeVideo: false,
      loading: true,
      error: false,
      errorMessage: "Error al cargar el podcast.",
      podcastId: null, // Guardaremos el ID del podcast
    };
  },

  created() {
    this.podcastId = this.$route.params.id;
    this.fetchPodcastDetails();
  },

  methods: {
    async fetchPodcastDetails() {
      try {
        const response = await podcastService.fetchPodcastDetails(this.podcastId);

        if (!response.data || !response.data.archivo) {
          throw new Error("Podcast no encontrado.");
        }

        this.podcast = response.data;
        this.processVideoSource(this.podcast.archivo);
      } catch (error) {
        console.error("Error al obtener el podcast:", error);
        this.error = true;
        this.errorMessage =
          error.response?.data?.message || "Ocurrió un error inesperado.";
      } finally {
        this.loading = false;
      }
    },

    processVideoSource(url) {
      if (url.includes("youtube.com") || url.includes("youtu.be")) {
        this.isYouTubeVideo = true;
        const videoId = this.extractYouTubeId(url);
        this.videoUrl = videoId ? `https://www.youtube.com/embed/${videoId}` : "";
      } else {
        console.log(this.videoUrl);
        this.isYouTubeVideo = false;
        this.videoUrl = `${API_URL}/${url}`;
      }
    },

    extractYouTubeId(url) {
      const match = url.match(
        /(?:youtube\.com\/.*(?:v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
      );
      return match ? match[1] : null;
    },

    likeVideo() {
      console.log("Video liked!");
    },

    copyLink() {
      const url = window.location.href;
      navigator.clipboard.writeText(url);
      console.log("Enlace copiado:", url);
    },
  },

  mounted() {
    this.$nextTick(() => {
      const container = this.$refs.commentsContainer;
      if (container) {
        container.addEventListener("scroll", () => {
          if (
            container.scrollTop + container.clientHeight >=
            container.scrollHeight
          ) {
            this.loadComments();
          }
        });
      } else {
        console.error("Contenedor de comentarios no encontrado.");
      }
    });
  },
};
</script>


<style>
.video-player {
  width: 100%;
  height: 400px;
  background-color: black;
}

.video-title {
  font-size: 24px;
  font-weight: bold;
}

.video-info {
  display: flex;
  align-items: center;
  gap: 1%;
}

.video-description {
  margin-top: 10px;
}

.comments-container {
  max-height: 400px;
  overflow-y: auto;
}

.error-message {
  font-size: 24px;
  font-weight: bold;
  color: red;
}
</style>
