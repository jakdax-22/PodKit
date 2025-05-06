<template>
  <div class="login-container">
    <v-card class="login-card" elevation="0">
      <v-card-title class="text-center">
        <v-icon large class="mr-2">mdi-lock-reset</v-icon>
        <span class="headline">Recuperar Contraseña</span>
      </v-card-title>

      <v-card-text>
        <v-form @submit.prevent="recoverPassword">
          <v-text-field v-model="state.email" label="Correo electrónico" outlined @blur="v$.email.$touch()"></v-text-field>
          <span v-if="v$.email.$error" class="error-message">{{ v$.email.$errors[0].$message }}</span>

          <v-btn :disabled="v$.$invalid" type="submit" color="primary" class="mr-4">Recuperar Contraseña</v-btn>
        </v-form>
      </v-card-text>

      <v-card-actions class="d-flex justify-end">
        <router-link to="/login">¿Ya tienes una cuenta? Inicia sesión aquí</router-link>
      </v-card-actions>
    </v-card>
  </div>
</template>

<script>
import { reactive, computed } from 'vue'
import useValidate from '@vuelidate/core'
import { required, helpers, email } from '@vuelidate/validators'
import Swal from 'sweetalert2'
import userService from '@/services/userService'

export default {
  setup() {
    const state = reactive({
      email: ''
    })

    const rules = computed(() => ({
      email: { 
        required: helpers.withMessage('El correo electrónico es obligatorio', required),
        email: helpers.withMessage('El correo electrónico no es válido', email)
      }
    }))

    const v$ = useValidate(rules, state)

    const recoverPassword = async () => {
      v$.value.$touch()
      if (v$.$invalid) {
        return
      }
      try {
        const response = await userService.recoverPassword(state.email)
        if (response.data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Te hemos enviado un correo para restablecer tu contraseña. Revisa tu bandeja de entrada.',
          })
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.data.message,
          })
        }
      } catch (error) {
        console.error(error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error en el servidor.',
        })
      }
    }

    // Asegúrate de devolver state, v$ y recoverPassword
    return { state, v$, recoverPassword }
  }
}
</script>

<style scoped>
.login-container {
  background-image: url('https://images.unsplash.com/photo-1688457537834-e3bdc02d3743?q=80&w=2031&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
  background-size: cover;
  background-position: center;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-card {
  max-width: 400px;
  background-color: rgba(0, 0, 0, 0.7);
  border-radius: 10px;
  color: #fff;
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

.v-divider {
  background-color: rgba(255, 255, 255, 0.5);
}

.error-message {
  color: red;
  font-size: 14px;
  margin-top: -10px;
  display: block;
}
</style>
