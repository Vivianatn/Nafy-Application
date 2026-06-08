/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './vue/App.vue'
import router from './vue/router'
import { initApi } from './vue/api'

async function demarrer() {
  await initApi()

  const app = createApp(App)
  app.use(createPinia())
  app.use(router)
  app.mount('#app')
}

demarrer()
