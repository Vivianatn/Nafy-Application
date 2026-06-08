const { app, BrowserWindow } = require('electron')

const BACKEND_URL = process.env.KAMILLE_BACKEND_URL || 'http://localhost:8080'

function createWindow() {
  const win = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
    },
  })

  win.loadURL(BACKEND_URL)
}

app.whenReady().then(createWindow)

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit()
})
