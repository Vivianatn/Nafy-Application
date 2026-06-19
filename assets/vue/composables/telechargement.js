import api from '../api'
import { Capacitor } from '@capacitor/core'

const TAILLE_MAX_IPC_OCTETS = 40 * 1024 * 1024

function suffixeCheminApi(cheminApi) {
  const chemin = cheminApi.startsWith('/') ? cheminApi : `/${cheminApi}`

  return chemin.replace(/^\/api/, '')
}

export function estElectron() {
  return typeof window !== 'undefined' && window.electronAPI?.estElectron === true
}

/**
 * URL de telechargement (navigateur / lien direct).
 */
export function urlTelechargementMemeOrigine(cheminApi) {
  const suffixe = suffixeCheminApi(cheminApi)
  const segment = suffixe.startsWith('/') ? suffixe : `/${suffixe}`
  const relatif = `/api${segment}`

  const base = String(api.defaults.baseURL || '/api').replace(/\/$/, '')

  if (Capacitor.isNativePlatform() && base.startsWith('http')) {
    return `${base}${segment}`
  }

  return relatif
}

export function urlPdfDevis(id) {
  return urlTelechargementMemeOrigine(`/devis/${id}/pdf`)
}

export function urlPdfFacture(id) {
  return urlTelechargementMemeOrigine(`/factures/${id}/pdf`)
}

export function urlPdfCatalogue() {
  return urlTelechargementMemeOrigine('/catalogue/pdf')
}

function nomDepuisEntete(contentDisposition, fallback) {
  if (!contentDisposition) {
    return fallback
  }

  const utf8 = /filename\*=UTF-8''([^;]+)/i.exec(contentDisposition)
  if (utf8?.[1]) {
    return decodeURIComponent(utf8[1])
  }

  const ascii = /filename="([^"]+)"/i.exec(contentDisposition)
  if (ascii?.[1]) {
    return ascii[1]
  }

  return fallback
}

function telechargerBlob(blob, nomFichier) {
  const url = URL.createObjectURL(blob)
  const lien = document.createElement('a')
  lien.href = url
  lien.download = nomFichier
  lien.rel = 'noopener'
  document.body.appendChild(lien)
  lien.click()
  lien.remove()
  URL.revokeObjectURL(url)
}

async function telechargerViaElectron(url, nomParDefaut, blob) {
  if (blob && blob.size <= TAILLE_MAX_IPC_OCTETS && window.electronAPI?.enregistrerFichier) {
    const contenu = await blob.arrayBuffer()
    return window.electronAPI.enregistrerFichier(nomParDefaut, contenu)
  }

  if (window.electronAPI?.telechargerFichier) {
    return window.electronAPI.telechargerFichier(url, nomParDefaut)
  }

  throw new Error('API Electron indisponible')
}

/**
 * Telecharge un PDF (Electron, mobile ou navigateur).
 */
export async function telechargerDocument(cheminApi, nomFichierParDefaut) {
  const cheminApiRelatif = suffixeCheminApi(cheminApi)
  const url = urlTelechargementMemeOrigine(cheminApi)

  try {
    const reponse = await api.get(cheminApiRelatif, { responseType: 'blob' })
    const type = String(reponse.headers['content-type'] || reponse.data?.type || '')

    if (reponse.status !== 200 || !(reponse.data instanceof Blob)) {
      throw new Error(`HTTP ${reponse.status}`)
    }

    if (type.includes('application/json') || type.includes('text/html')) {
      throw new Error('Reponse invalide (session expiree ?)')
    }

    const nom = nomDepuisEntete(
      reponse.headers['content-disposition'],
      nomFichierParDefaut,
    )

    if (estElectron()) {
      return telechargerViaElectron(url, nom, reponse.data)
    }

    telechargerBlob(reponse.data, nom)

    return { nom }
  } catch (erreur) {
    if (estElectron() && window.electronAPI?.telechargerFichier) {
      return window.electronAPI.telechargerFichier(url, nomFichierParDefaut)
    }

    throw erreur
  }
}

export function telechargerPdfDevis(id, numero) {
  return telechargerDocument(`/devis/${id}/pdf`, `devis-${numero || id}.pdf`)
}

export function telechargerPdfFacture(id, numero) {
  return telechargerDocument(`/factures/${id}/pdf`, `facture-${numero || id}.pdf`)
}

export function telechargerPdfCatalogue() {
  return telechargerDocument('/catalogue/pdf', 'catalogue-produits.pdf')
}
