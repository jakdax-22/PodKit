<template>
    <div>
      <v-text-field v-model="password" label="Contraseña" type="password" outlined></v-text-field>
      <v-progress-linear :value="passwordStrength" :color="passwordStrengthColor"></v-progress-linear>
    </div>
  </template>
  
  <script>
  import zxcvbn from 'zxcvbn'
  
  export default {
    data() {
      return {
        password: '',
      };
    },
    computed: {
      passwordStrength() {
        const result = zxcvbn(this.password);
        // La puntuación de zxcvbn varía de 0 a 4
        return (result.score + 1) * 20;
      },
      passwordStrengthColor() {
        // Definir colores basados en la puntuación de la contraseña
        if (this.passwordStrength >= 80) {
          return 'success';
        } else if (this.passwordStrength >= 60) {
          return 'info';
        } else if (this.passwordStrength >= 40) {
          return 'warning';
        } else {
          return 'error';
        }
      },
    },
  };
  </script>
  