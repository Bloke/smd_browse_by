<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'smd_browse_by';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.1.2';
$plugin['author'] = 'Stef Dawson';
$plugin['author_uri'] = 'https://stefdawson.com/';
$plugin['description'] = 'Browse by Category (+article Section) in admin interface';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no Ajax)
// 4 = admin+ajax          : only on the admin side (Ajax supported)
// 5 = public+admin+ajax   : on both the public and admin side (Ajax supported)
$plugin['type'] = '3';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '0';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

/** Uncomment me, if you need a textpack
$plugin['textpack'] = <<< EOT
#@admin
#@language en-gb
abc_sample_string => Sample String
abc_one_more => One more
#@language de-de
abc_sample_string => Beispieltext
abc_one_more => Noch einer
EOT;
**/
// End of textpack

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
if (@txpinterface == 'admin') {
	register_callback('smd_browse_by_image', 'image');
	register_callback('smd_browse_by_article', 'list');
	register_callback('smd_browse_by_file', 'file');
	register_callback('smd_browse_by_link', 'link');
}

function smd_browse_by_image($evt, $stp) {
	if ($stp == '' || $stp == 'image_list') {
		$showit = do_list(get_pref('smd_browse_by', 'SMD_ALL'));
		if (in_array('SMD_ALL', $showit) || in_array('image', $showit)) {
			$smd_browse_by_form = form(smd_browse_dropdown('image', $evt), '', '', 'get');
			smd_browse_by_js($smd_browse_by_form);
		}
	}
}

function smd_browse_by_article($evt, $stp) {
	$showit = do_list(get_pref('smd_browse_by', 'SMD_ALL'));
	$showcat = (in_array('SMD_ALL', $showit) || in_array('article', $showit) || in_array('article_cat', $showit));
	$showsec = (in_array('SMD_ALL', $showit) || in_array('article', $showit) || in_array('article_sec', $showit));

	$smd_browse_by_form = (($showcat) ? form(smd_browse_dropdown('article_cat', $evt), '', '', 'get') : '')
		. (($showsec) ? form(smd_browse_dropdown('article_sec', $evt), '', '', 'get') : '');

	if ($showcat || $showsec) {
		smd_browse_by_js($smd_browse_by_form);
	}
}

function smd_browse_by_file($evt, $stp) {
	if ($stp == '' || $stp == 'file_list') {
		$showit = do_list(get_pref('smd_browse_by', 'SMD_ALL'));
		if (in_array('SMD_ALL', $showit) || in_array('file', $showit)) {
			$smd_browse_by_form = form(smd_browse_dropdown('file', $evt), '', '', 'get');
			smd_browse_by_js($smd_browse_by_form);
		}
	}
}

function smd_browse_by_link($evt, $stp) {
	if ($stp == '' || $stp == 'link_list') {
		$showit = do_list(get_pref('smd_browse_by', 'SMD_ALL'));
		if (in_array('SMD_ALL', $showit) || in_array('link', $showit)) {
			$smd_browse_by_form = form(smd_browse_dropdown('link', $evt), '', '', 'get');
			smd_browse_by_js($smd_browse_by_form);
		}
	}
}

// Common js to place each 'browse by' above each area's search form
function smd_browse_by_js($content) {
	$out = doSlash($content);
	$js = <<<EOJS
<script type="text/javascript">
/* <![CDATA[ */
	jQuery(document).ready(function() {
		jQuery(".search-form").before('<div class="smd_browse_by">{$out}</div>');
	});
/* ]]> */
</script>
EOJS;
	echo $js;
}

function smd_browse_dropdown($type, $evt) {
	$browsable = array(
		'article_cat' => array(
			'by'    => smd_browse_by_gTxt('by_cat'),
			'table' => 'txp_category',
			'where' => "type = 'article' AND name != 'root'",
			'step'  => 'list',
			'meth'  => 'categories',
		),
		'article_sec' => array(
			'by'    => smd_browse_by_gTxt('by_sec'),
			'table' => 'txp_section',
			'where' => "name != 'default'",
			'step'  => 'list',
			'meth'  => 'section',
			'sort'  => 'Title asc',
		),
		'image' => array(
			'by'    => smd_browse_by_gTxt('by_cat'),
			'table' => 'txp_category',
			'where' => "type = 'image' AND name != 'root'",
			'step'  => 'image_list',
			'meth'  => 'category',
		),
		'file' => array(
			'by'    => smd_browse_by_gTxt('by_cat'),
			'table' => 'txp_category',
			'where' => "type = 'file' AND name != 'root'",
			'step'  => 'file_list',
			'meth'  => 'category',
		),
		'link' => array(
			'by'    => smd_browse_by_gTxt('by_cat'),
			'table' => 'txp_category',
			'where' => "type = 'link' AND name != 'root'",
			'step'  => 'link_edit',
			'meth'  => 'category',
		),
	);

	if (isset($browsable[$type])) {
		$entry = $browsable[$type];
		$sel = array();

		if ($entry['meth'] == 'section') {
			$sorder = ($entry['sort']) ? ' ORDER BY '.$entry['sort'] : '';
			$section_cat = safe_rows('name, title', $entry['table'], $entry['where'].$sorder);
			foreach ($section_cat as $row) {
				$sel[] = array('name' => $row['name'], 'title' => $row['title']);
			}
		} else if ($entry['meth'] == 'category' || $entry['meth'] == 'categories') {
			$thistype = str_replace('_cat', '', $type);
			$sel = getTree('root', $thistype, '1=1', $entry['table']);
		}
		if ($sel) {
			$use_go = get_pref('smd_browse_by_go_button', 0);
			$val = gps('crit');
			return
				graf($entry['by']
				. pluggable_ui('smd_browse_by_ui', $type, smd_browse_select_input('crit', $sel, $val, true, (($use_go) ? false : true)), $sel,  $val)
				. (($use_go) ? fInput('submit', '', gTxt('go'), 'smallerbox') : '')
				. hInput('event', $evt)
				. hInput('step', $entry['step'])
				. hInput('search_method', $entry['meth'])
				);
		}
	}
	return false;
}

// Frankensteined from the core's selectInput() and treeSelectInput()
function smd_browse_select_input($select_name = '', $array = '', $value = '', $blank_first = false, $onchange = false, $select_id = '', $truncate = 0) {
	$out = array();
	$selected = false;
	$level = 0;

	foreach ($array as $a) {
		if ($a['name'] == 'root') {
			continue;
		}

		extract($a);

		if ($name == $value) {
			$sel = ' selected="selected"';
			$selected = true;
		} else {
			$sel = '';
		}

		$sp = str_repeat(sp.sp, $level);

		if (($truncate > 3) && (strlen(utf8_decode($title)) > $truncate)) {
			$htmltitle = ' title="'.htmlspecialchars($title).'"';
			$title = preg_replace('/^(.{0,'.($truncate - 3).'}).*$/su','$1',$title);
			$hellip = '&#8230;';
		} else {
			$htmltitle = $hellip = '';
		}

		$out[] = n.t.'<option value="'.htmlspecialchars($name).'"'.$htmltitle.$sel.'>'.$sp.htmlspecialchars($title).$hellip.'</option>';
	}

	return n.'<select'.( $select_id ? ' id="'.$select_id.'"' : '' ).' name="'.$select_name.'" class="list"'.
		($onchange == 1 ? ' onchange="submit(this.form);"' : $onchange).
		'>'.
		($blank_first ? n.t.'<option value=""'.($selected == false ? ' selected="selected"' : '').'></option>' : '').
		( $out ? join('', $out) : '').
		n.'</select>';
}

// Plugin-specific replacement strings - localise as required
function smd_browse_by_gTxt($what, $atts = array()) {
	$lang = array(
		'by_cat' => 'Browse by Category',
		'by_sec' => 'Browse by Section',
	);
	return strtr($lang[$what], $atts);
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
h1. smd_browse_by

Browse your articles/images/files/links via a quick dropdown menu of category (or in the case of articles: section as well). Just install, activate, done.

h2. Author / credits

Written by "Stef Dawson":https://stefdawson.com/contact. Original plugin by Steve Lam.

h2. Installation / uninstallation

p(important). Requires TXP 4.4.1+

Download the plugin from either "GitHub":https://github.com/Bloke/smd_browse_by, or the "software page":https://stefdawson.com/sw, paste the code into the Textpattern _Admin>Plugins_ pane, install and enable the plugin.

To uninstall the plugin, delete from the _Admin>Plugins_ page.

Visit the "forum thread":https://forum.textpattern.com/viewtopic.php?id=36784 for more info or to report on the success or otherwise of the plugin.

h2. Configuration

The plugin can be controlled with a few hidden preference values (the smd_prefalizer plugin can help here). Any prefs you create should have the following items set, in addition to the preference-specific name and value given later:

* Visibility: *Hidden*
* Event: *smd_browse*
* User: _your login name_ if you wish the preference to only apply to your login, or leave it empty to apply it for all users

The preferences are:

h3. Browse using

By default you can browse by category on all main content types and additionally by section with articles. If you wish to only offer browsing on certain tabs, set this preference. Comma-separate any of the following values to build the interface to your choosing:

* Name: *smd_browse_by*
* Value:
** _empty_ : none (!) : remove all browsable lists added by the plugin
** *SMD_ALL* : browse on every tab -- this has the same effect as completely removing the preference value
** *article*: browse by category and section lists on the Articles tab
** *article_cat*: browse by category on the Articles tab
** *article_sec*: browse by section on the Articles tab
** *image*: browse by category on the Images tab
** *file*: browse by category on the Files tab
** *link*: browse by category on the Links tab

h3. Go button

By default the select lists submit automatically when the entry is changed. If you wish to add a 'Go' button next to the lists, set this preference:

* Name: *smd_browse_by_go_button*
* Value:
** 0 (auto-submit)
** 1 (Go button)

h2. API

If you wish to alter the output of any of the lists that appear on the screen you may do so by registering a callback to be notified when one or all of the select lists are displayed. Here are the relevant events and steps you can use:

* Event: @smd_browse_by_ui@
* Step: call your plugin at the following times:
** _empty_ : all select lists
** @article_cat@: when the Article category list is displayed
** @article_sec@: when the Article section list is displayed
** @image@: when the Image category list is displayed
** @file@: when the File category list is displayed
** @link@: when the Link category list is displayed

An example:

bc.. if (@txpinterface == 'admin') {
   register_callback('my_custom_browser', 'smd_browse_by_ui', 'image');
}

function my_custom_browser($evt, $stp, $data, $rs, $val) {
   // $evt is always 'smd_browse_by_ui'
   // $stp is the current step
   //    (article_cat, article_sec, image, file, or link)
   // $data holds the fully rendered default select list HTML
   //    so you can search/replace/manipulate it
   // $rs is the raw array containing the list of items
   //    so you can iterate over them
   // $val is the currently chosen value from the list
   // if you return anything from this function,
   //    that will be what is displayed in place of the
   //    default select list
}
# --- END PLUGIN HELP ---
-->
<?php
}
?>