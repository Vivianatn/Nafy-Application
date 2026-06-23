import { ref } from 'vue'
import api from '../api'

const cache = ref(null)

export async function chargerOrganisateur() {
  if (cache.value) {
    return cache.value
  }

  try {
    const { data } = await api.get('/organisateur')
    cache.value = data
    return data
  } catch {
    cache.value = {
      nomEntreprise: 'Nafy Bonine',
      nomMarque: 'Kamille Events',
      siret: '',
      logoUrl: '/images/logo.svg',
    }
    return cache.value
  }
}
