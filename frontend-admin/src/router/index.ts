import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: Login,
            meta: { guest: true }
        },
        {
            path: '/',
            component: () => import('../views/Layout.vue'), // Wrapper layout
            meta: { requiresAuth: true },
            children: [
                { path: '', redirect: '/dashboard' },
                { path: 'dashboard', name: 'dashboard', component: Dashboard },
                {
                    path: 'users',
                    name: 'users',
                    component: () => import('../views/Users.vue')
                },
                {
                    path: 'assets',
                    name: 'assets',
                    component: () => import('../views/Assets.vue')
                },
                {
                    path: 'songs',
                    name: 'songs',
                    component: () => import('../views/Songs.vue')
                },
                {
                    path: 'videos',
                    name: 'videos',
                    component: () => import('../views/Videos.vue')
                },
                {
                    path: 'backgrounds',
                    name: 'backgrounds',
                    component: () => import('../views/Backgrounds.vue')
                },
            ]
        }
    ]
})

router.beforeEach((to, _from, next) => {
    const token = localStorage.getItem('adminToken')

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ path: '/login' })
        } else {
            next()
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (token) {
            next({ path: '/dashboard' })
        } else {
            next()
        }
    } else {
        next()
    }
})

export default router
