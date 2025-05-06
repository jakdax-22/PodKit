<template>
  <div class="login-container">
    <v-card class="login-card" elevation="0">
      <v-card-title class="text-center">
        <v-icon large class="mr-2">mdi-account-circle</v-icon>
        <span class="headline">Iniciar Sesión</span>
      </v-card-title>

      <v-card-text>
        <!-- Formulario de inicio de sesión -->
        <v-form @submit.prevent="login">
          <v-text-field v-model="state.email" label="Correo electrónico" outlined></v-text-field>
          <span v-if="v$.email.$error"> {{ v$.email.$errors[0].$message }} </span>
          <v-text-field v-model="state.password" label="Contraseña" type="password" outlined></v-text-field>
          <v-btn type="submit" color="primary" class="mr-4">Iniciar Sesión</v-btn>
        </v-form>
        <!-- Mostrar mensaje de error de autenticación -->
        <v-alert v-if="state.authenticationError" type="error">Credenciales inválidas</v-alert>
      </v-card-text>

      <v-card-actions class="d-flex justify-end">
        <router-link to="/registro">¿No tienes una cuenta? Regístrate aquí</router-link>
        <router-link to="/password">¿Has olvidado tu contraseña?</router-link>
      </v-card-actions>
    </v-card>
  </div>
</template>

<script>
import { reactive, computed, watchEffect } from 'vue'
import { useRouter } from 'vue-router'
import useValidate from '@vuelidate/core'
import { required, helpers, email, minLength } from '@vuelidate/validators'
import axios from 'axios'
import { useAuthStore } from '@/store' // Importa el store de Pinia
import userService from '@/services/userService'

export default {
  setup() {
    const atLeastOneUpper = value => /^[A-Z]/.test(value)
    const state = reactive({
      email: '',
      password: '',
      authenticationError: false
    })
    const router = useRouter();
    const authStore = useAuthStore() // Obtiene el store de Pinia

    const rules = computed(() => ({
      email: { required, email, minLength: minLength(6) },
      password: { required, minLength: minLength(5), atLeastOneUpper: helpers.withMessage('Debe empezar por mayúscula', atLeastOneUpper) }
    }))

    const v$ = useValidate(rules, state)

    const handleSuccessfulLogin = userData => {
      sessionStorage.setItem('isLoggedIn', 'true')
      sessionStorage.setItem('userData', JSON.stringify(userData))
      const token = userData.token;
      localStorage.setItem('token', token); // Guardamos el token en localStorage
      authStore.login(userData) // Actualiza el estado de isLoggedIn y userData en el store de Pinia
      router.push({ path: '/' })
    }

    const login = async () => {
      try {
        const response = await userService.login(state.email, state.password);
        state.authenticationError = false;
        handleSuccessfulLogin(response.data);
      } catch (error) {
        if (error.response && error.response.status === 401) {
          state.authenticationError = true;
        }
      }
    };


    // Verificar la sesión al cargar el componente y en cada actualización
    watchEffect(() => {
      state.isLoggedIn = sessionStorage.getItem('isLoggedIn') === 'true'
    })

    return { state, v$, login, atLeastOneUpper, router }
  }
}
</script>


<style scoped>
.login-container {
  background-image: url('https://images.unsplash.com/photo-1688457537834-e3bdc02d3743?q=80&w=2031&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
  background-size: cover; /* Ajusta la imagen para que cubra completamente el contenedor */
  background-position: center;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-card {
  max-width: 400px;
  background-color: rgba(0, 0, 0, 0.7); /* Fondo oscuro */
  border-radius: 10px;
  color: #fff; /* Texto blanco */
}

.headline {
  font-size: 24px;
  font-weight: bold;
}

.v-text-field,
.v-btn {
  width: 100%;
  margin-bottom: 20px;
}

.v-card-actions {
  padding: 16px;
}

/* Cambiar el color de las líneas separadoras */
.v-divider {
  background-color: rgba(255, 255, 255, 0.5); /* Líneas separadoras semi-transparentes */
}
</style>
