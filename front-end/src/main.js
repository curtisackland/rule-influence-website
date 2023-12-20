import { createApp } from 'vue'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

const customTheme = {
    dark: false,
    colors: {
        "rie-primary-color": "#2F4F4F",
        "rie-secondary-color": "#3d6565",
    }
}

const vuetify = createVuetify({
    components,
    directives,
    theme:{
        defaultTheme:"customTheme",
        themes: {
            customTheme
        }
    }
})
import App from './App.vue'
import router from './router/index.js'
import 'bootstrap/dist/css/bootstrap.css'
import './assets/main.css' // Must come after other imports to override styles

const app = createApp(App)

app.use(router)
app.use(vuetify)
app.mount('#app')
