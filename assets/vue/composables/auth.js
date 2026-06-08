import { ref } from 'vue'
import api from '../api'

const session = ref({
  initialise: false,
  connecte: false,
  utilisateur: null,
})

let promesseInitialisation = null

export function useAuth() {
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
          // Erreur réseau : ne pas déconnecter si une session était déjà active.
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

  function estResponsable() {
    return session.value.utilisateur?.type === 'responsable'
  }

  return {
    session,
    verifierSession,
    connecter,
    deconnecter,
    estResponsable,
  }
}
