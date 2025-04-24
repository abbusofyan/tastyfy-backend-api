import './bootstrap';
import '../css/app.css';
import 'flag-icons/css/flag-icons.min.css';
import 'v-phone-input/dist/v-phone-input.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createVuetify } from 'vuetify';
import { createVPhoneInput } from 'v-phone-input';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Vuetify
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

const vuetify = createVuetify({
  ssr: true,
  icons: {
    defaultSet: 'mdi', // This is already the default value - only for display purposes
  },
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#B13D27', // Primary color
          primaryTr: '#B13D2711',
          secondary: '#DE9A3C', // Secondary color
          accent: '#B13D27', // Accent color
          error: '#FF5252', // Error color
          info: '#2196F3', // Info color
          success: '#4CAF50', // Success color
          warning: '#FFC107', // Warning color}
          greenlight: '#ddfff0',
          whitegray: '#F8F8F826',
          gray: '#6E6B7B',
        },
      },
    },
  },
});

const vPhoneInput = createVPhoneInput({
  countryIconMode: 'svg',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(
    `./Pages/${name}.vue`,
    import.meta.glob('./Pages/**/*.vue'),
  ),
  setup({
    el, App, props, plugin,
  }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(vuetify)
      .use(vPhoneInput)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
