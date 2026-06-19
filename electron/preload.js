const { contextBridge, ipcRenderer } = require('electron')

contextBridge.exposeInMainWorld('electronAPI', {
  enregistrerFichier: (nomFichier, contenu) => ipcRenderer.invoke('enregistrer-fichier', nomFichier, contenu),
  telechargerFichier: (url, nomFichier) => ipcRenderer.invoke('telecharger-fichier', url, nomFichier),
  lireConfigBackend: () => ipcRenderer.invoke('lire-config-backend'),
  reessayerConnexion: () => ipcRenderer.invoke('reessayer-connexion'),
  estElectron: true,
})
