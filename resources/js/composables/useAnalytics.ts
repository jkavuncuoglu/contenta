import { ref } from 'vue';
import AnalyticsService from '@/services/analytics';

interface AnalyticsSettings {
  enabled: boolean;
  googleAnalyticsId?: string;
  googleTagManagerId?: string;
  facebookPixelId?: string;
  cookieConsentEnabled: boolean;
}

const analyticsService = ref<AnalyticsService | null>(null);

export function useAnalytics() {
  const initializeAnalytics = (settings: AnalyticsSettings) => {
    if (!analyticsService.value) {
      analyticsService.value = new AnalyticsService(settings);

      // Show cookie consent if required
      if (settings.cookieConsentEnabled && settings.enabled) {
        analyticsService.value.showCookieConsent();
      }
    }
  };

  const trackEvent = (eventName: string, parameters: Record<string, any> = {}) => {
    analyticsService.value?.trackEvent(eventName, parameters);
  };

  const trackPageView = (path?: string) => {
    analyticsService.value?.trackPageView(path);
  };

  const isEnabled = () => {
    return analyticsService.value?.isEnabled() || false;
  };

  const trackFormSubmission = (formName: string, success: boolean = true) => {
    trackEvent('form_submit', {
      form_name: formName,
      success: success
    });
  };

  const trackButtonClick = (buttonName: string, location?: string) => {
    trackEvent('button_click', {
      button_name: buttonName,
      location: location || 'unknown'
    });
  };

  const trackDownload = (fileName: string, fileType?: string) => {
    trackEvent('file_download', {
      file_name: fileName,
      file_type: fileType || 'unknown'
    });
  };

  return {
    initializeAnalytics,
    trackEvent,
    trackPageView,
    trackFormSubmission,
    trackButtonClick,
    trackDownload,
    isEnabled,
  };
}