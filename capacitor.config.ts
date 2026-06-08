import type { CapacitorConfig } from '@capacitor/cli';

/**
 * Backend distant (recommandé avec Tailscale) :
 *   CAPACITOR_SERVER_URL=http://100.79.127.51 npm run android:apk
 *   (remplacez par l'IP Tailscale et le port de votre serveur Symfony)
 *
 * APK autonome (assets embarqués) :
 *   npm run android:apk
 *   puis configurez public/app-config.json (apiUrl vers le serveur Tailscale)
 */
const serverUrl = process.env.CAPACITOR_SERVER_URL;

const config: CapacitorConfig = {
  appId: 'com.kamille.events',
  appName: 'Kamille Events Manager',
  webDir: 'public',
  server: serverUrl
    ? {
        url: serverUrl,
        cleartext: serverUrl.startsWith('http://'),
        androidScheme: serverUrl.startsWith('http://') ? 'http' : 'https',
      }
    : {
        androidScheme: 'https',
      },
};

export default config;
