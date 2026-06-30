import { createApp } from 'vue'
import { createPinia } from 'pinia'

<<<<<<< HEAD
// 🎨 Agrega estas dos líneas para activar Bootstrap en tus componentes Vue de golpe
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

=======
>>>>>>> respaldo-caja
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

<<<<<<< HEAD
app.mount('#app')
=======
app.mount('#app')
>>>>>>> respaldo-caja
