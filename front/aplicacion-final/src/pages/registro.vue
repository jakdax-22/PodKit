<template>
  <div class="login-container">
    <v-card class="login-card" elevation="0">
      <v-card-title class="text-center">
        <v-icon large class="mr-2">mdi-account-circle</v-icon>
        <span class="headline">Registrarse</span>
      </v-card-title>

      <v-card-text>
        <v-form @submit.prevent="register">
          <v-text-field v-model="state.username" label="Nombre de usuario" outlined @blur="v$.username.$touch()"></v-text-field>
          <span v-if="v$.username.$error" class="error-message">{{ v$.username.$errors[0].$message }}</span>
          
          <v-text-field v-model="state.email" label="Correo electrónico" outlined @blur="v$.email.$touch()"></v-text-field>
          <span v-if="v$.email.$error" class="error-message">{{ v$.email.$errors[0].$message }}</span>
          
          <v-text-field v-model="state.password" label="Contraseña" type="password" outlined @blur="v$.password.$touch()"></v-text-field>
          <span v-if="v$.password.$error" class="error-message">{{ v$.password.$errors[0].$message }}</span>
          
          <v-text-field v-model="state.confirmPassword" label="Repetir contraseña" type="password" outlined @blur="v$.confirmPassword.$touch()"></v-text-field>
          <span v-if="v$.confirmPassword.$error" class="error-message">{{ v$.confirmPassword.$errors[0].$message }}</span>
          
          <v-btn :disabled="v$.$invalid" type="submit" color="primary" class="mr-4">Registrarse</v-btn>
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
import useVuelidate from '@vuelidate/core'
import { required, helpers, email, minLength, sameAs } from '@vuelidate/validators'
import axios from 'axios'
import Swal from 'sweetalert2'
import { API_URL } from "@/main"; 

export default {
  setup() {
    const atLeastOneUpper = (value) => /^[A-Z]/.test(value);
    
    const state = reactive({
      username: '',
      email: '',
      password: '',
      confirmPassword: ''
    });

    const rules = computed(() => ({
      username: { required: helpers.withMessage('El nombre de usuario es obligatorio', required) },
      email: { 
        required: helpers.withMessage('El correo electrónico es obligatorio', required),
        email: helpers.withMessage('El correo electrónico no es válido', email),
        minLength: helpers.withMessage('El correo electrónico debe tener al menos 6 caracteres', minLength(6))
      },
      password: { 
        required: helpers.withMessage('La contraseña es obligatoria', required),
        minLength: helpers.withMessage('La contraseña debe tener al menos 5 caracteres', minLength(5)),
        atLeastOneUpper: helpers.withMessage('Debe empezar por mayúscula', atLeastOneUpper) 
      },
      confirmPassword: { 
        required: helpers.withMessage('Debes confirmar la contraseña', required),
        sameAsPassword: helpers.withMessage('Las contraseñas no coinciden', sameAs(state.password)) 
      }
    }));

    const v$ = useVuelidate(rules, state);

    const register = async () => {
      v$.value.$touch(); 
      if (v$.value.$invalid) { 
        return;
      }
      try {
        const response = await axios.post(`${API_URL}/mailcontroller.php?action=register`, {
          username: state.username,
          email: state.email,
          password: state.password
        });
        if (response.data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Correo de confirmación enviado exitosamente, comprueba tu bandeja!.',
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.data.message,
          });
        }
      } catch (error) {
        console.error(error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error en el servidor.',
        });
      }
    };

    return { state, v$, register, atLeastOneUpper };
  }
};
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
