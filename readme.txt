=== Redirect Manager ===
Contributors: zubeidhendricks
Tags: redirect, 301, 404, redirect manager, htaccess
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and manage 301/302/410 redirects from one simple screen, and catch 404s before visitors hit them.

== Description ==

Moved a page? Killed an old URL? Redirect Manager lets you set up clean
redirects without touching .htaccess. Add one rule per line, pick the type, done.
It also logs your most frequent 404s so you know exactly what to fix.

    /old-page    | /new-page        | 301
    /promo-2024  | /promo           | 302
    /dead-url    |                  | 410

**Free features**

* 301 / 302 / 307 redirects and 410 "Gone" responses.
* Relative or absolute targets.
* 404 logging (top 50) so you can find and fix broken URLs.

**Pro features**

* Automatic redirect when a post slug changes.
* Regex and wildcard rules.
* Priority support.

== Installation ==

1. Install via Plugins → Add New, or upload the zip.
2. Activate.
3. Go to Settings → Redirect Manager and add your rules.

== Frequently Asked Questions ==

= Does it edit my .htaccess? =

No. Redirects run in PHP on template_redirect, so it works on any server.

== Changelog ==

= 1.0.0 =
* Initial release.
