import { createApp } from 'vue'
import App from './App.vue'
import router from './router' // Importe ton fichier de config
import "./style.css"

const app = createApp(App)

app.use(router) // On dit à Vue d'utiliser le routeur
app.mount('#app')