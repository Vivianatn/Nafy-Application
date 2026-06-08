import { ref } from 'vue'

const STORAGE_KEY = 'kamille_heures_recuperation'

function chargerDepuisStockage() {
  try {
    const brut = localStorage.getItem(STORAGE_KEY)
    return brut ? JSON.parse(brut) : {}
  } catch {
    return {}
  }
}

const heures = ref(chargerDepuisStockage())

function cleEvenement(devisId) {
  return `devis-${devisId}`
}

function persister() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(heures.value))
}

export function useHeuresRecuperation() {
  function getHeure(devisId) {
    return heures.value[cleEvenement(devisId)] ?? ''
  }

  function setHeure(devisId, heure) {
    const cle = cleEvenement(devisId)

    if (!heure) {
      const copie = { ...heures.value }
      delete copie[cle]
      heures.value = copie
    } else {
      heures.value = { ...heures.value, [cle]: heure }
    }

    persister()
  }

  function formaterHeure(heure) {
    if (!heure) {
      return ''
    }

    const [h, m] = heure.split(':')
    return `${h}h${m}`
  }

  function formaterHeureLongue(heure) {
    if (!heure) {
      return ''
    }

    const [h, m] = heure.split(':')
    return `${h} h ${m}`
  }

  return {
    getHeure,
    setHeure,
    formaterHeure,
    formaterHeureLongue,
  }
}
