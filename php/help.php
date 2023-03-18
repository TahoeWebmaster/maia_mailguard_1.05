<?php
    /*
     * $Id: help.php 1439 2009-11-17 23:31:04Z dmorton $
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
     *                   David Morton   <mortonda@dgrmm.net>
     * All rights reserved.
     *
     * PREAMBLE
     *
     * This License is designed for users of Maia Mailguard
     * ("the Software") who wish to support the Maia Mailguard project by
     * leaving "Maia Mailguard" branding information in the HTML output
     * of the pages generated by the Software, and providing links back
     * to the Maia Mailguard home page.  Users who wish to remove this
     * branding information should contact the copyright owner to obtain
     * a Rebranding License.
     *
     * DEFINITION OF TERMS
     *
     * The "Software" refers to Maia Mailguard, including all of the
     * associated PHP, Perl, and SQL scripts, documentation files, graphic
     * icons and logo images.
     *
     * GRANT OF LICENSE
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     *
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     *
     * 3. The end-user documentation included with the redistribution, if
     *    any, must include the following acknowledgment:
     *
     *    "This product includes software developed by Robert LeBlanc
     *    <rjl@renaissoft.com>."
     *
     *    Alternately, this acknowledgment may appear in the software itself,
     *    if and wherever such third-party acknowledgments normally appear.
     *
     * 4. At least one of the following branding conventions must be used:
     *
     *    a. The Maia Mailguard logo appears in the page-top banner of
     *       all HTML output pages in an unmodified form, and links
     *       directly to the Maia Mailguard home page; or
     *
     *    b. The "Powered by Maia Mailguard" graphic appears in the HTML
     *       output of all gateway pages that lead to this software,
     *       linking directly to the Maia Mailguard home page; or
     *
     *    c. A separate Rebranding License is obtained from the copyright
     *       owner, exempting the Licensee from 4(a) and 4(b), subject to
     *       the additional conditions laid out in that license document.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
     * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
     * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
     * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
     * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
     * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
     * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
     * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
     * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */

    require_once ("core.php");
    require_once ("authcheck.php");
    require_once ("display.php");
    require_once ("maia_db.php");
    $display_language = get_display_language($euid);
    require_once ("./locale/$display_language/display.php");
    require_once ("./locale/$display_language/db.php");
    require_once ("./locale/$display_language/settings.php");
    require_once ("./locale/$display_language/wblist.php");
    require_once ("./locale/$display_language/viewmail.php");
    require_once ("./locale/$display_language/reportspam.php");
    require_once ("./locale/$display_language/quarantine.php");
    require_once ("./locale/$display_language/help.php");

	require_once ("smarty.php");

    $select = "SELECT admin_email, " .
                     "enable_virus_scanning, " .
                     "enable_spam_filtering, " .
                     "enable_banned_files_checking, " .
                     "enable_bad_header_checking, " .
                     "enable_spamtraps, " .
                     "enable_charts, " .
                     "enable_false_negative_management, " .
                     "enable_stats_tracking, " .
                     "use_icons, " .
                     "reminder_threshold_count, " .
                     "reminder_threshold_size, " .
                     "expiry_period, " .
                     "size_limit, " .
                     "oversize_policy, " .
                     "ham_cache_expiry_period " .
              "FROM maia_config WHERE id = 0";
    $sth = $dbh->prepare($select);
    $res = $sth->execute();
    // if (PEAR::isError($sth)) {
    if ((new PEAR)->isError($sth)) {
        die($sth->getMessage());
    }
    if ($row = $res->fetchrow()) {
    	$admin_email = $row["admin_email"];
        $enable_virus_scanning = ($row["enable_virus_scanning"] == 'Y');
        $enable_spam_filtering = ($row["enable_spam_filtering"] == 'Y');
        $enable_banned_files_checking = ($row["enable_banned_files_checking"] == 'Y');
        $enable_bad_header_checking = ($row["enable_bad_header_checking"] == 'Y');
        $enable_spamtraps = ($row["enable_spamtraps"] == 'Y');
        $enable_charts = ($row["enable_charts"] == 'Y');
        $enable_false_negative_management = ($row["enable_false_negative_management"] == 'Y');
        $enable_stats_tracking = ($row["enable_stats_tracking"] == 'Y');
        $reminder_threshold_count = $row["reminder_threshold_count"];
        $reminder_threshold_size = $row["reminder_threshold_size"];
        $use_icons = ($row["use_icons"] == 'Y');
        $expiry_period = $row["expiry_period"];
        $ham_cache_expiry_period = $row["ham_cache_expiry_period"];
        $size_limit = $row["size_limit"];
        $oversize_policy = $row["oversize_policy"];
    }
    $sth->free();

    $mail_viewer_url = "help.php#mail_viewer" . $sid;

 $lang['help_introduction_2'] = sprintf($lang['help_introduction_2'], ini_get("session.gc_maxlifetime") / 60,
                $lang['menu_logout']);

 $lang['help_stats_1'] = sprintf($lang['help_stats_1'], "<a href=\"stats.php" . $msid . "id=0\">[" . $lang['link_systemwide'] . "]</a>"); 

 $lang['help_stats_2'] = sprintf($lang['help_stats_2'], $lang['array_header']["suspected_ham"],
                "<a href=\"list-cache.php?cache_type=ham" . $sid . "\">[" . $lang['menu_report'] . "]</a>"); 

 $lang['help_stats_3'] = sprintf($lang['help_stats_3'], $lang['array_header']["ham"]); 

 $lang['help_stats_4'] = sprintf($lang['help_stats_4'], $lang['array_header']["fp"],
                "<a href=\"list-cache.php?cache_type=spam" . $sid . "\">[" . $lang['menu_quarantine'] . "]</a>"); 

 $lang['help_stats_5'] = sprintf($lang['help_stats_5'], $lang['array_header']["suspected_spam"],
                "<a href=\"list-cache.php?cache_type=spam" . $sid . "\">[" . $lang['menu_quarantine'] . "]</a>"); 

 $lang['help_stats_6'] = sprintf($lang['help_stats_6'], $lang['array_header']["spam"]); 

 $lang['help_stats_7'] = sprintf($lang['help_stats_7'], $lang['array_header']["fn"],
                "<a href=\"list-cache.php?cache_type=ham" . $sid . "\">[" . $lang['menu_report'] . "]</a>"); 

 $lang['help_stats_8'] = sprintf($lang['help_stats_8'], $lang['array_header']["wl"]); 

 $lang['help_stats_9'] = sprintf($lang['help_stats_9'], $lang['array_header']["bl"]); 

 $lang['help_stats_10'] = sprintf($lang['help_stats_10'], $lang['array_header']["virus"]); 

 $lang['help_stats_11'] = sprintf($lang['help_stats_11'], $lang['array_header']["banned_file"],
                "<a href=\"list-cache.php?cache_type=attachment" . $sid . "\">[" . $lang['menu_quarantine'] . "]</a>"); 

 $lang['help_stats_12'] = sprintf($lang['help_stats_12'], $lang['array_header']["bad_header"],
                "<a href=\"list-cache.php?cache_type=header" . $sid . "\">[" . $lang['menu_quarantine'] . "]</a>"); 

 $lang['help_stats_13'] = sprintf($lang['help_stats_13'], $lang['array_header']["oversized"],
                $size_limit, (($oversize_policy == 'P') ? "accepted" : "rejected")); 

 $lang['help_settings_2'] = sprintf($lang['help_settings_2'], $lang['text_primary'], $lang['button_make_primary']); 

 $lang['help_settings_3'] = sprintf($lang['help_settings_3'], $lang['button_add_email_address']); 

 $lang['help_settings_21'] = sprintf($lang['help_settings_21'], $lang['text_new_login_name'], $lang['text_new_password'],
                $lang['text_confirm_new_password'], $lang['button_change_login_info']); 

 $lang['help_settings_4'] = sprintf($lang['help_settings_4'], $lang['text_reminders'], $reminder_threshold_count,
                $reminder_threshold_size, $expiry_period); 

 $lang['help_settings_5'] = sprintf($lang['help_settings_5'], $lang['text_charts'],
                "<a href=\"stats.php" . $sid . "\">[" . $lang['menu_stats'] . "]</a>"); 

 $lang['help_settings_6'] = sprintf($lang['help_settings_6'], $lang['text_spamtrap']); 

 $lang['help_settings_22'] = sprintf($lang['help_settings_22'], $lang['text_auto_whitelist']); 

 $lang['help_settings_23'] = sprintf($lang['help_settings_23'], $lang['text_items_per_page']); 

 $lang['help_settings_7'] = sprintf($lang['help_settings_7'], $lang['text_language']); 

 $lang['help_settings_9'] = sprintf($lang['help_settings_9'], $lang['text_virus_scanning']); 

 $lang['help_settings_10'] = sprintf($lang['help_settings_10'], $lang['text_detected_viruses'],
                $lang['text_quarantined'], $lang['text_labeled']); 

 $lang['help_settings_11'] = sprintf($lang['help_settings_11'], $lang['text_spam_filtering']); 

 $lang['help_settings_12'] = sprintf($lang['help_settings_12'], $lang['text_detected_spam'],
                $lang['text_quarantined'], $lang['text_labeled']); 

 $lang['help_settings_13'] = sprintf($lang['help_settings_13'], $lang['text_prefix_subject']); 

 $lang['help_settings_14'] = sprintf($lang['help_settings_14'], $lang['text_add_spam_header'],
                $lang['text_consider_mail_spam']); 

 $lang['help_settings_15'] = sprintf($lang['help_settings_15'], $lang['text_consider_mail_spam'],
                $lang['text_quarantine_spam']); 

 $lang['help_settings_16'] = sprintf($lang['help_settings_16'], $lang['text_quarantine_spam'],
                $lang['text_consider_mail_spam']); 

 $lang['help_settings_17'] = sprintf($lang['help_settings_17'], $lang['text_attachment_filtering']); 

 $lang['help_settings_18'] = sprintf($lang['help_settings_18'], $lang['text_mail_with_attachments'],
                $lang['text_quarantined'], $lang['text_labeled']); 

 $lang['help_settings_19'] = sprintf($lang['help_settings_19'], $lang['text_bad_header_filtering']); 

 $lang['help_settings_20'] = sprintf($lang['help_settings_20'], $lang['text_mail_with_bad_headers'],
                $lang['text_quarantined'], $lang['text_labeled']); 

 $lang['help_wblist_3'] = sprintf($lang['help_wblist_3'],
                "<a href=\"wblist.php" . $sid . "\">[" . $lang['menu_whiteblacklist'] . "]</a>",
                $lang['header_whitelist'],
                $lang['header_blacklist'],
                $lang['button_add_to_list'],
                $lang['heading_wblist']); 

 $lang['help_wblist_4'] = sprintf($lang['help_wblist_4'], $lang['button_update'], $lang['heading_wblist']);

 $lang['help_quarantine_2'] = sprintf($lang['help_quarantine_2'], $mail_viewer_url); 

 $lang['help_quarantine_9'] = sprintf($lang['help_quarantine_9'],
                $lang['text_ham'],
                $lang['text_delete'],
                $lang['button_confirm']); 

 $lang['help_quarantine_3'] = sprintf($lang['help_quarantine_3'],
                $mail_viewer_url,
                $lang['text_ham'],
                $lang['text_ham'],
                $lang['button_confirm']); 

 $lang['help_quarantine_4'] = sprintf($lang['help_quarantine_4'],
                $mail_viewer_url,
                $lang['text_ham'],
                $lang['button_confirm']); 

 $lang['help_quarantine_5'] = sprintf($lang['help_quarantine_5'],
                $mail_viewer_url,
                $lang['text_ham'],
                $lang['button_confirm']); 

 $lang['help_quarantine_6'] = sprintf($lang['help_quarantine_6'], $lang['text_ham']); 
 $lang['help_quarantine_7'] = sprintf($lang['help_quarantine_7'], $expiry_period); 

 $lang['help_quarantine_8'] = sprintf($lang['help_quarantine_8'], $lang['button_delete_all_items']); 

 $lang['help_fn_3'] = sprintf($lang['help_fn_3'],
                $mail_viewer_url,
                $lang['link_report']); 

 $lang['help_fn_6'] = sprintf($lang['help_fn_6'], $lang['text_spam'], $lang['button_confirm']); 

 $lang['help_fn_4'] = sprintf($lang['help_fn_4'], $ham_cache_expiry_period); 

 $lang['help_fn_5'] = sprintf($lang['help_fn_5'], $lang['button_delete_all_cached_items']); 

 $lang['help_mail_viewer_1'] = sprintf($lang['help_mail_viewer_1'],
                $lang['link_view_raw'],
                $lang['link_view_decoded']); 

 $lang['help_admin_1'] = sprintf($lang['help_admin_1'],
                "<a href=\"adminhelp.php" . $sid . "\">" . $lang['help_text_adminhelp'] . "</a>"); 

 $lang['help_assistance_1'] = sprintf($lang['help_assistance_1'],
                "<a href=\"mailto:" . $admin_email . "\">" . $admin_email . "</a>"); 


 $lang['help_credits_1'] = sprintf($lang['help_credits_1'],
                "<a href=\"http://www.maiamailguard.com/\">Maia Mailguard</a>",
                "<a href=\"mailto:rjl@renaissoft.com\">Robert LeBlanc</a> &amp; <a href=\"mailto:mortonda@dgrmm.net\">David Morton</a>",
                "<a href=\"http://www.ijs.si/software/amavisd/\">AMaViS Mail Virus Scanner (amavisd-new)</a>",
                "<a href=\"http://www.spamassassin.org/\">SpamAssassin</a>",
                "<a href=\"http://us.mcafee.com/root/package.asp?pkgid=100\">McAfee VirusScan</a>",
                "<a href=\"https://www.clamav.net/\">Clam Antivirus</a>",
                "<a href=\"http://www.f-prot.com/\">F-Prot Antivirus</a>"); 


 $smarty->assign("is_an_administrator",is_an_administrator($uid));
 $smarty->assign("enable_banned_files_checking", $enable_banned_files_checking); 
 $smarty->assign("enable_virus_scanning", $enable_virus_scanning); 
 $smarty->assign("enable_bad_header_checking", $enable_bad_header_checking); 
 $smarty->assign("enable_spam_filtering", $enable_spam_filtering); 
 $smarty->assign("enable_false_negative_management", $enable_false_negative_management);
 $smarty->assign("auth_method", $auth_method); 
 $smarty->assign("use_icons", $use_icons);
 $smarty->assign("enable_stats_tracking", $enable_stats_tracking);
 $smarty->assign("lang",$lang);
 $smarty->display("help.tpl");
?>
