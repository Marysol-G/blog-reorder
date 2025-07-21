# Behavioral Recommendation Topics Plugin

**Version:** 1.0.1
**Author:** Marysol Gurrola
**Requires:** WordPress 5.0+, PHP 7.4+

---

## Description

This plugin displays a cookie-consent banner and then reorders your blog index based on the user’s inferred interests. It tracks which topic tags (AI, Marketing, Engineering) the visitor views most often and boosts those posts to the top of the main loop once consent is granted.

---

## Features

* **Cookie Consent Banner:** Prompt visitors to accept or decline personalized post ordering.
* **Behavior Tracking:** Automatically increment view counts for AI, Marketing, and Engineering topics on each post view.
* **Dynamic Reordering:** After consent, the main blog loop is reordered using a weighted ranking (most-viewed topics first).
* **Lightweight & Self-Contained:** No external services; all logic lives in your WP install and browser cookies.
* **Fallback:** If consent is declined or not given, the blog remains in its default order.

---

## Installation

1. **Upload** the plugin folder `behavioral-recommendation-topics` to your `/wp-content/plugins/` directory.
2. **Activate** the plugin in the WordPress admin: **Plugins → Installed Plugins → Behavioral Recommendation Topics → Activate**.
3. **Ensure** your theme calls `wp_footer()` before `</body>` so the banner and scripts are injected properly.

---

## Usage

1. **Visit** your site’s blog index (home page or `post` archive).
2. **Consent Banner:** A banner appears at the bottom asking for cookie consent.

   * **Accept:** enables personalized ordering.
   * **Decline:** disables personalization (banner hides, default order).
3. **Browse** posts tagged **AI**, **Marketing**, or **Engineering** to build your profile.
4. **Return** to the blog index (or refresh)—posts in your top topics will be displayed first.

---

## Plugin Files

* **br-topic-subscriber.php**
  Main plugin file: registers hooks, enqueues assets, outputs the consent banner, and reorders the main query.
* **br-consent.js**
  Frontend script: handles consent interactions, cookie management, and view tracking.

---

## Customization

* **Topics of Interest:** By default the plugin tracks the `post_tag` slugs `ai`, `marketing`, and `engineering`. To adjust: edit the `$topics_of_interest` array in `br_enqueue_assets()`.
* **Banner Styling:** Modify the inline CSS in `br_enqueue_assets()` or override via your theme’s stylesheet targeting `#br-consent-banner`.
* **Cookie Expiration:** Change the `cookieExpiry` value (default 30 days) in the `wp_localize_script()` data or in the JS.

---

## Troubleshooting

* **Banner not showing:** Ensure the plugin is active, `wp_footer()` is present, and no caching plugin is preventing the banner HTML.
* **JS errors:** Check your browser console for `BR-Consent` logs. Verify `br-consent.js` is loaded and `brConsentData` is available.
* **Posts not reordering:** Confirm you have viewed at least one post with a tracked tag *after* accepting consent. Refresh the index page to see the weighted ordering.

---

## License

GPL v2 or later.
©2025 Marysol Gurrola.
