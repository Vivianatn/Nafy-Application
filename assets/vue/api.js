import axios from 'axios'
import { Capacitor } from '@capacitor/core'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  },
})

function estAppNativeEmbarquee() {
  if (!Capacitor.isNativePlatform()) {
    return false
  }

  const origine = window.location.origin

  return (
    origine.includes('localhost')
    || origine.startsWith('capacitor://')
    || origine.startsWith('ionic://')
  )
}

export async function initApi() {
  if (!estAppNativeEmbarquee()) {
    return
  }

  try {
    const reponse = await fetch('./app-config.json', { cache: 'no-store' })

    if (!reponse.ok) {
      return
    }

    const config = await reponse.json()

    if (!config.apiUrl) {
      return
    }

    let apiUrl = String(config.apiUrl).replace(/\/$/, '')

    // URL relative : même origine que la page embarquée
    if (apiUrl.startsWith('/')) {
      api.defaults.baseURL = apiUrl
      return
    }

    if (window.location.protocol === 'https:' && apiUrl.startsWith('http://')) {
      apiUrl = apiUrl.replace(/^http:/, 'https:')
    }

    api.defaults.baseURL = apiUrl
  } catch {
    // Configuration optionnelle
  }
}

export default api
