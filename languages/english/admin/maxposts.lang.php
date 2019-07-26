<?php
/*
Author: itsmeJAY
Year: 2019
Version tested: 1.8.21
Date: 25.07.2019
Contact and support exclusively in the MyBB.de Forum (German Community - https://www.mybb.de/). 
*/

$l['mp_name'] = "Max posts per usergroup";
$l['mp_desc'] = "Users may only write a limited number of posts within a user group. The value can be defined for each group.";
$l['mp_active'] = "Activate Plugin";
$l['mp_active_desc'] = "Should the plugin be activated or deactivated?";
$l['mp_forums'] = "Select the forums";
$l['mp_forums_desc'] = "In which forums should the plugin be active?";
$l['mp_warningq'] = "Warning / Show information";
$l['mp_warningq_desc'] = "Should a warning / hint be displayed in the forumdisplay template? If yes, please define the text below.";
$l['mp_warning'] = "Warning text";
$l['mp_warning_desc'] = "Which text should be displayed as a warning? <br> (Use {postnum} for the number of posts the user has already made.)<br/>(Use {allowedposts} for the maximum number of posts the user can write in his user group.)";
$l['mp_warning_text'] = "You have already written {postnum} posts from {allowedposts} allowed posts. You can only create threads and reply to your own threads. You can no longer reply to other topics.";
$l['mp_noreplies'] = "When the limit is reached: Lock posts in external threads";
$l['mp_noreplies_desc'] = "Select this option if you do not want the user to be able to post to other threads once the maximum number of posts has been reached. They can still post to their own threads or create threads.";
$l['mp_nothreads'] = "When the limit is reached: Lock thread creation";
$l['mp_nothreads_desc'] = "Select this option if you do not want the user to be allowed to create topics after the maximum number of posts has been reached.";
$l['mp_ownthreads'] = "When the limit is reached: Lock posts in own topics from the user";
$l['mp_ownthreads_desc'] = "Select this option if you do not want the user to be allowed to create posts in their own threads (threads already created by the user) once the maximum number of posts has been reached.";
?>