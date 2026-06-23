import { createRouter, createWebHistory } from 'vue-router'
import { useAuth, utilisateurEstResponsable } from '../composables/auth'

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
    path: '/inventaire/ajouter',
    name: 'vaisselle-ajouter',
    component: () => import('../views/KitDetailView.vue'),
    props: { mode: 'create' },
    meta: { requiresAuth: true },
  },
  {
    path: '/inventaire/vaisselle/:id',
    name: 'vaisselle-detail',
    component: () => import('../views/KitDetailView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/evenements',
    name: 'evenements',
    component: () => import('../views/EvenementsView.vue'),
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
    path: '/plus',
    name: 'plus',
    component: () => import('../views/PlusView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/secretaires',
    name: 'secretaires',
    component: () => import('../views/SecretairesView.vue'),
    meta: { requiresAuth: true, requiresResponsable: true },
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

  if (to.meta.requiresResponsable && !utilisateurEstResponsable(session.value.utilisateur)) {
    return { name: 'home' }
  }

  return true
})

export default router
