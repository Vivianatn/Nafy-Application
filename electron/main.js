const { app, BrowserWindow, ipcMain, session, shell } = require('electron')
const fs = require('fs')
const path = require('path')
const {
  initialiserConfig,
  lireUrlBackend,
  listerUrlsCandidates,
  resoudreUrlBackend,
  testerUrlBackend,
  cheminConfigUtilisateur,
} = require('./config')

function cheminTelechargementUnique(dossier, nomFichier) {
  const nomSecurise = path.basename(nomFichier).replace(/[<>:"/\\|?*\x00-\x1F]/g, '_') || 'fichier.pdf'
  let cible = path.join(dossier, nomSecurise)

  if (!fs.existsSync(cible)) {
    return cible
  }

  const extension = path.extname(nomSecurise)
  const base = path.basename(nomSecurise, extension)
  let compteur = 1

  while (fs.existsSync(cible)) {
    cible = path.join(dossier, `${base} (${compteur})${extension}`)
    compteur += 1
  }

  return cible
}

function configurerTelechargementsSilencieux() {
  session.defaultSession.on('will-download', (_event, item) => {
    if (item.getSavePath()) {
      return
    }

    const dossier = app.getPath('downloads')
    item.setSavePath(cheminTelechargementUnique(dossier, item.getFilename()))
  })
}

function chargerPageErreur(fenetre, urlsTestees, cheminConfig) {
  return fenetre.loadFile(path.join(__dirname, 'error.html'), {
    query: {
      urls: urlsTestees.join('|'),
      config: cheminConfig,
    },
  })
}

ipcMain.handle('telecharger-fichier', async (event, urlDemandee, nomParDefaut) => {
  const wc = event.sender
  const backendUrl = await resoudreUrlBackend()
  let urlComplete = urlDemandee

  if (!urlDemandee.startsWith('http')) {
    const base = new URL(backendUrl).origin
    urlComplete = `${base}${urlDemandee.startsWith('/') ? urlDemandee : `/${urlDemandee}`}`
  }

  return new Promise((resolve, reject) => {
    let termine = false
    const timeout = setTimeout(() => {
      if (termine) {
        return
      }
      termine = true
      wc.session.removeListener('will-download', onDownload)
      reject(new Error('Delai de telechargement depasse'))
    }, 300000)

    const onDownload = (_ev, item, contents) => {
      if (contents.id !== wc.id) {
        return
      }

      const nom = nomParDefaut || item.getFilename()
      const cible = cheminTelechargementUnique(app.getPath('downloads'), nom)
      item.setSavePath(cible)

      item.once('done', (_e, state) => {
        if (termine) {
          return
        }
        termine = true
        clearTimeout(timeout)
        wc.session.removeListener('will-download', onDownload)

        if (state === 'completed') {
          resolve({ chemin: cible, nom: path.basename(cible) })
          return
        }

        if (state === 'cancelled') {
          reject(new Error('Telechargement annule'))
          return
        }

        reject(new Error(`Telechargement echoue (${state})`))
      })
    }

    wc.session.on('will-download', onDownload)
    wc.downloadURL(urlComplete)
  })
})

ipcMain.handle('enregistrer-fichier', async (_event, nomFichier, contenu) => {
  const dossier = app.getPath('downloads')
  const cible = cheminTelechargementUnique(dossier, nomFichier)
  const buffer = Buffer.from(contenu)

  await fs.promises.writeFile(cible, buffer)

  return { chemin: cible, nom: path.basename(cible) }
})

ipcMain.handle('lire-config-backend', () => ({
  backendUrl: lireUrlBackend(),
  cheminConfig: cheminConfigUtilisateur(),
  urlsCandidates: listerUrlsCandidates(),
}))

ipcMain.handle('reessayer-connexion', async () => {
  const fenetre = BrowserWindow.getFocusedWindow()

  if (!fenetre) {
    return { ok: false }
  }

  const backendUrl = await resoudreUrlBackend()
  const urlsTestees = listerUrlsCandidates()

  if (await testerUrlBackend(backendUrl)) {
    await fenetre.loadURL(backendUrl)
    return { ok: true, backendUrl }
  }

  await chargerPageErreur(fenetre, urlsTestees, cheminConfigUtilisateur())
  return { ok: false }
})

async function creerFenetre() {
  const backendUrl = await resoudreUrlBackend()
  const urlsTestees = listerUrlsCandidates()

  const fenetre = new BrowserWindow({
    width: 1280,
    height: 860,
    minWidth: 960,
    minHeight: 640,
    title: 'Kamille Events Manager',
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js'),
    },
  })

  const accessible = await testerUrlBackend(backendUrl)

  if (accessible) {
    await fenetre.loadURL(backendUrl)
  } else {
    await chargerPageErreur(fenetre, urlsTestees, cheminConfigUtilisateur())
  }

  fenetre.webContents.on('did-fail-load', (_event, errorCode, _description, validatedURL) => {
    if (errorCode === -3) {
      return
    }

    if (validatedURL && validatedURL.includes('error.html')) {
      return
    }

    chargerPageErreur(fenetre, urlsTestees, cheminConfigUtilisateur())
  })

  fenetre.webContents.setWindowOpenHandler(({ url }) => {
    if (url.startsWith('http://') || url.startsWith('https://')) {
      shell.openExternal(url)
    }

    return { action: 'deny' }
  })

  fenetre.webContents.on('will-navigate', (event, url) => {
    if (url.startsWith('file://')) {
      return
    }

    if (url.includes('/api/') && url.includes('/pdf')) {
      event.preventDefault()
      return
    }

    try {
      const origine = new URL(backendUrl).origin

      if (!url.startsWith(origine)) {
        event.preventDefault()
        shell.openExternal(url)
      }
    } catch {
      event.preventDefault()
    }
  })

  return fenetre
}

app.whenReady().then(() => {
  initialiserConfig()
  configurerTelechargementsSilencieux()
  creerFenetre()
})

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit()
  }
})

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    creerFenetre()
  }
})
