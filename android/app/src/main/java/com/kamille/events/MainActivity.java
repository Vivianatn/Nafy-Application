package com.kamille.events;

import android.app.DownloadManager;
import android.content.Context;
import android.net.Uri;
import android.os.Environment;
import android.webkit.CookieManager;
import android.webkit.DownloadListener;
import android.webkit.URLUtil;
import android.webkit.WebView;

import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {

    @Override
    public void onResume() {
        super.onResume();
        configurerTelechargements();
    }

    private void configurerTelechargements() {
        if (getBridge() == null) {
            return;
        }

        WebView webView = getBridge().getWebView();
        if (webView == null) {
            return;
        }

        webView.setDownloadListener(new DownloadListener() {
            @Override
            public void onDownloadStart(
                String url,
                String userAgent,
                String contentDisposition,
                String mimeType,
                long contentLength
            ) {
                String nomFichier = URLUtil.guessFileName(url, contentDisposition, mimeType);

                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
                request.setMimeType(mimeType);
                request.setTitle(nomFichier);
                request.setDescription("Téléchargement…");
                request.setNotificationVisibility(
                    DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED
                );
                request.setDestinationInExternalPublicDir(
                    Environment.DIRECTORY_DOWNLOADS,
                    nomFichier
                );

                String cookies = CookieManager.getInstance().getCookie(url);
                if (cookies != null) {
                    request.addRequestHeader("Cookie", cookies);
                }
                request.addRequestHeader("User-Agent", userAgent);

                DownloadManager manager = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);
                if (manager != null) {
                    manager.enqueue(request);
                }
            }
        });
    }
}
