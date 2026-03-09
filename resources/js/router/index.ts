import { createRouter, createWebHistory } from 'vue-router';
import SneatLayout from '../layouts/SneatLayout.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: SneatLayout,
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('../pages/Dashboard.vue'),
        },
      ],
    },
  ],
});

export default router;
