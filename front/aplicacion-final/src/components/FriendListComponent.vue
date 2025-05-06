<template>
    <div class="friend-list">
      <v-list dense>
        <v-list-item
          v-for="(friend, index) in friends"
          :key="index"
          @click="selectFriend(friend)"
        >
          <v-list-item-avatar class="rounded-avatar">
            <v-img :src="friend.avatar" alt="Avatar"></v-img>
          </v-list-item-avatar>
  
          <v-list-item-content>
            <v-list-item-title>{{ friend.name }}</v-list-item-title>
            <v-list-item-subtitle>{{ friend.status }}</v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const friends = ref([
    { name: 'Amigo 1', avatar: 'https://via.placeholder.com/100', status: 'En línea' },
    { name: 'Amigo 2', avatar: 'https://via.placeholder.com/100', status: 'Fuera de línea' },
    { name: 'Amigo 3', avatar: 'https://via.placeholder.com/100', status: 'En línea' },
    { name: 'Amigo 4', avatar: 'https://via.placeholder.com/100', status: 'En línea' },
  ]);
  
  const selectFriend = (friend) => {
    $emit('friendSelected', friend);
  };
  </script>
  
  <style scoped>
  .friend-list {
    overflow-y: auto;
    max-height: 580px;
  }
  
  .friend-list .rounded-avatar img {
    border-radius: 50%;
  }
  </style>
  