import '../css/app.css';
import './bootstrap';

//import { createInertiaApp } from '@inertiajs/vue3';
//import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
//import { createApp, h } from 'vue';
//import { ZiggyVue } from '../../vendor/tightenco/ziggy';
//import { registerGlobalToast, toastRef } from './Composables/useGlobalToast'

//const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

//registerGlobalToast()

//createInertiaApp({
   // title: (title) => `${title} - ${appName}`,
   // resolve: (name) =>
   //     resolvePageComponent(
   //         `./Pages/${name}.vue`,
   //         import.meta.glob('./Pages/**/*.vue'),
   //     ),
   // setup({ el, App, props, plugin }) {
   //     return createApp({ render: () => h(App, props) })
   //         .use(plugin)
   //         .use(ZiggyVue)
   //         .mount(el);
   // },
   // progress: {
   //     color: '#4B5563',
   // },
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import Toast from './Components/Toast.vue'
import { registerGlobalToast, toastRef } from './Composables/useGlobalToast'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Registra a função global do toast antes de montar
registerGlobalToast()

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({
            render: () => h(App, props)
        })

        vueApp.use(plugin)
        vueApp.use(ZiggyVue)

        // Registra o componente Toast globalmente (opcional, se quiser usar <Toast /> diretamente em views)
        vueApp.component('Toast', Toast)

        // Mixin para capturar o ref global do componente
        vueApp.mixin({
            mounted() {
                if (this.$refs.globalToast) {
                    toastRef.value = this.$refs.globalToast
                }
            }
        })

        vueApp.mount(el)
    },
    progress: {
        color: '#4B5563',
    },
});
