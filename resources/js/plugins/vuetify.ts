import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';

export default createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#696cff',
          secondary: '#8592a3',
          success: '#71dd37',
          info: '#03c3ec',
          warning: '#ffab00',
          danger: '#ff3e1d',
          background: '#f5f5f9',
        },
      },
    },
  },
});
