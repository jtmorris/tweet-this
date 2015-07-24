=== Tweet This ===
Contributors: jtmorris
Donate link: http://tweetthis.jtmorris.net/
Tags: twitter, tweet, social, sharing
Requires at least: 3.9
Tested up to: 4.2.2
Stable tag: 1.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tweet This offers easily embedded, stylish tweetable content boxes in your
posts and pages. Get your visitors sharing on Twitter today!


== Description ==
All the pros agree. Sharing your content on Twitter is an immensely powerful, and
essential, marketing tool for modern websites and blogs.

But do you know what is 100 times more effective than you sharing your stuff?
Your *readers* sharing it!  Not only does word of mouth vastly increase your
market reach, but your authority and the power of your message explodes
exponentially.

= Getting Readers Active =
But there's a problem.  The standard "share" methods are impersonal
and difficult for your readers.  Those share buttons at the bottom of your page?
They don't send anything inspiring!  Just a lifeless message and ugly link.
What you need is Tweet This!

Tweet This is the perfect tool for encouraging your readers to share.  You get to
write a short message.  This message gets embedded into your post or page in an
attractive manner.  When a reader clicks your embedded content, they are given an
opportunity to send the message as a tweet along with a link back to your post.

All you have to do is pick your most engaging quotes or content, load them into a
Tweet This box, and watch your readers spread it to the far corners of the globe!




= How Does It Work? =
Tweet This works using shortcodes.  Simply use the shortcode to embed your text
anywhere you like.  When viewed, the shortcode displays either a beautiful box containing
your message, as well as subtle encouragement for your readers to tweet it, or a simple
inline link.  It's your choice how to display it.

When they decide to tweet it and click the box/link, the message will automatically be filled into
 a Twitter post box for them, as well as a link back to your content and your Twitter account if so desired.

Check out the [screenshots](./screenshots) to get a better idea of how it works!


Tweet This is immensely customizable.  You can change the look of your Tweet
This boxes.  Setup the use of shortlinks in place of long URLs.  And much more!


== Installation ==
= Using WordPress.org's Plugin Repository (recommended) =
1. Search for "Tweet This" in your WordPress "Add New" plugin section of your dashboard.
1. Install and activate the "[Tweet This](http://wordpress.org/plugins/tweetthis/)" plugin by John Morris.


= Manually =
1. Download the latest version of Tweet This and upload the `tweet-this.zip` file
in the Add New plugin section of your dashboard.
1. Activate the plugin through the "Plugins" menu in your WordPress admin section.


= From Source =
1. Visit the plugin's [GitHub repository](https://github.com/jtmorris/tweet-this).
1. Select the branch you want to download (leaving the default master branch is highly recommended).
1. Click the download ZIP button on the lower right side of the page.
1. Upload the contents of the tweet-this-**branch name** directory to a directory
named 'tweet-this' in your WordPress site's './wp-content/plugins/' directory.
1. Visit your WordPress site's Plugins menu in the admin section, and activate the newly listed
Tweet This plugin.


== Frequently Asked Questions ==
= I'm not sure how to use this plugin. Where can I get help? =
Usage guides and information for this plugin are available on the [plugin's website](http://tweetthis.jtmorris.net). 
I recommend beginning with the [Getting Started Guide](http://tweetthis.jtmorris.net/posts/using-tweet-this/) 
You can also find links to other helpful articles on the plugin's settings page in your WordPress admin.

I strive to make this plugin easy to use.  But, it is easy for me, as the plugin developer, to miss something.
Feedback from users if *very* helpful in improving this product.  If something is confusing, difficult, or illogical,
let me know along with any suggestions for making it better.  You can use the support forum on WordPress.org,
or you can find other methods for contacting me [here](http://tweetthis.jtmorris.net/contact/).

= Can I include graphical content in tweets? =
Sort of.  Twitter does not allow the posting of images on behalf of other users, like this
plugin does for text.  However, there are a few tricks you can use to work around this and
get graphical content included.  There is a detailed guide for how to do this on the plugin's website:
[Including Graphics and Images in a Tweet](http://tweetthis.jtmorris.net/posts/including-graphics-and-images-in-a-tweet/)

== Screenshots ==
1. Shortcode creator for WordPress' TinyMCE editor.
2. Result of shortcode from first screenshot using default theme.
3. Result of shortcode from first screenshot using dark theme.
4. Send tweet popup seen by users when they "Tweet This."
5. Settings page. Lots of customization options available.




== Changelog ==
= 1.5.2 =
* Fix jQuery UI theme scope issues introduced in last update.
* Added "Insert Shortcode Behavior" setting which allows users to choose to copy and paste resultant shortcode from Shortcode Creator dialog rather than have it automatically inserted. Serves as a last resort fallback for users reporting plugin conflicts (notably with OptimizePress), and those with the dreaded tinyMCE == undefined bug that keeps persisting.
= 1.5.1 =
* Changed behavior of "Disable URLs?" setting.  This now only stops automatic URLs.  Overridden URLs will still display.
* Restricted jQuery UI theme scope to Tweet This content instead of globally in dashboard.
= 1.4.0 =
* Added editor button placement options to advanced settings.
* Preliminary fix for editor==null exception.
* Manual fallback if automatic shortcode insertion from the shortcode creator fails.
* Bug & typo fixes.
= 1.3.3 =
* Fix detection and usage of shortlinks created by the Shortn.It plugin.
= 1.3.2 =
* Fix wide button bug in WordPress editor's Text tab. (https://wordpress.org/support/topic/this-plug-in-has-messed-up-my-websites-edit-functions-after-the-last-update?replies=4)
= 1.3.1 =
* Fix jQuery dialog loading behind overlay (https://wordpress.org/support/topic/plugin-not-workinggrey-page).
= 1.3.0 =
* Massive shortcode creator dialog box retooling. Removed the intermittently problematic dependence on TinyMCE's window manager.
* Fixed broken removal of "hidden URLs" on a per-shortcode basis.
* Improved error handling and reporting.
* Visual enchancements.
* Performance improvements.
= 1.2.3 =
* Further refinements to error reporting and editor bug workarounds from previous updates.
* Fix excess spaces in resultant Tweets when no defaults set.
* Fix order of items in shortcode creator dialog box to match resultant tweet.
= 1.2.2 =
* Further refinements to error reporting.
* Additional workarounds for rare WordPress editor bug.
= 1.2.1 =
* Add workaround for rare WordPress editor bug where it doesn't provide arguments to dialog box.
* Improve any error reporting when the aforementioned bug is encoutered and eliminate JavaScript exceptions.
* Fix unnecessary and undesired hard coding of default shortcode parameters when using shortcode creation dialog box.
* Fix Tweet This box mode display problem for some themes.
* Code cleanup.
= 1.2.0 =
* Adds hidden URL feature. Similar to hidden hashtags, but for URLs.  Now you can specify a link that will be appended to your user's tweets, but does not display in the Tweet This box.
* Cleans up and reogranizes the shorcode creator dialog box.
* Code cleanup.
= 1.1.9 =
* Fix SSL/HTTPS security errors due to unsecure font imports.
= 1.1.8 =
* Add requested feature: hidden hashtags. Now you can specify hashtags that will be appended to your tweet when tweeted, but do not show to the user.  Just like the Twitter handles and URL, but with hashtags.
* Improve the behavior of the "remove _____" checkboxes. Before, they only affected the preview in the shortcode creation dialog box.  Now, they affect the tweet users will send.  This behavior is far more logical and useful.  It will not be applied retroactively.  It will only work on new Tweet This shortcodes.
* Fix some code styling and less than optimal syntax.
= 1.1.7 =
* Fix support for older versions of PHP by removing usage of PHP 5.3+ only function array_merge().
= 1.1.6 =
* Added option for custom alt attribute for Twitter icon (user request).  New setting is located in "Advanced Settings" section in Tweet This settings page.
= 1.1.5 =
* Fix plugin update.
= 1.1.4 =
* Fix broken shortcode creation dialog.
= 1.1.3 =
* Fix layout conflict with some themes.
= 1.1.2 =
* Fix plugin update errors.
= 1.1.1 =
* Restored missing setting "Remove Byline"
= 1.1.0 =
* Added new inline link capability.
* New shortcode creator dialog box options.
* Settings page tidying.
* Three new themes.
= 1.0.7 =
* Added the ability to remove auto-generated URL from post completely by default, and on a case-by-case basis.
= 1.0.6 =
* Fix character encoding issues on some versions of PHP.
= 1.0.5 =
* Implement workaround for obscure jQuery bug in Firefox.
= 1.0.4 =
* Fix plugin update errors.
= 1.0.3 =
* Fix plugin update errors.
* Code cleanup.
= 1.0.2 =
* Plugin listing improvements.
= 1.0.0 =
* Initial version. No changes to report.


== Upgrade Notice ==
= 1.4.0 =
If you've experienced issues with the shortcode creator dialog box, for example, nothing happens when the button is clicked, be sure to update!
= 1.1.0 =
NEW FEATURES & THEMES: New inline link capability, shortcode creator options, and three new themes.
= 1.0.4 =
If plugin update fails for any reason, try manually activating the plugin!
= 1.0.0 =
It's the awesome first version!
