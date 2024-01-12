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
      path: '/frdocs/:frdocNumber?',
      name: 'frdocs',
      component: () => import('../views/FRDocs.vue')
    },
    {
      path: '/responses/:commentId?/:frdocNumber?',
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
