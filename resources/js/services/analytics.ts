interface AnalyticsSettings {
    enabled: boolean;
    googleAnalyticsId?: string;
    googleTagManagerId?: string;
    facebookPixelId?: string;
    cookieConsentEnabled: boolean;
}

class AnalyticsService {
    private settings: AnalyticsSettings;
    private consentGiven = false;
    private scriptsLoaded = false;

    constructor(settings: AnalyticsSettings) {
        this.settings = settings;

        // Check for existing consent
        this.consentGiven = this.getCookieConsent();

        // Auto-load if analytics enabled and either consent not required or consent given
        if (
            this.settings.enabled &&
            (!this.settings.cookieConsentEnabled || this.consentGiven)
        ) {
            this.loadAnalytics();
        }
    }

    /**
     * Load all analytics scripts
     */
    public loadAnalytics(): void {
        if (this.scriptsLoaded || !this.settings.enabled) {
            return;
        }

        console.log('Loading analytics scripts...');

        // Load Google Tag Manager first (it can manage other scripts)
        if (this.settings.googleTagManagerId) {
            this.loadGoogleTagManager();
        }

        // Load Google Analytics if not managed by GTM
        if (
            this.settings.googleAnalyticsId &&
            !this.settings.googleTagManagerId
        ) {
            this.loadGoogleAnalytics();
        }

        // Load Facebook Pixel
        if (this.settings.facebookPixelId) {
            this.loadFacebookPixel();
        }

        this.scriptsLoaded = true;
        this.setCookieConsent(true);
    }

    /**
     * Load Google Tag Manager
     */
    private loadGoogleTagManager(): void {
        const gtmId = this.settings.googleTagManagerId;
        if (!gtmId) return;

        // GTM Head Script
        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtm.js?id=${gtmId}`;
        document.head.appendChild(script);

        // GTM DataLayer
        (window as any).dataLayer = (window as any).dataLayer || [];
        (window as any).dataLayer.push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js',
        });

        // GTM Body NoScript (for users with JS disabled)
        const noscript = document.createElement('noscript');
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.googletagmanager.com/ns.html?id=${gtmId}`;
        iframe.height = '0';
        iframe.width = '0';
        iframe.style.display = 'none';
        iframe.style.visibility = 'hidden';
        noscript.appendChild(iframe);
        document.body.insertBefore(noscript, document.body.firstChild);

        console.log(`Google Tag Manager loaded: ${gtmId}`);
    }

    /**
     * Load Google Analytics 4
     */
    private loadGoogleAnalytics(): void {
        const gaId = this.settings.googleAnalyticsId;
        if (!gaId) return;

        // GA4 Global Site Tag
        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
        document.head.appendChild(script);

        // GA4 Configuration
        const configScript = document.createElement('script');
        configScript.innerHTML = `
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '${gaId}');
    `;
        document.head.appendChild(configScript);

        // Make gtag available globally
        (window as any).gtag =
            (window as any).gtag ||
            function gtag(...a: any[]){(window as any).dataLayer.push(...a);};

        console.log(`Google Analytics loaded: ${gaId}`);
    }

    /**
     * Load Facebook Pixel
     */
    private loadFacebookPixel(): void {
        const pixelId = this.settings.facebookPixelId;
        if (!pixelId) return;

        // Facebook Pixel Code
        const script = document.createElement('script');
        script.innerHTML = `
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '${pixelId}');
      fbq('track', 'PageView');
    `;
        document.head.appendChild(script);

        // No-script fallback
        const noscript = document.createElement('noscript');
        const img = document.createElement('img');
        img.height = 1;
        img.width = 1;
        img.style.display = 'none';
        img.src = `https://www.facebook.com/tr?id=${pixelId}&ev=PageView&noscript=1`;
        noscript.appendChild(img);
        document.head.appendChild(noscript);

        console.log(`Facebook Pixel loaded: ${pixelId}`);
    }

    /**
     * Show cookie consent banner
     */
    public showCookieConsent(): Promise<boolean> {
        return new Promise((resolve) => {
            if (!this.settings.cookieConsentEnabled || this.consentGiven) {
                resolve(true);
                return;
            }

            // Create consent banner
            const banner = document.createElement('div');
            banner.className =
                'fixed bottom-0 left-0 right-0 bg-neutral-900 text-white p-4 z-50 shadow-lg';
            banner.innerHTML = `
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex-1">
            <p class="text-sm">
              We use cookies and similar technologies to analyze website traffic and provide personalized content.
              <a href="/privacy-policy" class="underline hover:no-underline">Learn more</a>
            </p>
          </div>
          <div class="flex gap-2">
            <button id="reject-cookies" class="px-4 py-2 text-sm border border-neutral-600 rounded hover:bg-neutral-800">
              Reject
            </button>
            <button id="accept-cookies" class="px-4 py-2 text-sm bg-blue-600 rounded hover:bg-blue-700">
              Accept
            </button>
          </div>
        </div>
      `;

            document.body.appendChild(banner);

            // Handle consent
            const acceptBtn = banner.querySelector('#accept-cookies');
            const rejectBtn = banner.querySelector('#reject-cookies');

            acceptBtn?.addEventListener('click', () => {
                this.consentGiven = true;
                this.setCookieConsent(true);
                banner.remove();
                this.loadAnalytics();
                resolve(true);
            });

            rejectBtn?.addEventListener('click', () => {
                this.consentGiven = false;
                this.setCookieConsent(false);
                banner.remove();
                resolve(false);
            });
        });
    }

    /**
     * Track custom event
     */
    public trackEvent(
        eventName: string,
        parameters: Record<string, any> = {},
    ): void {
        if (!this.settings.enabled || !this.scriptsLoaded) {
            return;
        }

        // Google Analytics
        if (typeof (window as any).gtag === 'function') {
            (window as any).gtag('event', eventName, parameters);
        }

        // Facebook Pixel
        if (typeof (window as any).fbq === 'function') {
            (window as any).fbq('track', eventName, parameters);
        }

        console.log(`Event tracked: ${eventName}`, parameters);
    }

    /**
     * Track page view
     */
    public trackPageView(path?: string): void {
        if (!this.settings.enabled || !this.scriptsLoaded) {
            return;
        }

        const page = path || window.location.pathname;

        // Google Analytics
        if (typeof (window as any).gtag === 'function') {
            (window as any).gtag('config', this.settings.googleAnalyticsId, {
                page_path: page,
            });
        }

        // Facebook Pixel
        if (typeof (window as any).fbq === 'function') {
            (window as any).fbq('track', 'PageView');
        }

        console.log(`Page view tracked: ${page}`);
    }

    /**
     * Get cookie consent status
     */
    private getCookieConsent(): boolean {
        return localStorage.getItem('cookie-consent') === 'true';
    }

    /**
     * Set cookie consent status
     */
    private setCookieConsent(consent: boolean): void {
        localStorage.setItem('cookie-consent', consent.toString());
    }

    /**
     * Check if analytics is enabled
     */
    public isEnabled(): boolean {
        return this.settings.enabled && this.scriptsLoaded;
    }
}

export default AnalyticsService;
