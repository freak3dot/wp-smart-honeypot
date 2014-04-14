wp-smart-honeypot
=================

Wordpress plugin to reduce comment spam with a smarter honeypot.


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
3. Rename the zip for to wp-smart-honeypot.zip
4. Go to your WordPress admin panel. Click on Plugin » Add New. Then click the upload tab.
5. Browse for your wp-smart-honeypot.zip and click Install Now.
6. Activate the plugin.


###Note###
This plugin may include some from elements related to bootstrap. You may need to modify the PHP before using on your site.


###Honeypot Concepts###
More information about the concepts at work here: http://www.smartfile.com/blog/captchas-dont-work-how-to-trick-spam-bots-with-a-smarter-honey-pot/
