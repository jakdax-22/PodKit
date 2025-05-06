<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Buscar usuario"
            single-line
            hide-details
            class="mx-4"
          />

          <v-data-table
            :headers="headers"
            :items="filteredUsers"
            item-key="ID_usuario"
            :items-per-page="10"
            class="elevation-1"
            dense
            hide-default-footer
            no-data-text="No hay registros disponibles"
          >
            <template v-slot:item.rol="{ item }">
              <v-chip :color="item.rol === 1 ? 'red' : 'blue'" dark>
                {{ item.rol === 1 ? 'Administrador' : 'Usuario' }}
              </v-chip>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn
                v-if="item.rol !== 1"
                @click="makeAdmin(item.ID_usuario)"
                color="primary"
                small
              >
                Hacer Admin
              </v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import userService from '@/services/userService';

export default {
  name: 'UserAdminTable',
  setup() {
    const users = ref([]);
    const search = ref('');

    const headers = [
      { text: 'ID', value: 'ID_usuario', align: 'start' },
      { text: 'Nombre', value: 'nombre_usuario' },
      { text: 'Correo', value: 'correo_electronico' },
      { text: 'Rol', value: 'rol' },
      { text: 'Acciones', value: 'actions', sortable: false }
    ];

    const fetchUsers = async () => {
      try {
        const userId = JSON.parse(sessionStorage.getItem('userData')).id;
        const response = await userService.getUsers(userId);
        users.value = response.data;
      } catch (err) {
        console.error('Error al obtener los usuarios', err);
      }
    };

    const filteredUsers = computed(() => {
      return users.value
        .filter(user =>
          user.nombre_usuario.toLowerCase().includes(search.value.toLowerCase()) ||
          user.correo_electronico.toLowerCase().includes(search.value.toLowerCase())
        )
        .sort((a, b) => a.rol - b.rol);
    });

    const makeAdmin = async (userId) => {
      const formDataToSend = new FormData();
      formDataToSend.append('userId', userId);
      try {
        await userService.makeAdmin(formDataToSend);
        fetchUsers();
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'Usuario ahora es administrador.',
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo actualizar el rol del usuario.',
        });
      }
    };

    fetchUsers();

    return {
      users,
      headers,
      search,
      filteredUsers,
      makeAdmin
    };
  }
};
</script>

<style scoped>
.v-card-title {
  font-weight: bold;
  font-size: 1.2em;
  text-align: center;
}

.v-chip {
  font-weight: bold;
}

.v-btn {
  font-size: 0.8rem;
  padding: 5px 10px;
}

.elevation-1 {
  box-shadow: none;
}

.v-data-table .v-data-table-header th {
  font-weight: bold;
  text-transform: uppercase;
}
</style>
