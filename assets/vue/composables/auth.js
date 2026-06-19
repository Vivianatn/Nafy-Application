import { computed, ref } from 'vue'
import api from '../api'

const session = ref({
  initialise: false,
  connecte: false,
  utilisateur: null,
})

let promesseInitialisation = null

export function utilisateurEstResponsable(utilisateur) {
  if (!utilisateur) {
    return false
  }

  if (utilisateur.type === 'responsable') {
    return true
  }

  return Array.isArray(utilisateur.roles) && utilisateur.roles.includes('ROLE_RESPONSABLE')
}

export function utilisateurEstSecretaire(utilisateur) {
  if (!utilisateur) {
    return false
  }

  if (utilisateurEstResponsable(utilisateur)) {
    return false
  }

  if (utilisateur.type === 'secretaire') {
    return true
  }

  return Array.isArray(utilisateur.roles) && utilisateur.roles.includes('ROLE_SECRETAIRE')
}

export function libelleRoleUtilisateur(utilisateur) {
  if (utilisateurEstResponsable(utilisateur)) {
    return 'Responsable'
  }

  if (utilisateurEstSecretaire(utilisateur)) {
    return 'Secrétaire'
  }

  return 'Utilisateur'
}

export function useAuth() {
  const estResponsable = computed(() => utilisateurEstResponsable(session.value.utilisateur))
  const estSecretaire = computed(() => utilisateurEstSecretaire(session.value.utilisateur))
  const peutGererSecretaires = computed(() => estResponsable.value)
  const roleUtilisateur = computed(() => libelleRoleUtilisateur(session.value.utilisateur))

  async function verifierSession() {
    if (promesseInitialisation) {
      return promesseInitialisation
    }

    promesseInitialisation = (async () => {
      try {
        const { data } = await api.get('/auth/session')
        session.value.connecte = data.connecte === true
        session.value.utilisateur = data.utilisateur ?? null
      } catch (error) {
        if (error.response?.status === 401 || error.response?.status === 403) {
          session.value.connecte = false
          session.value.utilisateur = null
        } else if (!error.response) {
          if (!session.value.connecte) {
            session.value.utilisateur = null
          }
        } else {
          session.value.connecte = false
          session.value.utilisateur = null
        }
      } finally {
        session.value.initialise = true
      }
    })()

    return promesseInitialisation
  }

  async function connecter(identifiant, motDePasse, resterConnecte = false) {
    const { data } = await api.post('/auth/connexion', { identifiant, motDePasse, resterConnecte })
    session.value.connecte = true
    session.value.utilisateur = data.utilisateur ?? null
    session.value.initialise = true
    return data
  }

  async function deconnecter() {
    try {
      await api.post('/auth/deconnexion')
    } catch {
      // La session peut déjà être expirée.
    } finally {
      session.value.connecte = false
      session.value.utilisateur = null
      promesseInitialisation = null
    }
  }

  return {
    session,
    estResponsable,
    estSecretaire,
    peutGererSecretaires,
    roleUtilisateur,
    verifierSession,
    connecter,
    deconnecter,
  }
}
