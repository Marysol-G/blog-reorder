console.log('BR-Consent.js loaded');

(function() {
  // Cookie helpers
  function setCookie(name, value, maxAge) {
    document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + maxAge;
  }
  function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
  }

  function hideBanner(banner) {
    if (banner) banner.style.display = 'none';
  }

  function trackTopic(brData, cookieName, cookieExpiry) {
    const currentTopic = brData.currentTopic;
    if (!currentTopic) return;
    console.log('BR-Consent tracking topic:', currentTopic);
    let weights = {};
    const raw = getCookie(cookieName);
    try { weights = raw ? JSON.parse(raw) : {}; } catch (e) { weights = {}; }
    weights[currentTopic] = (weights[currentTopic] || 0) + 1;
    setCookie(cookieName, JSON.stringify(weights), cookieExpiry);
  }

  // Run after DOM is fully loaded so banner exists
  document.addEventListener('DOMContentLoaded', function() {
    console.log('BR-Consent DOMContentLoaded');
    const brData       = window.brConsentData || {};
    console.log('BR-Consent data:', brData);
    const cookieName   = brData.cookieName;
    const consentName  = brData.consentName;
    const cookieExpiry = parseInt(brData.cookieExpiry, 10) || 2592000;
    
    const banner     = document.getElementById('br-consent-banner');
    const acceptBtn  = document.getElementById('br-consent-accept');
    const declineBtn = document.getElementById('br-consent-decline');

    function acceptHandler(event) {
      console.log('BR-Consent Accept Handler');
      setCookie(consentName, '1', cookieExpiry);
      hideBanner(banner);
      trackTopic(brData, cookieName, cookieExpiry);
    }
    function declineHandler(event) {
      console.log('BR-Consent Decline Handler');
      setCookie(consentName, '0', cookieExpiry);
      hideBanner(banner);
    }

    // Initialize
    console.log('BR-Consent initializing');
    const consent = getCookie(consentName);
    if (consent === null) {
      console.log('BR-Consent: No consent, showing banner');
      if (banner) banner.style.display = 'block';
    } else if (consent === '1') {
      console.log('BR-Consent: consent=1, tracking topic');
      hideBanner(banner);
      trackTopic(brData, cookieName, cookieExpiry);
    } else {
      console.log('BR-Consent: consent=0, hiding banner');
      hideBanner(banner);
    }

    if (acceptBtn) {
      acceptBtn.addEventListener('click', acceptHandler);
      console.log('BR-Consent bound accept');
    }
    if (declineBtn) {
      declineBtn.addEventListener('click', declineHandler);
      console.log('BR-Consent bound decline');
    }
  });
})();
