=== Share Logins Across Multiple Sites ===
Contributors: codexpert, mukto90
Donate link: https://codexpert.io
Tags: share login, share user base, share userbase, share logins, share users, sync users, sync logins, share user login, share user account, synchronize users, SSO, single sign on
Requires at least: 4.0
Tested up to: 5.4
Stable tag: trunk
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Do you run multiple WordPress sites and want to synchronize user activities? E.g. when they log in to any one of your sites, do you want it so that they get logged in to other sites automatically?

== Description ==

Do you run multiple WordPress sites and want to synchronize user activities? E.g. when they log in to any one of your sites, do you want it so that they get logged in to other sites? Automatically?

Well, here we came into the picture!

## Share Logins
### The ultimate solution to synchronize your userbase

This is the plugin you have been waiting for for ages!

Links: [How To Configure](http://bit.ly/share-logins-pro) | [Live Demo](http://bit.ly/share-logins-pro)

### Synchronize User Activities

* Login

When a user logs in to a site, it'll automatically log them in to other sites!

* Logout

When a user logs out from a site, it'll automatically log them out from other sites!

* Activity Log

Keep log of users' all activities. E.g. when they log in or when they log out.

* Create User (Pro)

When a user account is created in a site, the same account will be created in other sites!

* Update Profile (Pro)

When a user profile is updated in a site, the changes made will reflect in other sites!

* Password Reset (Pro)

When a user password is updated in a site, his password will be updated in other sites too!

* Delete User (Pro)

When a user account is deleted from a site, his account will get deleted from other sites as well!

* Connectivity Validation

Either your sites are connected or not can be checked right from your site dashboard.

* Export/Import Users (Pro)

Migrate all your users from one site to another!

### Gives you full control over everything
* You can add or remove `Remote Sites` anytime you want.
* You can choose which of the user activities should be shared to your `Remote Sites`.
* You can choose which of the incoming requests you want to accept.

### Keeps your data secure
* Transactions are made using custom made REST API endpoints.
* You can set your own access token to secure the data that are being synchronized among sites.
* As an extra layer of security, we are using [Ncrypt](https://github.com/mukto90/ncrypt/ "Ncrypt") to encrypt and decrypt the data.

### Made for you
* `WooCommerce` compatible
* `Easy Digital Downloads` compatible
* `Theme My Login` compatible
* `Gravity Forms` compatible
* More in the queue..

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/share-logins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the `Plugins` screen in WordPress
3. Use the `Share Logins` screen to configure the plugin.
4. Add your `Remote Sites` and configure incoming and outgoing requests from the settings page.

== Frequently Asked Questions ==

= How does it work? =

It works based on user's login name or username. That means, if a user logs in to a site and his/her username is johndoe, it'll look for the same username in other site(s) and if found, the user will automatically be logged in!

= What happens if the username isn't found? =

If the username isn't found in other site(s), the login to other sites won't work. No error messages or notifications will be shown.

= How many sites can be synchronized? =

2 Sites. More can be added if you migrate to Pro version.

= Is it safe? =

We hope, yes. You can set your own access token to authorize your sites. Along with this, login data are secured and encrypted.

= My query was not answered here =

We have a separate help section for this. Please take a look here https://help.codexpert.io/docs/share-logins-pro/


== Screenshots ==

1. Adding remote sites
2. Configuring outgoing requests
3. Configuring incoming requests
4. Validation
5. Basic settings
6. Security settings
7. Activity log
8. User export
9. User import
10. Help

== Changelog ==

= 3.0.0 (31.03.2020) =
* [improve] Code rewritten
* [improve] More secure data transaction
* [improve] Better UI

= 2.1.4 (30.11.2019) =
* [improve] Better session handling

= 2.1.3 (23.11.2019) =
* [improve] Destroy session when needed

= 2.1.1 (17.11.2019) =
* [fix] Large export stops working
* [improve] Log sort

= 2.1.0 (09.10.2019) =
* [fix] Bug fixed
* [fix] Warning fixed
* [add] User role selector added

= 2.0.2 (02.10.2019) =
* [fix] Bug fixed

= 2.0.1 (23.09.2019) =
* [fix] Bug fixed

= 2.0 (19.09.2019) =
* [add] Multisite compatible
* [add] User role handling
* [add] User meta handling
* [fix] Bug fixed
* [improve] UI Improved

= 1.30 (21.05.2019) =
* [add] Validator
* [add] Help section
* [improve] Code Improved


= 1.20 (22.03.2019) =
* [improve] Code Improved
* [add] Native List Table implemented for logs

= 1.10 (06.01.2019) =
* [improve] Code Improved
* [improve] CSS and JS minified

= 1.0 (05.12.2018) =
* Initial release

== Upgrade Notice ==