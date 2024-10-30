=== COP PDF Attachment Menu ===
Contributors: trevogre 
Donate link: http://www.agreenweb.com/wordpress/cop-pdf-attachment-menu/cop-pdf-attachment-menu-donations/
Tags: PDF, Menu, Attachments, List, widget, sidebar
Requires at least: 3.2.1
Tested up to: 4.0
Stable tag: trunk

Adds a shortcode [pdfmenu] that defaults to displaying an unordered list of all pdf's attached to the current page or post.
Adds a shortcode [attachmentmenu] that defaults to displaying an unordered list of all attachments attached to the current page or post.
== Description ==

Adds a shortcode [pdfmenu] and a widget that defaults to displaying an unordered list of all pdf's attached to the current page or post.

There are options for both altering the query to return a different set of attachments and for formatting the output. 

This plugin should always default to:

<code>
<ul class="cop_pdfmenu">
	<li class="attachment/pdf"><a href="(direct uri to attachment)" target="_blank" title="(attachment title)">(attachment title)</a>
</ul>
</code>

The attachments will be ordered by date in the ascending order by default. 

<h4>Shortcode Options include:</h4>

<ul>
	<li>Change list types ol, li, div (nested divs).</li>
	<li>
		<ul>
			<li>[pdfmenu list_type="ul"]</li>
			<li>[pdfmenu list_type="ol"]</li>
			<li>[pdfmenu list_type="div"]</li>
		</ul>
	</li>
	<li>Set a different class on the container.</li>
	<li>
		<ul>
			<li>[pdfmenu class="differentclass"]</li>
	  	</ul>
	</li>
	<li>Set a different target.</li>
	<li>
		<ul>
			<li>[pdfmenu target="differenttarget"]</li>
		</ul>
	</li>
	<li>Get a list of pdfs from a different parent.</li>
	<li>
		<ul>
			<li>[pdfmenu post_parent="1"]</li>
 		</ul>
 	</li>
 
	<li>Change the number of pdfs to return.  	(currently no paging).</li>
	<li>
		<ul>
			<li>[pdfmenu numberposts="-1"]  (returns all attachments)</li>
 		</ul>
 	</li>
	<li>Change the post_mime_type to query for a different type or multiple types.</li>

	<li>
		<ul>
			<li>[pdfmenu post_mime_type="application/zip"]</li>
 		</ul>
 	</li>

    <li>Force download of the attachments</li>

	<li>
		<ul>
            <li>[pdfmenu dowload="all"] (force download of all listed attachments)</li>
			<li>[pdfmenu dowload="application/pdf"]  (force download of a single mime type)</li>
 		</ul>
 	</li>
</ul>
Widget Options include all of the shortcode options and add some selectors for altering the query and output.
 
== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==


<strong>How can I style the mime type classes that are now output?</strong>

You will now see <code><li class="application/pdf"></code> (or your selected mime type) in the code. But you can't type the / in your stylesheet!

In order to refer to this style you need to change the / to \2f in your classname.

So it looks like 
<code>
.application\2fpdf { 

}
</code>
<i>That solution reduces that amount of code required to output the class and only sligthly increases the complexity of styling. It is the least code approach rather than the most user-friendly approach. Which I think is better in this case.</i>


More Questions Please.

<a href="http://www.agreenweb.com/wordpress/cop-pdf-attachment-menu/cop-pdf-attachment-menu-donations/">Questions / Donations</a>
== Screenshots ==


== Changelog ==

<h4>0.1.1</h4>
<ul>
<li>First WordPress 4.0 testing</li>
<li>Added the first version of paging to the shortcode and widget functionality so that you can set numberofposts and your attatchments will show page navigation within the page.
<ul>
<li>This should fix but not necessarily modify the previous default behavior as it appears that the previous version was output the page numbers but they were not functioning.</li>
<li>Paging only works appropriately with a single instance of paging per page. Multiple instances will all navigate between pages together.</li>
</ul>
</li>
<li>Added the [attachments], and [attachmentmenu] shortcode. I haven't checked to see if [attachments] creates conflicts with other plugins. This has the same functions but it defaults to listing all mime types rather than just pdf files. Use with caution, this may be renamed if I discover I'm overlapping with another plugin. [attachmentmenu] is more in line with future plans I have and will should be safer. </li>
<li>Cleaned up extract code from the shortcode.</li>
<li>Added a span of class="sep" to the | in the paging so that it can be styled but still have a default appearance that is functional.</li>
</ul>

<h4>0.1.0</h4>
<ul>
<li>Added download="(mime type)" and download="all" which converts links to forced download links. Allows other types (all types not tested).</li>
<li>Widget also allows you to set the mime type to force downloads</li>
<p>Thanks to <a href="http://wordpress.org/support/profile/sallydeford">sallydeford</a> for suggesting forced download links as an option. </p>
</ul>

<h4>0.0.9.8</h4>
<ul>
<li>Just updating things for the plugin page.</li>
</ul>

<h4>0.0.9.7</h4>
<ul>
<li>IMPORTANT Change. The widget (not the shortcode) was showing all pdf files with the default settings instead of just the attachments to the current post. Should be working now.
</li>
</ul>

<h4>0.0.9.6</h4>

<ul>
<li>Fixed error with shortcode showing all types by default<br/>
<p>Thanks to <a href="http://wordpress.org/support/profile/sallydeford">sallydeford</a> for pointing out the issue. </p>
</li>
</ul>


<h4>0.0.9.5</h4>

<ul>
<li>Added options to display captions and descriptions.</li>
<li>style captions with .the_caption </li>
<li>style description with .the_description </li>
</ul>

<h4>0.0.9.4</h4>

<ul>
<li>Minor Fixes to new features. (Was defaulting in the widget to "all types"</li>
<li>Added localization functions.</li>
</ul>

<h4>0.0.9.3</h4>

<ul>
<li>Added [pdfmenu all_types='true'] to the shortcode.</li>
<li>Added and all types checkbox to the widget.</li> 
<li>All items have thier mime type as a class (ex. class="application/pdf").</li>
</ul>

<h4>0.0.9.2</h4>

<ul>
<li>Adding the mime type as a class to each list item ( ex. class="application/pdf" ). Would have added it to the container but I wanted to be able to later add the output and styling of attachments of multiple mime types in a single list. </li>
<li>Changed basic ownership details of the plugin to point to my current site <a href="http://www.agreenweb.com/" target="_blank">www.agreenweb.com</a>. </li>
</ul>


<h4>0.0.8</h4>

<ul>
<li>Minor changes to the widget editor</li>
</ul>

<h4>0.0.7</h4>

<ul>
<li>Added ordering options to the widget settings.</li>
</ul>
<h4>0.0.6</h4>

<h4>Shortcode</h4>
	
Added orderby and order to the shortcode options for reordering.

<h4>defaults are:</h4>
<ul>
<li>orderby = "date"</li>
<li>order = "ASC"</li>
</ul>
		
<h4>In Progress</h4>
<ul>
	<li>Started adding paging.</li>
		<ul>
		<li>Add offset="x" or paged="x" with a numberposts="(number)" that is not zero to the shortcode.</li> 
		<li>In order to get another set of links.</li>
		<li>There is not yet any navigation.</li>
		</ul>
	
</ul>

<h4>Widget</h4>

Added an option to add a title.

Did some debugging.

<h4>0.0.5</h4>

Added a check requiring php version 5.2.1 on install.
Moved more code into classes.
		
<h4>0.0.4</h4>

Added custom widget class. No change to function.

<h4>0.0.3</h4>

Improved the widget options.

<h4>0.0.2</h4>

Added basic widget that functions the same as the shortcode. 

<h4>0.0.1</h4>

Initial Version
