const fs = require('fs')
const path = require('path')
const { app, net } = require('electron')

const NOM_FICHIER = 'config.json'

const URLS_PAR_DEFAUT = [
  'http://symfony.mmi-troyes.fr:8319',
  'http://127.0.0.1:8319',
  'http://localhost:8319',
]

function cheminConfigUtilisateur() {
  return path.join(app.getPath('userData'), NOM_FICHIER)
}

function cheminConfigParDefaut() {
  if (app.isPackaged) {
    return path.join(process.resourcesPath, 'default-config.json')
  }

  return path.join(__dirname, 'default-config.json')
}

function lireConfigBrute() {
  try {
    const brut = fs.readFileSync(cheminConfigUtilisateur(), 'utf8')
    return JSON.parse(brut)
  } catch {
    try {
      const brut = fs.readFileSync(cheminConfigParDefaut(), 'utf8')
      return JSON.parse(brut)
    } catch {
      return {}
    }
  }
}

function initialiserConfig() {
  const cible = cheminConfigUtilisateur()

  if (fs.existsSync(cible)) {
    return
  }

  fs.mkdirSync(path.dirname(cible), { recursive: true })
  fs.copyFileSync(cheminConfigParDefaut(), cible)
}

function lireUrlBackend() {
  if (process.env.KAMILLE_BACKEND_URL) {
    return process.env.KAMILLE_BACKEND_URL.replace(/\/$/, '')
  }

  const config = lireConfigBrute()

  if (config.backendUrl) {
    return String(config.backendUrl).replace(/\/$/, '')
  }

  return URLS_PAR_DEFAUT[0]
}

function listerUrlsCandidates() {
  const config = lireConfigBrute()
  const urls = []

  if (process.env.KAMILLE_BACKEND_URL) {
    urls.push(process.env.KAMILLE_BACKEND_URL.replace(/\/$/, ''))
  }

  if (config.backendUrl) {
    urls.push(String(config.backendUrl).replace(/\/$/, ''))
  }

  if (Array.isArray(config.backendUrlsFallback)) {
    for (const url of config.backendUrlsFallback) {
      urls.push(String(url).replace(/\/$/, ''))
    }
  }

  for (const url of URLS_PAR_DEFAUT) {
    urls.push(url)
  }

  return [...new Set(urls.filter(Boolean))]
}

function testerUrlBackend(url) {
  return new Promise((resolve) => {
    const request = net.request({ method: 'GET', url: `${url}/api/auth/session` })
    let termine = false

    const finir = (resultat) => {
      if (termine) {
        return
      }

      termine = true
      resolve(resultat)
    }

    request.on('response', (response) => {
      const type = String(response.headers['content-type'] || '')
      const ok = response.statusCode === 200 && type.includes('application/json')
      finir(ok)
      response.on('data', () => {})
    })

    request.on('error', () => finir(false))
    request.end()

    setTimeout(() => {
      request.abort()
      finir(false)
    }, 4000)
  })
}

async function resoudreUrlBackend() {
  const candidates = listerUrlsCandidates()

  for (const url of candidates) {
    if (await testerUrlBackend(url)) {
      return url
    }
  }

  return lireUrlBackend()
}

module.exports = {
  cheminConfigUtilisateur,
  initialiserConfig,
  lireUrlBackend,
  listerUrlsCandidates,
  resoudreUrlBackend,
  testerUrlBackend,
}
