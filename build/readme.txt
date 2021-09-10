=== Sid aka Cookiebar ===

Contributors:
Donate link:
Tags: Cookiebar, DSGVO
Requires at least:
Tested up to:
Stable tag:
License: MIT
License URI: https://github.com/artcomventure/wordpress-plugin-cookiebar/blob/master/LICENSE

...

== Description ==

Custom message cookiebar.

== Installation ==

1. Upload files to the `/wp-content/plugins/` directory of your WordPress installation.
  * Either [download the latest files](https://github.com/artcomventure/wordpress-plugin-cookiebar/archive/master.zip) and extract zip (optionally rename folder)
  * ... or clone repository:
  ```
  $ cd /PATH/TO/WORDPRESS/wp-content/plugins/
  $ git clone https://github.com/artcomventure/wordpress-plugin-cookiebar.git
  ```
  If you want a different folder name than `wordpress-plugin-cookiebar` extend clone command by ` 'FOLDERNAME'` (replace the word `'FOLDERNAME'` by your chosen one):
  ```
  $ git clone https://github.com/artcomventure/wordpress-plugin-cookiebar.git 'FOLDERNAME'
  ```
2. Activate the plugin 'Sid' through the 'Plugins' screen in WordPress.
3. **Enjoy**

== Settings ==

Once activated you'll find the 'Cookiebar' settings page listed in the submenu of 'Settings'.

1. Enter the content of the cookiebar (multi-language-ready<sup>1</sup>).
2. Set confirmation UI (text and type).
3. Define cookiebar style (font size, colors, position).
4. UX

<sub><sup><sup>1</sup> Compatible with [Bogo](https://de.wordpress.org/plugins/bogo/), [Polylang](https://de.wordpress.org/plugins/polylang/), [WP Multilang](https://wordpress.org/plugins/wp-multilang/) and all others<sup>2</sup> </sup></sub><br />
<sub><sup><sup>2</sup> Edit the list of languages with filter hook (`'sid_get_available_languages'`) see [Usage PHP](https://github.com/artcomventure/wordpress-plugin-cookiebar== Description ==

== Usage ==

== == Description ==

```javascript
// do stuff the moment cookiebar is confirmed
document.body.addEventListener( 'sid_accepted', function() {
    // ...
}, false );

if ( typeof Sid === 'undefined' || Sid.accepted ) {
    // do stuff if cookiebar is not in use or already confirmed
}
```

== == Description ==

```php
if ( function_exists( 'sid_is_accepted' ) && sid_is_accepted() ) {
    // do stuff if cookiebar is confirmed
}

// edit the list of languages with filter hook
add_filter( 'sid_get_available_languages', 'filter_languages' );
function filter_languages ( $languages ) {
    // locale => 'native name'
    return $languages;
} 
```

== Plugin Updates ==

Although the plugin is not _yet_ listed on https://wordpress.org/plugins/, you can use WordPress' update functionality to keep it in sync with the files from [GitHub](https://github.com/artcomventure/wordpress-plugin-cookiebar).

**Please use for this our [WordPress Repository Updater](https://github.com/artcomventure/wordpress-plugin-repoUpdater)** with the settings:

* Repository URL: https://github.com/artcomventure/wordpress-plugin-cookiebar/
* Subfolder (optionally, if you don't want/need the development files in your environment): build

_We test our plugin through its paces, but we advise you to take all safety precautions before the update. Just in case of the unexpected._

== Questions, concerns, needs, suggestions? ==

Don't hesitate! [Issues](https://github.com/artcomventure/wordpress-plugin-cookiebar/issues) welcome.

== Changelog ==

= 1.1.5 - 2021-05-25 =
**Fixed**

* PHP undefined index warning.

= 1.1.4 - 2021-05-10 =
**Fixed**

* Default CSS.

= 1.1.3 - 2021-03-22 =
**Changed**

* Extend layout settings.

= 1.1.2 - 2020-11-03 =
**Changed**

* Put js in header.

= 1.1.1 - 2020-10-15 =
**Fixed**

* Remove cookiebar gap on `remove()`.

= 1.1.0 - 2020-08-14 =
**Added**

* Compatibility with multi-language plugins.

= 1.0.1 - 2020-08-14 =
**Fixed**

* Locale's confirmation link setting.

= 1.0.0 - 2020-08-14 =
**Added**

* Initial file commit.
