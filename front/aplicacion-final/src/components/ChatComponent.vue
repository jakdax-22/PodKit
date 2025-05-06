<template>
  <v-dialog v-model="chatDialog" max-width="600px">
    <v-card>
      <v-card-title>Chat con {{ chatUser?.nombre_usuario }}</v-card-title>
      <v-card-text>
        <v-list dense>
          <v-list-item v-for="message in messages" :key="message.id">
            <v-list-item-content>
              <v-list-item-title>
                <strong>{{ message.id_emisor === selfId ? 'Yo' : chatUser?.nombre_usuario }}:</strong> {{ message.mensaje }}
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-text-field v-model="newMessage" label="Escribe un mensaje" @keyup.enter="sendMessage"></v-text-field>
      </v-card-text>
      <v-card-actions>
        <v-btn color="primary" @click="closeChatModal">Cerrar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import messageService from '@/services/messageService';

export default {
  setup() {
    const chatDialog = ref(false);
    const chatUser = ref(null);
    const messages = ref([]);
    const newMessage = ref('');
    const selfId = JSON.parse(sessionStorage.getItem('userData')).id;
    let refreshInterval = null;

    const fetchMessages = async () => {
      try {
        const response = await messageService.fetchMessages(selfId);
        messages.value = response.data;
      } catch (error) {
        console.error('Error al obtener mensajes:', error);
      }
    };

    const sendMessage = async () => {
      if (newMessage.value.trim() === '') return;
      try {
        const response = await messageService.sendMessage(selfId, chatUser.value.id, newMessage.value);
        if (response.data.success) {
          messages.value.push({
            id_emisor: selfId,
            id_receptor: chatUser.value.id,
            mensaje: newMessage.value,
            fecha: new Date().toISOString()
          });
          newMessage.value = '';
        } else {
          console.error('Error al enviar el mensaje:', response.data.message);
        }
      } catch (error) {
        console.error('Error:', error);
      }
    };

    const openChatModal = (user) => {
      chatUser.value = user;
      chatDialog.value = true;
    };

    const closeChatModal = () => {
      chatDialog.value = false;
      chatUser.value = null;
      messages.value = [];
    };

    // Iniciar la actualización automática cada 5 segundos cuando el componente se monta
    onMounted(() => {
      fetchMessages();
      refreshInterval = setInterval(fetchMessages, 5000);
    });

    // Limpiar el intervalo cuando el componente se destruye para evitar fugas de memoria
    onUnmounted(() => {
      clearInterval(refreshInterval);
    });

    return {
      chatDialog,
      chatUser,
      messages,
      newMessage,
      selfId,
      openChatModal,
      closeChatModal,
      sendMessage
    };
  }
};
</script>
