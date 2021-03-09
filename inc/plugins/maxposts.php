<?php
/*
Author: itsmeJAY
Year: 2019
Version tested: 1.8.21
Contact and support exclusively in the MyBB.de Forum (German Community - https://www.mybb.de/). 
*/


if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

// Hooks and Functions
$plugins->add_hook('admin_formcontainer_output_row', 'maxposts_add_setting');
$plugins->add_hook('admin_user_groups_edit_commit', 'maxposts_group_update');
$plugins->add_hook('newreply_start', 'maxposts_checkpost');
$plugins->add_hook('forumdisplay_thread_end', 'maxposts_checkpost_error');
$plugins->add_hook('newreply_do_newreply_start', 'maxposts_checkpost_quick');
$plugins->add_hook('newthread_start', 'maxposts_checkpost_thread');

function maxposts_info()
{
    global $db, $lang;
// Sprachdatei laden
    $lang->load("maxposts");

    return array(
        "name" => $db->escape_string($lang->mp_name),
        "description" => $db->escape_string($lang->mp_desc),
        "website" => "https://www.mybb.de/forum/user-10220.html",
        "author" => "itsmeJAY",
        "authorsite" => "https://www.mybb.de/forum/user-10220.html",
        "version" => "1.0",
        "guid" => "",
        "codename" => "maxposts",
        "compatibility" => "18*"
    );
}


function maxposts_install()
{
    global $db, $mybb, $lang;

    if (!$db->field_exists('maxpostsgroup', "usergroups")) {
        $db->query("ALTER TABLE `" . TABLE_PREFIX . "usergroups` ADD `maxpostsgroup` INT( 10 ) NOT NULL DEFAULT '0';");
    }

// Sprachdatei laden
    $lang->load("maxposts");

    $setting_group = array(
        'name' => 'maxposts',
        'title' => $db->escape_string($lang->mp_name),
        'description' => $db->escape_string($lang->mp_desc),
        'disporder' => 5,
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    // Einstellungen

    $setting_array = array(
        // Plugin aktiviert?
        'mp_plugin_enable' => array(
            'title' => $db->escape_string($lang->mp_active),
            'description' => $db->escape_string($lang->mp_active_desc),
            'optionscode' => 'yesno',
            'value' => 1, // Default
            'disporder' => 1
        ),
        'mp_forums' => array(
            'title' => $db->escape_string($lang->mp_forums),
            'description' => $db->escape_string($lang->mp_forums_desc),
            'optionscode' => 'forumselect',
            'value' => -1, // Default
            'disporder' => 2
        ),
        'mp_warning_q' => array(
            'title' => $db->escape_string($lang->mp_warningq),
            'description' => $db->escape_string($lang->mp_warningq_desc),
            'optionscode' => 'yesno',
            'value' => 1, // Default
            'disporder' => 3
        ),
        'mp_warning' => array(
            'title' => $db->escape_string($lang->mp_warning),
            'description' => $db->escape_string($lang->mp_warning_desc),
            'optionscode' => 'textarea',
            'value' => $db->escape_string($lang->mp_warning_text), // Default
            'disporder' => 4
        ),
        'mp_noreplies' => array(
            'title' => $db->escape_string($lang->mp_noreplies),
            'description' => $db->escape_string($lang->mp_noreplies_desc),
            'optionscode' => 'yesno',
            'value' => 1, // Default
            'disporder' => 5
        ),
        'mp_nothreads' => array(
            'title' => $db->escape_string($lang->mp_nothreads),
            'description' => $db->escape_string($lang->mp_nothreads_desc),
            'optionscode' => 'yesno',
            'value' => 0, // Default
            'disporder' => 6
        ),
        'mp_ownthreads' => array(
            'title' => $db->escape_string($lang->mp_ownthreads),
            'description' => $db->escape_string($lang->mp_ownthreads_desc),
            'optionscode' => 'yesno',
            'value' => 0, // Default
            'disporder' => 7
        ),
    );

    // Einstellungen in Datenbank speichern
    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    // Rebuild Settings! :-)
    rebuild_settings();

}

function maxposts_uninstall()
{
    global $db;

    $db->query("ALTER TABLE `" . TABLE_PREFIX . "usergroups` DROP `maxpostsgroup`;");
    $db->delete_query('settings', "name IN ('mp_plugin_enable', 'mp_forums', 'mp_warningq', 'mp_warning', 'mp_noreplies', 'mp_nothreads', 'mp_ownthreads')");
    $db->delete_query('settinggroups', "name = 'maxposts'");

    // Rebuild Settings! :-)
    rebuild_settings();

}

function maxposts_activate()
{
    //Template ergänzen
    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("forumdisplay", "#" . preg_quote('{$header}') . "#i", '{$header}<br/>{$postreached}');
}

function maxposts_deactivate()
{
    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("forumdisplay", "#" . preg_quote('<br/>{$postreached}') . "#i", '');
}

function maxposts_is_installed()
{
    global $db;
    if ($db->field_exists('maxpostsgroup', "usergroups")) {
        return true;
    } else {
        return false;
    }
}


function maxposts_add_setting(&$pluginargs)
{
    global $mybb, $lang, $form, $user;

    if ($pluginargs['title'] == $lang->posting_rating_options && $lang->posting_rating_options) {
        $pluginargs['content'] .= "<tr><td><label> Max-Posts (0 for unlimited) </label><br />";
        $pluginargs['content'] .= "<div class=\"form_row\"><div class=\"user_settings_bit\">" . $form->generate_numeric_field('maxpostsgroup', $mybb->get_input('maxpostsgroup', MyBB::INPUT_INT), $options = array(
                "min" => 0,
                'maxposts' => $updated_group['maxposts'],
                'class' => 'field50'));
        $pluginargs['content'] .= "</td></tr>";
    }
}

function maxposts_group_update()
{

    global $db, $mybb, $cache, $user, $updated_group;

    $updated_group['maxpostsgroup'] = $mybb->get_input('maxpostsgroup', MyBB::INPUT_INT);
}

// Funktion ohne Hook welche in den einzelnen Hooks aufgerufen wird
function stop_post()
{
    global $db, $mybb, $thread;

    $usergroup = (int)$mybb->user['usergroup'];

    $query = $db->simple_select("usergroups", "maxpostsgroup",
        "gid = $usergroup", array("order_by" => 'gid',
            "order_dir" => 'DESC', "limit" => 1));

    $result = $db->fetch_array($query);

    $mpforums = explode(",", $mybb->settings['mp_forums']);

    // ... Überprüfen, ob wir uns in einem Forum befinden, welches in den Plugin-Einstellungen gewählt wurde
    if (in_array($thread['fid'], $mpforums) || $mybb->settings['mp_forums'] == "-1") {

        // ... Überprüfung ob der User in fremden Threads nicht antworten darf
        if ($mybb->settings['mp_noreplies'] == 1) {
            if ($mybb->user['postnum'] >= $result['maxpostsgroup'] && $thread['username'] != $mybb->user['username'] && $mybb->settings['mp_plugin_enable'] == 1 && $result['maxpostsgroup'] != 0) {
                error_no_permission();
            }
        }
        // ... Überprüfung ob der User auch in eigenen Threads nicht antworten darf
        if ($mybb->settings['mp_ownthreads'] == 1) {
            if ($mybb->user['postnum'] >= $result['maxpostsgroup'] && $thread['username'] == $mybb->user['username'] && $mybb->settings['mp_plugin_enable'] == 1 && $result['maxpostsgroup'] != 0) {
                error_no_permission();
            }
        }
    }
}

function maxposts_checkpost()
{
    stop_post();
}

function maxposts_checkpost_error()
{
    global $postreached, $mybb, $db, $thread;

    $usergroup = (int)$mybb->user['usergroup'];

    $query = $db->simple_select("usergroups", "maxpostsgroup",
        "gid = $usergroup", array("order_by" => 'gid',
            "order_dir" => 'DESC', "limit" => 1));

    $result = $db->fetch_array($query);

    $mpforums = explode(",", $mybb->settings['mp_forums']);

    // ... Überprüfen, ob wir uns in einem Forum befinden, welches in den Plugin-Einstellungen gewählt wurde
    if (in_array($thread['fid'], $mpforums) || $mybb->settings['mp_forums'] == "-1") {

        if ($mybb->user['postnum'] >= $result['maxpostsgroup'] && $mybb->settings['mp_plugin_enable'] == 1 && $result['maxpostsgroup'] != 0 && $mybb->settings['mp_warning_q'] == 1) {

            $shortcode_1 = "{postnum}";
            $shortcode_2 = "{allowedposts}";

            $mybb->settings['mp_warning'] = str_replace($shortcode_1, $mybb->user['postnum'], $mybb->settings['mp_warning']);
            $mybb->settings['mp_warning'] = str_replace($shortcode_2, $result['maxpostsgroup'], $mybb->settings['mp_warning']);

            $postreached = "<div class=\"red_alert\" id=\"pm_notice\"><div>" . $mybb->settings['mp_warning'] . "</div></div>";

        }
    }
}

function maxposts_checkpost_quick()
{
    stop_post();
}

function maxposts_checkpost_thread()
{
    global $db, $mybb, $thread;

    $usergroup = (int)$mybb->user['usergroup'];

    $query = $db->simple_select("usergroups", "maxpostsgroup",
        "gid = $usergroup", array("order_by" => 'gid',
            "order_dir" => 'DESC', "limit" => 1));

    $result = $db->fetch_array($query);

    $mpforums = explode(",", $mybb->settings['mp_forums']);

    $fid = $mybb->get_input('fid', MyBB::INPUT_INT);

// ... Überprüfen, ob wir uns in einem Forum befinden, welches in den Plugin-Einstellungen gewählt wurde
    if (in_array($fid, $mpforums) || $mybb->settings['mp_forums'] == "-1") {
        // ... Überprüfung ob der User neue Threads erstellen darf
        if ($mybb->settings['mp_nothreads'] == 1) {
            if ($mybb->user['postnum'] >= $result['maxpostsgroup'] && $mybb->settings['mp_plugin_enable'] == 1 && $result['maxpostsgroup'] != 0) {
                error_no_permission();
            }
        }
    }

}

?>
