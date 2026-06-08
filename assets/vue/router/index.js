import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/auth'

const routes = [
  {
    path: '/connexion',
    name: 'connexion',
    component: () => import('../views/ConnexionView.vue'),
    meta: { guest: true },
  },
  {
    path: '/mot-de-passe-oublie',
    name: 'mot-de-passe-oublie',
    component: () => import('../views/MotDePasseOublieView.vue'),
    meta: { guest: true },
  },
  {
    path: '/reinitialiser-mot-de-passe',
    name: 'reinitialiser-mot-de-passe',
    component: () => import('../views/ReinitialiserMotDePasseView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    name: 'home',
    component: () => import('../views/AccueilView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/inventaire',
    name: 'inventaire',
    component: () => import('../views/InventaireView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/devis',
    name: 'devis',
    component: () => import('../views/DevisView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/facture',
    name: 'facture',
    component: () => import('../views/FactureView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/secretaire/ajouter',
    name: 'ajouter-secretaire',
    component: () => import('../views/AjouterSecretaireView.vue'),
    meta: { requiresAuth: true, requiresResponsable: true },
  },
  {
    path: '/responsable/ajouter',
    name: 'ajouter-responsable',
    component: () => import('../views/AjouterResponsableView.vue'),
    meta: { requiresAuth: true, requiresResponsable: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const { session, verifierSession } = useAuth()

  if (!session.value.initialise) {
    await verifierSession()
  }

  if (to.meta.guest) {
    if (session.value.connecte) {
      return { name: 'home' }
    }
    return true
  }

  if (to.meta.requiresAuth && !session.value.connecte) {
    return {
      name: 'connexion',
      query: { redirect: to.fullPath },
    }
  }

  if (to.meta.requiresResponsable && session.value.utilisateur?.type !== 'responsable') {
    return { name: 'home' }
  }

  return true
})

export default router
