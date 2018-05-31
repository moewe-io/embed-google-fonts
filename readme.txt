=== Embed Google Fonts ===
Contributors: adrian2k7
Tags: quform
Donate link: https://www.moewe.io/
Requires at least: 4.0
Tested up to: 5.0
Stable tag: 2.0.4
License: GPL v3
License URI: http://www.gnu.org/copyleft/gpl.html

Helper plugin for embedding Google fonts, basically developed for us and our customers.

== Description ==

**Use on your own risk**

Helper plugin for embedding Google fonts, basically developed for us and our customers.

Fonts are downloaded using: https://google-webfonts-helper.herokuapp.com/fonts

**Notes**

* This is not optimized in any way
* This doesn't automatically replaces all your Google fonts with local versions. If a plugin/theme doens't use WordPress wp_enqueue_style it propably won't work.

== Changelog ==

= 2.0.4 =

* Fixed missing devanagari subset

= 2.0.3 =

* Fixed missing hebrew subset

= 2.0.2 =

* Fixed downlad url

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
