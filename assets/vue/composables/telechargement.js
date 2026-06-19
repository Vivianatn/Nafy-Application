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

function estIOS() {
  if (typeof navigator === 'undefined') {
    return false
  }

  return /iPhone|iPad|iPod/i.test(navigator.userAgent)
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
}

function estMobile() {
  if (typeof navigator === 'undefined') {
    return false
  }

  return estIOS()
    || /Android/i.test(navigator.userAgent)
    || Capacitor.isNativePlatform()
    || (navigator.maxTouchPoints > 0 && window.innerWidth < 900)
}

export function urlTelechargementMemeOrigine(cheminApi) {
  const suffixe = suffixeCheminApi(cheminApi)
  const segment = suffixe.startsWith('/') ? suffixe : `/${suffixe}`
  const relatif = `/api${segment}`

  const base = String(api.defaults.baseURL || '/api').replace(/\/$/, '')

  if (base.startsWith('http')) {
    return `${base}${segment}`
  }

  if (typeof window !== 'undefined') {
    return new URL(relatif, window.location.origin).href
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
  setTimeout(() => URL.revokeObjectURL(url), 10_000)
}

function ouvrirUrl(url) {
  const lien = document.createElement('a')
  lien.href = url
  lien.target = '_blank'
  lien.rel = 'noopener noreferrer'
  document.body.appendChild(lien)
  lien.click()
  lien.remove()
}

async function partagerFichier(blob, nomFichier) {
  if (!navigator.share || !navigator.canShare) {
    return false
  }

  const file = new File([blob], nomFichier, { type: blob.type || 'application/pdf' })

  if (!navigator.canShare({ files: [file] })) {
    return false
  }

  await navigator.share({ files: [file], title: nomFichier })
  return true
}

async function telechargerSurMobile(blob, nomFichier, urlFallback) {
  try {
    if (await partagerFichier(blob, nomFichier)) {
      return { nom: nomFichier, methode: 'share' }
    }
  } catch (erreur) {
    if (erreur?.name === 'AbortError') {
      throw erreur
    }
  }

  if (urlFallback) {
    ouvrirUrl(urlFallback)
    return { nom: nomFichier, methode: 'ouverture' }
  }

  telechargerBlob(blob, nomFichier)
  return { nom: nomFichier, methode: 'blob' }
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

export function messageTelechargement(resultat) {
  const nom = resultat?.nom || 'document.pdf'

  switch (resultat?.methode) {
    case 'share':
      return `Document prêt : ${nom}. Enregistrez-le via le menu Partager.`
    case 'ouverture':
      return estIOS()
        ? `Document ouvert : ${nom}. Touchez Partager puis « Enregistrer dans Fichiers ».`
        : `Document ouvert : ${nom}. Enregistrez-le depuis le menu de votre navigateur.`
    default:
      return `Document téléchargé : ${nom}`
  }
}

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
      const resultat = await telechargerViaElectron(url, nom, reponse.data)
      return { nom, methode: 'electron', ...resultat }
    }

    if (estMobile()) {
      return telechargerSurMobile(reponse.data, nom, url)
    }

    telechargerBlob(reponse.data, nom)

    return { nom, methode: 'blob' }
  } catch (erreur) {
    if (erreur?.name === 'AbortError') {
      throw erreur
    }

    if (estElectron() && window.electronAPI?.telechargerFichier) {
      return window.electronAPI.telechargerFichier(url, nomFichierParDefaut)
    }

    if (estMobile() && url) {
      ouvrirUrl(url)
      return { nom: nomFichierParDefaut, methode: 'ouverture' }
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
