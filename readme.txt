=== Embed Google Fonts ===
Contributors: adrian2k7,moewe, creabrain
Tags: google fonts, embed, gdpr
Donate link: https://www.moewe.io/
Requires at least: 5.0
Tested up to: 5.3.2
Stable tag: 2.2.0
Requires PHP: 7.0
License: GPL v3
License URI: http://www.gnu.org/copyleft/gpl.html

Embed Google Fonts tries to automatically replace registered Google Fonts from themes and plugin with local versions, directly loaded from your own server.

== Description ==

Embed Google Fonts tries to automatically replace registered Google Fonts from themes and plugins with local versions, directly loaded from your own server.

**Contribute**: [https://github.com/moewe-io/embed-google-fonts](https://github.com/moewe-io/embed-google-fonts)

**Notes**

* The first request might be slow, as fonts are downloaded and cached the first time they are requested.
* This doesn't automatically replaces all your Google fonts with local versions. If a plugin/theme doens't use WordPress wp_enqueue_style it propably won't work.
* Loaded fonts are not optimized, means, the whole font including all subsets is loaded

**Thank you:** Fonts are downloaded using: [https://google-webfonts-helper.herokuapp.com/fonts](https://google-webfonts-helper.herokuapp.com/fonts)

== Frequently Asked Questions ==

= Does this work with every theme and plugin? =

No, themes and plugins must use wp_enqueue_style to load Google Fonts.

= Does it work with WP Fastest Cache? =

If you are using [WP Fastest Cache](https://de.wordpress.org/plugins/wp-fastest-cache/), you should create an exclude CSS rule for "_font.css"

== Upgrade Notice ==

Nothing special

== Screenshots ==

There is no ui or something like this. So no screenshots needed.

== Changelog ==

= 2.2.1 =

* Reverted file name of font.css to _font.css (for simpler WP Fastest Cache exclusion)

= 2.2.0 =

* Minor improvements
* Downloaded fonts will be cleared every 30 days automatically
* Downloaded fonts will be cleared, when "entire cache" is cleared in WP Rocket
* Downloaded fonts will be cleared, when cache is cleared in WP Fastest Cache

= 2.1.0 =

* Improved hoster compatible (used WordPress unzip_file)

= 2.0.6 =

* Fixed bug in family detection

= 2.0.5 =

* Prepared for first public release

= 2.0.4 =

* Fixed missing devanagari subset

= 2.0.3 =

* Fixed missing hebrew subset

= 2.0.2 =

* Fixed download url

= 2.0.1 =

* Uses standard cache folder for the fonts.

= 2.0 =

* Fonts are loaded and cached locally on the fly now. (from https://google-webfonts-helper.herokuapp.com/fonts)

= 1.3.3 =

* Added Work Sans, Karla, Alef, Permanent Marker, Amatic SC, Libre Baskerville, Roboto

= 1.3.2 =

* Improved theme compatibility (Elmastudio)

= 1.3.1 =

* Added Source Sans Pro and Anton

= 1.3.0 =

* Register fonts when needed and not all at the beginning (should improve performance)
* Added Domine

= 1.2.6 =

* Added Vollkorn, Montserrat and Forum

= 1.2.5 =

* Added Muli and Maven Pro

= 1.2.4 =

* Added Poppins and Questrial

= 1.2.3 =

* Improved Avada support
* Added Oswald and Indie Flower

= 1.2.2 =

* Improved filesystem access

= 1.2.1 =

* Added PT Sans

= 1.2 =

* Use filesystem for detecting embedded fonts

= 1.1.6 =

* Added Nunito and Raleway

= 1.1.5 =

* Added some fonts

= 1.1.4 =

* Hopefully finally fixed URL problems

= 1.1.3 =

* Improved url loading

= 1.1.2 =

* Reverted renaming

= 1.1.1 =

* Renamed _font.css to font.css

= 1.1.0 =

* Generic replacement of enqueued fonts

= 1.0.0 =

* Initial release
