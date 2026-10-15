Achievement Report Generator
============================

Version: 0.3.0

What it does
------------
Adds a shortcode for an Arabic RTL achievement-report form with a live A4 report preview and a print button. Data stays in the browser and is not saved in WordPress.

Install/update
--------------
1. Back up the existing plugin folder if you have custom edits.
2. In WordPress, deactivate and remove the older Achievement Report Generator plugin.
3. Go to Plugins > Add New Plugin > Upload Plugin and upload the new ZIP.
4. Activate the plugin.
5. Keep using this shortcode in your WordPress/Elementor page:
   [achievement_report_generator]

Logo
----
The plugin looks for the logo in this order:
1. The active theme/child theme: assets/images/logo.png
2. The plugin itself: assets/images/logo.png

For a Hello Elementor child theme, the expected first path is:
wp-content/themes/hello-elementor-child/assets/images/logo.png

The logo is centered before all header text and styled at 142.66 mm wide by 45.55 mm tall for A4 print (contained within that box).

Fixes in 0.3.0
-------------
- The selected month appears in the outlined box underneath the brown period bars; the brown bar says only "خلال شهر".
- The first section cards read visually from left to right: حالة, مستفيداً, مراجعاً.
- The footer is a full-width green banner with phone, email, and websites on the left and the Arabic organization name on the right.
- Print CSS is tuned to keep the logo, statistics, region, and footer on A4 portrait.

Print settings
--------------
Use A4, Portrait, scale 100%, disable browser headers/footers, and enable Background graphics in Chrome's print dialog.

Important
---------
This version does not save reports. If the logo is not displayed, check that logo.png exists at one of the paths above and clear any page/cache plugin cache.
