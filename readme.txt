=== Plugin Name ===
Contributors: lyzadanger
Tags: loop,custom loop,query_posts,template,theme,cms,developer
Requires at least: 2.8.4
Tested up to: 2.8.5
Stable tag: 0.3

Quickly code custom loops with re-usable templates and useful variables. Easier custom loops for theme developers.

== Description ==

Lyza Loop is a WordPress plugin for theme developers and template-savvy folks that aims to:

* Dramatically shorten the time required to code custom loops
* Modularize custom loop markup using what I hope is a straightforward templating process
* Provide convenient batching variables
* Respect the context in which the custom loop is called and put everything back the way it was (e.g. global $wp_query and $post objects)
* Reduce tedious and duplicated code for custom loops

There are two parts to the `lyza_loop()` function:

   1. Finding posts (or pages): extending `query_posts()` — `lyza_loop()` takes any argument WordPress’ own query_posts() takes
   2. Rendering relevant posts or pages: using “loop templates.” A loop template is a PHP file with markup (and logic) you’d like to use for each post/page in the loop.

Read more on the [plugin home page](http://www.lyza.com/lyza-loop "Lyza Loop WordPress plugin home and documentation")

== Installation ==

*Please note: This plugin requires PHP 5--it uses some of PHP's better object-oriented programming support introduced in PHP 5. If there is great hue and cry, I might consider making it PHP 4 compatible.*

1. Unzip, then upload the `lyza-loop` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Update defaults on the Settings -> Lyza Loop screen, if desired.
1. See [Lyza Loop documentation](http://www.lyza.com/lyza-loop-documentation) to get started quickly!

== Frequently Asked Questions ==

Please see the [Lyza Loop FAQ page](http://www.lyza.com/lyza-loop/lyza-loop-faq/)!

== Screenshots ==

1. Lyza Loop Settings admin screen.
2. Example of theme using a combination of Lyza Loop loops and loop templates. 

== Changelog ==

= 0.3 =
* Added configuration option for suppressing sticky posts.
* Added code to set `$wp_query->in_the_loop` to `true` during `lyza_loop()` processing.

= 0.2 =
* Fixed a couple of typos in the constructor function.
* Fixed bug in which `post__not_in` argument could get ignored if `exclude_repeats` is set to true (or the plugin is configured to exclude repeats by default).
* Updated to handle sticky tags appropriately (i.e. not return them at the top of every query).

= 0.1 = 
* Initial release.