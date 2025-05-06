import axios from 'axios';
import router from '@/router';
import { useAuthStore } from '@/store';
import { getActivePinia } from 'pinia';
import Swal from 'sweetalert2';

let authStore = null;

// Verificar si Pinia está activo antes de usarlo
if (getActivePinia()) {
  authStore = useAuthStore();
}

// Tiempo máximo de inactividad (10 minutos)
const TIMEOUT = 10 * 60 * 1000;
let sessionExpired = false; // Evita múltiples alertas

// Función para reiniciar el tiempo de actividad
const resetActivityTimer = () => {
  if (!sessionExpired) {
    localStorage.setItem('lastActivity', Date.now());
  }
};

// Función para comprobar inactividad
const checkInactivity = () => {
  const lastActivity = parseInt(localStorage.getItem('lastActivity'), 10) || Date.now();
  
  if (Date.now() - lastActivity > TIMEOUT) {
    if (!sessionExpired) {
      sessionExpired = true; // Evita que se muestre más de una vez

      // Muestra la alerta de SweetAlert2
      Swal.fire({
        title: 'Sesión Expirada',
        text: 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.',
        icon: 'warning',
        confirmButtonText: 'Aceptar',
        allowOutsideClick: false,
        allowEscapeKey: false,
      }).then(() => {
        authStore?.logout();
        localStorage.removeItem('token');
        sessionStorage.removeItem('isLoggedIn');
        router.push('/login'); // Redirigir al login después de aceptar
      });
    }
  }
};

// Ejecutar la comprobación de inactividad cada 30 segundos
setInterval(checkInactivity, 30 * 1000);

// Escuchar eventos del usuario para reiniciar el tiempo de actividad
['click', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
  document.addEventListener(event, resetActivityTimer);
});

// Guardar el tiempo inicial cuando la app carga
resetActivityTimer();

// Instancia de Axios
const AXIOS = axios.create({

});

// Interceptor de peticiones
AXIOS.interceptors.request.use(
  (config) => {
    if (sessionStorage.getItem('isLoggedIn') === 'true') {
      resetActivityTimer(); // Reinicia el tiempo de inactividad en cada petición
      const token = localStorage.getItem('token');
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
      }
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor de respuestas para manejar errores 401
AXIOS.interceptors.response.use(
  (response) => response, // Si la respuesta es correcta, simplemente la devuelve
  (error) => {
    if (error.response && error.response.status === 401) {
      console.warn('🔴 Error 401: Sesión no autorizada, cerrando sesión...');
      sessionExpired = true;

      Swal.fire({
        title: 'Sesión Expirada',
        text: 'Tu sesión ha caducado. Por favor, inicia sesión nuevamente.',
        icon: 'error',
        confirmButtonText: 'Aceptar',
        allowOutsideClick: false,
        allowEscapeKey: false,
      }).then(() => {
        authStore?.logout();
        localStorage.removeItem('token');
        sessionStorage.removeItem('isLoggedIn');
        router.push('/login');
      });
    }

    return Promise.reject(error);
  }
);

export default AXIOS;
