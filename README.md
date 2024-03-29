# Sid ... THE WordPress Cookiebar

Custom message cookiebar.

## Installation

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

## Settings

Once activated you'll find the 'Cookiebar' settings page listed in the submenu of 'Settings'.

1. Enter the content of the cookiebar (multi-language-ready<sup>1</sup>).
2. Set confirmation/rejection UI (text and type).
3. Define cookiebar style (font size, colors, position).
4. UX

![image](assets/screenshot-1.jpg)

<sub><sup><sup>1</sup> Compatible with [Bogo](https://de.wordpress.org/plugins/bogo/), [Polylang](https://de.wordpress.org/plugins/polylang/), [WP Multilang](https://wordpress.org/plugins/wp-multilang/) and all others<sup>2</sup> </sup></sub><br />
<sub><sup><sup>2</sup> Edit the list of languages with filter hook (`'sid_get_available_languages'`) see [Usage PHP](https://github.com/artcomventure/wordpress-plugin-cookiebar#php) </sup></sub>

## Usage

### Javascript

```javascript
// do stuff the moment cookiebar is confirmed
document.body.addEventListener( 'sid-accepted', function() {
    // ...
}, false );

if ( typeof Sid === 'undefined' || Sid.accepted ) {
    // do stuff if cookiebar is not in use or already confirmed
}

// do stuff the moment cookiebar is rejected
document.body.addEventListener( 'sid-declined', function() {
    // ...
}, false );

if ( typeof Sid === 'undefined' || Sid.declined ) {
    // do stuff if cookiebar is not in use or already declined
}
```

### PHP

```php
if ( !function_exists( 'sid_is_accepted' ) || sid_is_accepted() ) {
    // do stuff if cookiebar is confirmed
}

if ( !function_exists( 'sid_is_declined' ) || sid_is_declined() ) {
    // do stuff if cookiebar is declined
}

// edit the list of languages with filter hook
add_filter( 'sid_get_available_languages', 'filter_languages' );
function filter_languages ( $languages ) {
    // locale => 'native name'
    return $languages;
} 
```

## Plugin Updates

Although the plugin is not _yet_ listed on https://wordpress.org/plugins/, you can use WordPress' update functionality to keep it in sync with the files from [GitHub](https://github.com/artcomventure/wordpress-plugin-cookiebar).

**Please use for this our [WordPress Repository Updater](https://github.com/artcomventure/wordpress-plugin-repoUpdater)** with the settings:

* Repository URL: https://github.com/artcomventure/wordpress-plugin-cookiebar/
* Subfolder (optionally, if you don't want/need the development files in your environment): build

_We test our plugin through its paces, but we advise you to take all safety precautions before the update. Just in case of the unexpected._

## Questions, concerns, needs, suggestions?

Don't hesitate! [Issues](https://github.com/artcomventure/wordpress-plugin-cookiebar/issues) welcome.
