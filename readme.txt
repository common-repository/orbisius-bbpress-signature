=== Orbisius bbPress Signature ===
Contributors: lordspace
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A
Tags: Orbisius,wordpress,wp,plugins,bbpress,plugins,signature,signatures,sig,forum sig,forum signature,discussion,forum,forums,topic,topics,multisite
Requires at least: 2.6
Tested up to: 3.8
Stable tag: 1.0.3
License: GPLv2 or later

This plugin allows your users to have signatures in a bbPress powered forum.

== Description ==

This plugin allows your users to have signatures in a bbPress powered forum. The signature box appears as a separate menu as well as under user's profile page.

= Support =
> Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>
> Please do NOT use the WordPress forums or other places to seek support.

= Benefits / Features =

* Users can use a rich text editor to set their signature
* The signature is available as a separate top level menu as well as under user's profile.
* You can include text, links etc as a signature like you do in a blog post.
* The signature is available for all users even those with Subscriber role.
* Very easy to use.
* Signatures are truncated if they exceed 300 characters.
* Allowed HTML tags are: <p><a><b><strong><br /><span><div><img><li><ol><ul>

= Want to help development of the plugin? =
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A" target="_blank">Donate</a>

= Author =

Svetoslav Marinov (Slavi) | <a href="http://orbisius.com" title="Custom Web Programming, Web Design, e-commerce, e-store, Wordpress Plugin Development, Facebook App Development in Niagara Falls, St. Catharines, Ontario, Canada" target="_blank">Custom Web Programming and Design by Orbisius.com</a>

== Installation ==

= Automatic Install =
Please go to Wordpress Admin &gt; Plugins &gt; Add New Plugin &gt; Search for: Orbisius bbPress Signature and then press install

= Manual Installation =
1. Upload orbisius-bbpress-signature.zip into to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Want to help development of the plugin? =
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A" target="_blank">Donate</a>

= How to Remove The Question Mark Inserted in Every Signature? =
Just paste the following code into your current theme's functions.php
add_filter('orbisius_bbpress_signature_ext_filter_powered_by_public', '__return_false', 10);

= Run into issues or have questions/suggestions? =

Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>
Please do NOT use the WordPress forums or other places to seek support.

== Screenshots ==
1. Shows how the signature will appear in the forum
2. Shows the signature box from top level menu in WordPress administration area
3. Shows the signature box from Edit Profile page
4. Shows the settings page

== Upgrade Notice ==
n/a

== Changelog ==

= 1.0.3 =
* Fixed signature rendering. The plugin was showing only one signature and was overriding other user's signatures.
* Added caching so user's signature is remembered (for the current request) and not retrieved all the time.

= 1.0.2 =
* Fixed: the plugin was outputting powered by text... which was supposed to be shown only in the member area when the user sets the signature text
* Added a nice/cute/small question mark near the signature, which, when hovered shows powered by ...
* Added a WP filter to remove the powered by question mark if desired.

= 1.0.1 =
* fix: removed the plugin updated code was used only in my Pro plugins at http://club.orbisius.com

= 1.0.0 =
* Initial Release
