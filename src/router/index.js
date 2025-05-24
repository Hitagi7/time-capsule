import { createRouter, createWebHistory } from 'vue-router'
import LandingPage from '@/components/Pages/Landing.vue'
import UserDashboard from '@/components/Pages/Dashboard.vue'

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: LandingPage,
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: UserDashboard,
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('userEmail')

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
