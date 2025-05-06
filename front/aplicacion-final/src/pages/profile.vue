<template>
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card class="mb-5">
            <v-card-title>
              <v-avatar size="64" class="mr-3">
                <v-img v-if="!userData.foto_perfil || userData.foto_perfil == '0'" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png"></v-img>
                <v-img v-else :src="`${API_URL}/${userData.foto_perfil}`"></v-img>
              </v-avatar>
              <span class="headline">{{ userData.nombre_usuario }}</span>
            </v-card-title>
            <v-card-text>
              <v-btn color="primary" @click="editProfile">Editar Perfil</v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
  
      <v-row>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-title>Enviar Solicitud de Amistad</v-card-title>
            <v-card-text>
              <v-text-field
                v-model="searchQuery"
                label="Buscar usuarios"
                prepend-icon="mdi-magnify"
                @input="searchUsers"
                outlined
                clearable
                @click:clearable="clearSearch"
              ></v-text-field>
              <v-list dense>
                <v-list-item v-for="user in filteredUsers.slice(0, 5)" :key="user.ID_usuario">
                    <v-row align="center" justify="center">
                        <v-col cols="4">
                            <v-list-item-avatar v-if="user.foto_perfil">
                                <v-img rounded :src="`${API_URL}/${user.foto_perfil}`"></v-img>
                            </v-list-item-avatar>
                        </v-col>
                        <v-col cols="4">
                            <v-list-item-content>
                                <v-list-item-title>{{ user.nombre_usuario }}</v-list-item-title>
                            </v-list-item-content>
                        </v-col>
                        <v-col cols="4">
                            <v-list-item-action>
                                <v-btn icon @click="sendFriendRequest(user.ID_usuario)">
                                    <v-icon>mdi-account-plus</v-icon>
                                </v-btn>
                            </v-list-item-action>
                        </v-col>
                    </v-row>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="3">
          <v-card>
            <v-card-title>Solicitudes de Amistad</v-card-title>
            <v-card-text>
              <v-list dense>
                <v-list-item v-if="friendRequests?.length == 0">
                  <v-list-item-subtitle>No tienes ninguna solicitud de amistad pendiente</v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-for="friendRequest in friendRequests" :key="friendRequest.ID_usuario">
                  <v-row align="center">
                    <v-col cols="4">
                      <v-list-item-avatar>
                        <v-img :src="`${API_URL}/${friendRequest.foto_perfil}`" max-width="50"></v-img>
                      </v-list-item-avatar>
                    </v-col>
                    <v-col cols="4">
                      <v-list-item-content>
                        <v-list-item-title>{{ friendRequest.nombre_usuario }}</v-list-item-title>
                      </v-list-item-content>
                    </v-col>
                    <v-col cols="4">
                      <v-list-item-action>
                        <v-btn icon @click="acceptFriendRequest(friendRequest.ID_usuario)" class="mr-4">
                          <v-icon>mdi-check</v-icon>
                        </v-btn>
                        <v-btn icon @click="declineFriendRequest(friendRequest.ID_usuario)">
                          <v-icon>mdi-close</v-icon>
                        </v-btn>
                      </v-list-item-action>
                    </v-col>
                  </v-row>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

  
        <v-col cols="12" md="3">
          <v-card>
            <v-card-title>Notificaciones</v-card-title>
            <v-card-text>
              <v-list dense>
                <v-list-item v-for="notification in notifications" :key="notification.id">
                  <v-list-item-content>
                    <v-list-item-title class="d-flex justify-space-between align-center">
                      <span>{{ notification.contenido }}</span>
                      <v-btn icon @click="markAsRead(notification.id)">
                        <v-icon color="green">mdi-check</v-icon>
                      </v-btn>
                    </v-list-item-title>
                    <v-list-item-subtitle>{{ notification.fecha_emision }}</v-list-item-subtitle>
                  </v-list-item-content>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>


        <!-- Amigos -->
        <v-col cols="12" md="3">
          <v-card>
            <v-card-title>Amigos</v-card-title>
            <v-card-text>
              <v-data-table :headers="headers" :items="friends" 
              class="elevation-1"
              :pageText="'{0}-{1} de {2}'"
              :items-per-page-text="'Registros por página'">
                <template v-slot:item.chat="{ item }">
                  <v-btn icon @click="openChatModal(item)">
                    <v-icon>mdi-chat</v-icon>
                  </v-btn>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
  
    <!-- Modal de edición de perfil -->
    <v-dialog v-model="editDialog" max-width="600px">
      <v-card>
        <v-form ref="form" v-model="valid">
          <v-card-title>Editar Perfil</v-card-title>
          <v-card-text>
            <v-text-field v-model="editedProfile.nombre_usuario" label="Nombre" :rules="[rules.required]"></v-text-field>
            <v-text-field v-model="editedProfile.correo_electronico" label="Correo" :rules="[rules.required, rules.email]"></v-text-field>
          <div class="text-center">
            <h2>Foto de perfil</h2>
            <v-avatar size="100" class="mb-3" @click="triggerFileInput">
              <v-img 
                style="cursor: pointer;" 
                :src="editedProfile.foto_perfil_preview || `${API_URL}/${editedProfile.foto_perfil}`"
              ></v-img>
            </v-avatar>
            <input type="file" ref="fileInput" @change="handleFileUpload" accept="image/*" hidden />
          </div>

          </v-card-text>
          <v-card-actions>
            <v-btn :disabled="!valid" color="primary" @click="saveProfileChanges">Guardar</v-btn>
            <v-btn @click="editDialog = false">Cancelar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

      <!-- Chat Modal -->
      <v-dialog v-model="chatDialog" max-width="600px">
      <v-card>
        <v-card-title>{{ chatUser?.nombre_usuario }}</v-card-title>
        <v-card-text>
          <v-list dense class="chat-messages">
            <v-list-item v-for="(message, index) in messages" :key="index" :class="{'message-sent': message.id_emisor == selfId, 'message-received': message.id_emisor !== selfId}">
              <v-list-item-content>
                <v-list-item-title class="message-content">
                  <span class="message-text">{{ message.id_emisor == selfId ? 'Yo' : chatUser?.nombre_usuario }}:
                    {{ message.mensaje }}
                  </span>
                  <span class="message-time">{{ formatDate(message.fecha) }}</span>
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

    </v-container>
  </template>
  
  <script setup>
  import { ref,onMounted } from 'vue';
  import Swal from 'sweetalert2';
  import { useAuthStore } from '@/store' // Importa el store de Pinia
  import userService from '@/services/userService';
  import messageService from '@/services/messageService';
  import notificationService from '@/services/notificationService';
  import { API_URL } from "@/main"; 

  const authStore = useAuthStore() // Obtiene el store de Pinia

  const userData = ref({
    //Por seguridad
    id: null,
    name: '',
    email: '',
    avatar: '',
    role: 2
  });
  const editedProfile = ref({
    //Por seguridad
    id: null,
    nombre_usuario: '',
    correo_electronico: '',
    foto_perfil: '',
  });
  const friendRequests = ref([]);
  const notifications = ref([]);
  const selfId = JSON.parse(sessionStorage.getItem('userData')).id;
  const rules = {
    required: value => !!value || 'Campo requerido.',
    email: value => /.+@.+\..+/.test(value) || 'Correo electrónico no válido.',
  };
  const fileInput = ref(null);
  const defaultAvatar = 'https://via.placeholder.com/100';  
  const editDialog = ref(false);
  const valid = ref(false);
  
  const searchQuery = ref('');
  const users = ref([]);
  const filteredUsers = ref([]);

  const headers = ref([
  { text: 'Nombre de Usuario', value: 'nombre_usuario' },
  { text: 'Acciones', value: 'chat', sortable: false }
]);
  const friends = ref([]);
  const chatDialog = ref(false);
  const chatUser = ref(null);
  const messages = ref([]);
  const newMessage = ref('');

  const fetchUsers = async () => {
    const userId = JSON.parse(sessionStorage.getItem('userData')).id;
    try {
      const response = await userService.getUsers(userId);
      users.value = response.data;
    } catch (error) {
      console.error('Error:', error);
    }
};
  
  const editProfile = () => {
    editDialog.value = true;
    editedProfile.value.nombre_usuario = userData.value.nombre_usuario;
    editedProfile.value.correo_electronico = userData.value.correo_electronico;
    editedProfile.value.foto_perfil = userData.value.foto_perfil || userData.value.default;
  };

  const triggerFileInput = () => {
  fileInput.value.click();
};

  const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
      editedProfile.value.foto_perfil = file;

      // Vista previa de la imagen antes de subirla
      const reader = new FileReader();
      reader.onload = (e) => {
        editedProfile.value.foto_perfil_preview = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  };

  const saveProfileChanges = async () => {
    const formData = new FormData();
    formData.append('id', JSON.parse(sessionStorage.getItem('userData')).id);
    formData.append('name', editedProfile.value.nombre_usuario);
    formData.append('email', editedProfile.value.correo_electronico);
    formData.append('avatar', editedProfile.value.foto_perfil);

    try {
      const response = await userService.saveProfileChanges(formData);

      if (!response.data.success) {
        Swal.fire({
          icon: 'error',
          title: 'Error en la actualización',
          text: response.data.error,
        });
      } else {
        Swal.fire({
          icon: 'success',
          title: 'Perfil actualizado',
          text: response.data.message,
        });

        const updatedUserData = {
          id: userData.value.ID_usuario,
          username: userData.value.nombre_usuario,
          role: userData.value.role,
        };

        await fetchUserData();
        updatedUserData.avatar = userData.value.foto_perfil;
        authStore.setUserData(updatedUserData);
      }
    } catch (error) {
      console.error('Error:', error);
    }

    editDialog.value = false;
  };


  const fetchFriendRequests = async () => {
    const selfId = JSON.parse(sessionStorage.getItem('userData')).id;

    try {
      const response = await userService.fetchFriendRequests(selfId);
      friendRequests.value = response.data;
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const acceptFriendRequest = async (requestId) => {
    const selfId = JSON.parse(sessionStorage.getItem('userData')).id;
    const formData = new FormData();
    formData.append('selfId', selfId);
    formData.append('requestId', requestId);

    try {
      const response = await userService.acceptFriendRequest(selfId, requestId);

      if (response.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Solicitud aceptada',
          text: response.data.message,
        });
        fetchFriendRequests();
        fetchFriends();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Ha habido un error',
          text: response.data.error,
        });
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const declineFriendRequest = async (requestId) => {
    const selfId = JSON.parse(sessionStorage.getItem('userData')).id;
    const formData = new FormData();
    formData.append('selfId', selfId);
    formData.append('requestId', requestId);

    try {
      const response = await userService.declineFriendRequest(selfId, requestId);

      if (response.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Solicitud rechazada correctamente',
          text: response.data.message,
        });
        fetchFriendRequests();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Ha habido un error',
          text: response.data.error,
        });
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };


  const sendFriendRequest = async (userId) => {
    const emisorId = JSON.parse(sessionStorage.getItem('userData')).id;

    try {
      const response = await userService.sendFriendRequest(emisorId, userId);

      if (!response.data.success) {
        Swal.fire({
          icon: 'error',
          title: 'Error en la solicitud',
          text: response.data.error,
        });
      } else {
        Swal.fire({
          icon: 'success',
          title: 'Solicitud enviada',
          text: response.data.message,
        });
        fetchUsers();
        fetchFriends();
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  
  const searchUsers = () => {
    if (searchQuery.value && searchQuery.value.length > 0){
        const query = searchQuery.value.toLowerCase().trim();
        filteredUsers.value = users.value.filter(user => user.nombre_usuario.toLowerCase().includes(query));
    }
    else{
        filteredUsers.value = "";
    }
  };

  const fetchFriends = async () => {
    const selfId = JSON.parse(sessionStorage.getItem('userData')).id;

    try {
      const response = await userService.fetchFriends(selfId);
      friends.value = response.data;
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;
  };

  const openChatModal = async (user) => {
    chatUser.value = user;
    chatDialog.value = true;
    await fetchMessages();
  };

  const closeChatModal = () => {
    chatDialog.value = false;
    chatUser.value = null;
    messages.value = [];
  };

  const fetchMessages = async () => {
    try {
      const selfId = JSON.parse(sessionStorage.getItem('userData')).id;
      const response = await messageService.fetchMessages(selfId,chatUser.value.ID_usuario );
      messages.value = response.data;
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const sendMessage = async () => {
    if (newMessage.value.trim() === '') return;
    const selfId = JSON.parse(sessionStorage.getItem('userData')).id;

    try {
      const response = await messageService.sendMessage(selfId, chatUser.value.ID_usuario,newMessage.value)
      if (response.data.success) {
        messages.value.push({
          id_emisor: selfId,
          id_receptor: chatUser.value.id,
          mensaje: newMessage.value,
          fecha: new Date().toISOString(),
        });
        newMessage.value = '';
      } else {
        console.error(response);
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  
  const clearSearch = () => {
    searchQuery.value='';
    filteredUsers.value="";
  };
  const fetchUserData = async () => {
    const userId = JSON.parse(sessionStorage.getItem('userData')).id;
    try {
      const response = await userService.getUserData(userId);
      
      userData.value = response.data;
      if (!userData.value.foto_perfil) 
        userData.value.default = defaultAvatar;
    } catch (error) {
      console.error('Error:', error);
    }
  };
  const fetchNotifications = async () => {
    const userId = JSON.parse(sessionStorage.getItem('userData')).id;

    try {
      const response = await notificationService.fetchNotifications(userId);
      notifications.value = response.data;
    } catch (error) {
      console.error('Error:', error);
    }
  };

  const markAsRead = async (notificationId) => {
    const userId = JSON.parse(sessionStorage.getItem('userData')).id;

    try {
      const response = await notificationService.markAsRead(userId, notificationId);

      if (response.data.success) {
        fetchNotifications();
        Swal.fire("Éxito", "Notificación eliminada correctamente", "success");
      } else {
        Swal.fire("Error", "No se pudo eliminar la notificación", "error");
      }
    } catch (error) {
      Swal.fire("Error", "Ocurrió un problema con el servidor", "error");
    }
  };

  onMounted(()=>{
    fetchUsers();
    fetchUserData();
    fetchFriendRequests();
    fetchFriends();
    fetchNotifications();
  })
  </script>
  
  <style scoped>
  .headline {
    font-weight: bold;
  }
  .text-center {
    text-align: center;
  }
  .chat-messages {
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
  }

  .message-sent {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
  }

  .message-received {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 10px;
  }

  .message-content {
    overflow-x: auto;
    padding: 10px;
    border-radius: 10px;
    position: relative;
  }

  .message-sent .message-content {
    background-color: #c7f9cc;
    color: #000;
  }

  .message-received .message-content {
    background-color: #57cc99;
    color: #000;
    border: 1px solid #ddd;
  }

  .message-text {
    display: block;
    margin-bottom: 5px;
  }

  .message-time {
    display: block;
    font-size: 0.8em;
    color: #666;
    text-align: right;
  }

  </style>
  