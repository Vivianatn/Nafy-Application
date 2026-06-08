import { reactive } from 'vue'

// État global partagé du pop-up de notification.
const etat = reactive({
  visible: false,
  message: '',
  type: 'succes', // 'succes' | 'erreur'
})

const DUREE_AUTO_FERMETURE = 10000 // 10 secondes
let timer = null

export function useNotification() {
  function fermer() {
    etat.visible = false
    if (timer !== null) {
      clearTimeout(timer)
      timer = null
    }
  }

  function notifier(message, type = 'succes') {
    if (timer !== null) {
      clearTimeout(timer)
    }
    etat.message = message
    etat.type = type
    etat.visible = true
    timer = setTimeout(fermer, DUREE_AUTO_FERMETURE)
  }

  return { etat, notifier, fermer }
}
