// main.js

// Importa Pinia y crea una instancia
import { createPinia } from "pinia"; 

// Importa los plugins y el componente principal
import { registerPlugins } from '@/plugins'
import App from './App.vue'
// Importa la aplicación Vue
import { createApp } from 'vue'

const pinia = createPinia()
const app = createApp(App)

export const API_URL = import.meta.env.VITE_API_URL


app.use(pinia)

// Registra los plugins
registerPlugins(app)

// Monta la aplicación en el elemento con el ID "app"
app.mount('#app')
