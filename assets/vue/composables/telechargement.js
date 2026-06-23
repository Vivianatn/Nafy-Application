import api from '../api'
import { Capacitor } from '@capacitor/core'
import { Filesystem, Directory } from '@capacitor/filesystem'
import { Share } from '@capacitor/share'

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

function estAndroid() {
  if (typeof navigator === 'undefined') {
    return false
  }

  return /Android/i.test(navigator.userAgent) || Capacitor.getPlatform() === 'android'
}

function estMobile() {
  if (typeof navigator === 'undefined') {
    return false
  }

  return estIOS()
    || estAndroid()
    || Capacitor.isNativePlatform()
    || (navigator.maxTouchPoints > 0 && window.innerWidth < 900)
}

export function urlTelechargementMemeOrigine(cheminApi) {
  const suffixe = suffixeCheminApi(cheminApi)
  const segment = suffixe.startsWith('/') ? suffixe : `/${suffixe}`
  const relatif = `/api${segment}`

  const base = String(api.defaults.baseURL || '/api').replace(/\/$/, '')

  let url

  if (base.startsWith('http')) {
    url = `${base}${segment}`
  } else if (typeof window !== 'undefined') {
    url = new URL(relatif, window.location.origin).href
  } else {
    url = relatif
  }

  const separateur = url.includes('?') ? '&' : '?'
  return `${url}${separateur}download=${Date.now()}`
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

function blobPdf(source) {
  if (source instanceof Blob && source.type === 'application/pdf') {
    return source
  }

  return new Blob([source], { type: 'application/pdf' })
}

function blobVersBase64(blob) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => {
      const result = String(reader.result || '')
      const virgule = result.indexOf(',')
      resolve(virgule >= 0 ? result.slice(virgule + 1) : result)
    }
    reader.onerror = () => reject(reader.error ?? new Error('Lecture du fichier impossible.'))
    reader.readAsDataURL(blob)
  })
}

function telechargerBlob(blob, nomFichier) {
  const pdf = blobPdf(blob)
  const url = URL.createObjectURL(pdf)
  const lien = document.createElement('a')
  lien.href = url
  lien.download = nomFichier
  lien.rel = 'noopener'
  lien.style.display = 'none'
  document.body.appendChild(lien)
  lien.click()
  lien.remove()
  setTimeout(() => URL.revokeObjectURL(url), 60_000)
}

async function partagerFichier(blob, nomFichier) {
  if (!navigator.share) {
    return false
  }

  const file = new File([blobPdf(blob)], nomFichier, { type: 'application/pdf' })

  if (navigator.canShare && !navigator.canShare({ files: [file] })) {
    return false
  }

  await navigator.share({ files: [file], title: nomFichier })
  return true
}

function declencherTelechargementAndroid(url) {
  return new Promise((resolve) => {
    const lien = document.createElement('a')
    lien.href = url
    lien.rel = 'noopener'
    lien.style.display = 'none'
    document.body.appendChild(lien)
    lien.click()
    lien.remove()
    setTimeout(resolve, 400)
  })
}

async function enregistrerViaCapacitor(blob, nomFichier) {
  const base64 = await blobVersBase64(blobPdf(blob))

  const cheminCache = `pdf/${nomFichier}`

  await Filesystem.writeFile({
    path: cheminCache,
    data: base64,
    directory: Directory.Cache,
    recursive: true,
  })

  const { uri } = await Filesystem.getUri({
    directory: Directory.Cache,
    path: cheminCache,
  })

  await Share.share({
    title: nomFichier,
    url: uri,
    dialogTitle: 'Enregistrer le PDF',
  })

  return { nom: nomFichier, methode: 'share' }
}

async function forcerTelechargement(blob, nomFichier) {
  const pdf = blobPdf(blob)

  if (Capacitor.getPlatform() === 'ios') {
    return enregistrerViaCapacitor(pdf, nomFichier)
  }

  if (estMobile()) {
    try {
      if (await partagerFichier(pdf, nomFichier)) {
        return { nom: nomFichier, methode: 'share' }
      }
    } catch (erreur) {
      if (erreur?.name === 'AbortError') {
        throw erreur
      }
    }
  }

  telechargerBlob(pdf, nomFichier)

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
    case 'fichier':
      return `PDF enregistré dans ${resultat.chemin || 'Téléchargements'}.`
    case 'share':
      return estAndroid()
        ? `Choisissez « Enregistrer dans Téléchargements » ou une application de fichiers pour ${nom}.`
        : `Document prêt : ${nom}. Touchez « Enregistrer dans Fichiers » dans le menu Partager.`
    default:
      return `Document téléchargé : ${nom}`
  }
}

export async function telechargerDocument(cheminApi, nomFichierParDefaut) {
  const cheminApiRelatif = suffixeCheminApi(cheminApi)
  const url = urlTelechargementMemeOrigine(cheminApi)

  try {
    const reponse = await api.get(cheminApiRelatif, {
      responseType: 'blob',
      params: { download: Date.now() },
    })
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

    if (Capacitor.getPlatform() === 'android') {
      await declencherTelechargementAndroid(url)
      return {
        nom,
        methode: 'fichier',
        chemin: `Téléchargements/${nom}`,
      }
    }

    if (estElectron()) {
      const resultat = await telechargerViaElectron(url, nom, reponse.data)
      return { nom, methode: 'electron', ...resultat }
    }

    return forcerTelechargement(reponse.data, nom)
  } catch (erreur) {
    if (erreur?.name === 'AbortError') {
      throw erreur
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
