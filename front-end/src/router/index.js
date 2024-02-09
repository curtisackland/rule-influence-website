import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/Home.vue')
    },
    {
      path: '/organization/:orgName',
      name: 'organization',
      component: () => import('../views/Organization.vue')
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/About.vue')
    },
    {
      path: '/rules',
      name: 'rules',
      component: () => import('../views/Rules.vue')
    },
    {
      path: '/responses',
      name: 'responses',
      component: () => import('../views/Responses.vue')
    },
    {
      path: '/comments/:commentId?',
      name: 'comments',
      component: () => import('../views/Comments.vue')
    },
  ]
})

export default router
