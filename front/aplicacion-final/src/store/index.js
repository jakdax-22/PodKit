import { defineStore } from 'pinia';
import { API_URL } from "@/main"; 

export const useAuthStore = defineStore({
  id: 'auth',
  state: () => ({
    isLoggedIn: sessionStorage.getItem('isLoggedIn') === 'true',
    userData: JSON.parse(sessionStorage.getItem('userData')) || null,
    podcasts: [], // Array para almacenar los podcasts
  }),
  actions: {
    login() {
      const userData = JSON.parse(sessionStorage.getItem('userData'));
      if (userData) {
        this.isLoggedIn = true;
        this.userData = userData;
      }
    },
    logout() {
      sessionStorage.removeItem('isLoggedIn');
      sessionStorage.removeItem('userData');
      this.isLoggedIn = false;
      this.userData = null;
    },
    setUserData(newData) {
      this.userData = newData;
      sessionStorage.setItem('userData', JSON.stringify(newData));
    },
    async fetchPodcasts() {
      try {
        const response = await fetch(
          `${API_URL}?action=showPodcasts`
        );
        const data = await response.json();
        this.podcasts = data; // Almacenar los podcasts en el array del store
      } catch (error) {
        console.error('Error en la solicitud:', error);
      }
    },
  },
});
