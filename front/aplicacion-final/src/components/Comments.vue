<template>
  <v-container>
    <div class="comments-container" ref="commentsContainer">
      <v-row v-for="(comment, index) in comments" :key="index" class="comment-item">
        <v-col cols="12" class="d-flex align-start">
          <v-avatar size="40" class="mr-3">
            <img :src="`${api_url}/${comment.userPhoto}` || defaultPhoto" alt="User Photo" />
          </v-avatar>
          <div class="comment-content">
            <div class="comment-header">
              <span class="comment-author">{{ comment.userName }}</span>
              <v-icon v-if="comment.isAdmin" color="red" size="18" class="ml-2">mdi-star</v-icon>
            </div>
            <p class="comment-text">{{ comment.content }}</p>
            <span class="comment-date">{{ formatDate(comment.date) }}</span>
          </div>
        </v-col>
      </v-row>
    </div>

    <v-form class="mt-5" @submit.prevent="submitComment">
      <v-textarea
        v-model="newComment"
        outlined
        :disabled="loading || !isLoggedIn"
        label="Escribe tu comentario"
        rows="3"
        maxlength="500"
      ></v-textarea>
      <v-btn class="mt-2" :disabled="loading || !isLoggedIn" color="primary" @click="submitComment">Comentar</v-btn>
    </v-form>

    <v-row v-if="loading && !allCommentsLoaded" justify="center" align="center">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary" size="50" />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import commentService from "@/services/commentService";
import { API_URL } from "@/main"; 

export default {
  props: {
    idPodcast: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      api_url: API_URL,
      comments: [],
      newComment: "",
      loading: false,
      allCommentsLoaded: false,
      defaultPhoto: "path_to_default_photo.png",
      isLoggedIn: sessionStorage.getItem('userData'),
      isFetchingComments: false,
      currentPage: 1,
      pageSize: 5,
    };
  },
  methods: {
    async fetchComments() {
      if (this.isFetchingComments || this.allCommentsLoaded) return;

      this.isFetchingComments = true;

      try {
        const response = await commentService.fetchComments(this.idPodcast, this.currentPage, this.pageSize);
        const data = response.data;

        if (data.length === 0) {
          this.allCommentsLoaded = true;
        } else {
          this.comments.push(...data);
          this.currentPage++;
        }
      } catch (error) {
        console.error("Error al cargar comentarios:", error);
      } finally {
        this.isFetchingComments = false;
      }
    },

    async submitComment() {
      if (!this.newComment.trim()) return;

      this.loading = true;

      try {
        await commentService.submitComment(this.idPodcast, this.newComment, JSON.parse(this.isLoggedIn).id);

        this.newComment = "";
        this.comments = [];
        this.currentPage = 1;
        this.allCommentsLoaded = false;
        this.fetchComments();
      } catch (error) {
        console.error("Error al añadir el comentario:", error);
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString("es-ES", options);
    },
    handleScroll() {
      const container = this.$refs.commentsContainer;
      if (
        container.scrollTop + container.clientHeight >= container.scrollHeight - 10
      ) {
        this.fetchComments();
      }
    },
  },
  mounted() {
    this.fetchComments();
    const container = this.$refs.commentsContainer;
    if (container) {
      container.addEventListener("scroll", this.handleScroll);
    }
  },
  beforeDestroy() {
    const container = this.$refs.commentsContainer;
    if (container) {
      container.removeEventListener("scroll", this.handleScroll);
    }
  },
};
</script>

<style scoped>
.comments-container {
  max-height: 400px;
  overflow-y: auto;
  padding: 10px;
  background-color: #ffffff;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.comment-item {
  margin-bottom: 15px;
  padding: 10px;
  background-color: #f7f7f7;
  border-radius: 5px;
  border: 1px solid #e0e0e0;
}

.comment-header {
  display: flex;
  align-items: center;
  font-weight: bold;
  font-size: 14px;
  color: #333;
}

.comment-text {
  margin: 5px 0;
  font-size: 13px;
  color: #444;
}

.comment-date {
  font-size: 11px;
  color: #888;
}

.v-textarea {
  background-color: #f9f9f9 !important;
  color: #333 !important;
  border-radius: 5px;
}

.v-btn {
  background-color: #007bff !important;
  color: #ffffff !important;
}

.v-btn:hover {
  background-color: #0056b3 !important;
}
</style>
