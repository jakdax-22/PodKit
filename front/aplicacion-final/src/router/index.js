import { createRouter, createWebHistory } from 'vue-router';
import Admin from '@/pages/admin.vue';
import Index from '@/pages/index.vue';
import Login from '@/pages/login.vue';
import PasswordLost from '@/pages/passwordLost.vue';
import Profile from '@/pages/profile.vue';
import Registro from '@/pages/registro.vue';
import PodcastDetails from '@/pages/PodcastDetails.vue';

const routes = [
  { path: '/', component: Index },
  { path: '/admin', component: Admin, meta: { requiresAuth: true } },
  { path: '/login', component: Login },
  { path: '/password', component: PasswordLost },
  { path: '/profile', component: Profile, meta: { requiresAuth: true } },
  { path: '/registro', component: Registro },
  { path: '/podcast/:id', component: PodcastDetails },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

//**Añadir protección de rutas**
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token'); // Verifica si el usuario tiene token
  const isTokenValid = checkTokenValidity(); // Función para validar si el token sigue activo

  if (to.meta.requiresAuth && (!isAuthenticated || !isTokenValid)) {
    next('/login'); // Si no tiene token válido, redirige a login
  } else {
    next();
  }
});

//**Función para validar si el token está caducado**
function checkTokenValidity() {
  const token = localStorage.getItem('token');
  if (!token) return false;
  
  try {
    const payload = JSON.parse(atob(token.split('.')[1])); // Decodifica el token JWT
    return payload.exp * 1000 > Date.now(); // Compara con la fecha actual
  } catch (e) {
    return false; // Si falla la decodificación, el token es inválido
  }
}

export default router;
