wp-smart-honeypot
=================

WordPress plugin to reduce comment spam with a smarter honeypot.


###New Comment Template Required###

The trickiest thing here is make sure the comment form is done in the
new style in your comment form. Since this plugin rearranges form
fields, it requires the newer comment template.
http://codex.wordpress.org/Function_Reference/comment_form


###Trackback Spam###
The website still gets trackback spam. That's harder to figure out a
solution, because a trackback is sort of meant to be done by computers.
Really the only solution for trackback spam is to turn off trackbacks.
This plugin is not intended to stop trackback spam.


###Installation###

1. Go to the release page: https://github.com/freak3dot/wp-smart-honeypot/releases
2. Download the latest zip file.
3. Rename the zip file to wp-smart-honeypot.zip
4. Go to your WordPress admin panel. Click on Plugin » Add New. Then click the upload tab.
5. Browse for your wp-smart-honeypot.zip and click Install Now.
6. Activate the plugin.


###Notes###
* This plugin may include some form elements related to bootstrap. You may need to modify the PHP before using on your site.
* If you are _not_ using WordPress and would like to see a PHP example of a honeypot form in use. See https://github.com/freak3dot/smart-honeypot/


###Honeypot Concepts###
* Random field names to make it difficult to programatically fill in the fields.
* Use one of the standard field names as the honeypot. I like to use "url". Because, what bot isn't trying to spam a url?
* Mix up the location of the honeypot in the form. Let's prevent bots from always ignoring the n<sup>th</sup> field.
* Expire the form after a certain amount of time. Let's keep bots from resubmitting the same form repeatedly.
* Hide the honeypot field from legitimate users via JavaScript or css. Use a clever clasname if you use css. Bots may readily avoid commone classes like "hide", "hidden", "honeypot".
