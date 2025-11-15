import { useAnalytics } from '@/composables/useAnalytics';
import { usePage } from '@inertiajs/vue3';
import { App } from 'vue';

interface AnalyticsSettings {
    enabled: boolean;
    googleAnalyticsId: string;
    googleTagManagerId: string;
    facebookPixelId: string;
    cookieConsentEnabled: boolean;
}

export default {
    install(app: App) {
        const { initializeAnalytics, trackPageView } = useAnalytics();
        let analyticsInitialized = false;

        // Initialize analytics when app mounts
        app.mixin({
            mounted() {
                // Only initialize once at the app level
                if (
                    !analyticsInitialized &&
                    this.$el &&
                    this.$el.id === 'app'
                ) {
                    const page = usePage();
                    const analyticsSettings = page.props
                        .analytics as AnalyticsSettings;

                    if (analyticsSettings) {
                        initializeAnalytics(analyticsSettings);
                        analyticsInitialized = true;

                        // Track initial page view after a short delay
                        setTimeout(() => trackPageView(), 500);
                    }
                }
            },
        });

        // Track page views on Inertia navigation
        app.config.globalProperties.$inertia?.on('navigate', (event: any) => {
            // Small delay to ensure page content is loaded
            setTimeout(() => {
                trackPageView(event.detail.page.url);
            }, 100);
        });

        // Fallback: Track page views on URL changes
        let currentPath = window.location.pathname;
        const observer = new MutationObserver(() => {
            if (window.location.pathname !== currentPath) {
                currentPath = window.location.pathname;
                trackPageView(currentPath);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    },
};
