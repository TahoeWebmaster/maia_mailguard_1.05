<?php
    /*
     * $Id: settings.php,v 1.1.2.1 2004/09/07 19:29:05 jleaver Exp $
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
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

    // Page subtitle
    $lang['banner_subtitle'] =  "Epostfilter Innstillinger";

    // Table headers
    $lang['header_addresses'] =  "Epost Addresser";
    $lang['header_miscellaneous'] =  "Forskjellige Innstillinger";
    $lang['header_address'] =  "Adresse";
    $lang['header_login_info'] =  "Login Credentials";

    // Text messages
    $lang['text_username'] =  "Brukernavn";
    $lang['text_email_address'] =  "Epost adresse";
    $lang['text_password'] =  "Passord";
    $lang['text_reminders'] =  "Send quarantine påminnelse epost?";
    $lang['text_charts'] =  "Vis grafisk statistikk?";
    $lang['text_yes'] =  "Ja";
    $lang['text_no'] =  "Nei";
    $lang['text_virus_scanning'] =  "Virusskanning";
    $lang['text_enabled'] =  "Enabled";
    $lang['text_disabled'] =  "Disabled";
    $lang['text_quarantined'] =  "Quarantined";
    $lang['text_labeled'] =  "Merket";
    $lang['text_discarded'] =  "Forkastet";
    $lang['text_detected_viruses'] =  "Detekterte virus skal bli...";
    $lang['text_spam_filtering'] =  "Spam Filtrering";
    $lang['text_detected_spam'] =  "Detektert spam skal bli...";
    $lang['text_prefix_subject'] =  "Add a prefix to the subjects of spam";
    $lang['text_add_spam_header'] =  "Add X-Spam: Headers when Score is";
    $lang['text_consider_mail_spam'] =  "Consider mail 'Spam' when Score is";
    $lang['text_quarantine_spam'] =  "Quarantine Spam when Score is";
    $lang['text_attachment_filtering'] =  "Attachment Type Filtering";
    $lang['text_mail_with_attachments'] =  "Mail with dangerous attachments should be...";
    $lang['text_bad_header_filtering'] =  "Bad Header Filtrering";
    $lang['text_mail_with_bad_headers'] =  "Mail med bad headers skal bli...";
    $lang['text_settings_updated'] =  "Dine mailfilter innstillinger har blitt oppdatert.";
    $lang['text_address_added'] =  "Adresse %s har blitt linket til din konto.";
    $lang['text_login_failed'] =  "Autentisering feilet for '%s'.";
    $lang['text_primary'] =  "Standard Adresse";
    $lang['text_no_addresses_linked'] =  "Ingen adresser har blitt linket til denne kontoen.";
    $lang['text_new_primary_email'] =  "%s er nå din standard epost adresse.";
    $lang['text_language'] =  "Vis Språk";
    $lang['text_charset'] =  "Vis Språksett";
    $lang['text_spamtrap'] =  "Er dette en spam-felle konto?";
    $lang['text_auto_whitelist'] =  "Legg til sendere av reddet epost til din whitelist?";
    $lang['text_items_per_page'] =  "Epostmeldinger som skal vises per side?";
    $lang['text_new_login_name'] =  "Nytt Brukernavn";
    $lang['text_new_password'] =  "Nytt Passord";
    $lang['text_confirm_new_password'] =  "Bekreft Nytt Passord";
    $lang['text_login_name_empty'] =  "Et brukernavn er påkrevd.";
    $lang['text_login_name_not_allowed'] =  "Brukernavn kan ikke starte med '@'.";
    $lang['text_password_empty'] =  "Et passord og dens bekreftelse må bli angitt.";
    $lang['text_password_mismatch'] =  "Passordet og bekreftelsen stemmer ikke overens.";
    $lang['text_login_name_exists'] =  "Brukernavnet du ønsket er allerede i bruk av en annen bruker.";
    $lang['text_password_updated'] =  "Ditt passord har blitt byttet.";
    $lang['text_credentials_updated'] =  "Ditt brukernavn og passord har blitt byttet.";

    // Buttons
    $lang['button_add_email_address'] =  "Legg til epost adresse";
    $lang['button_reset'] =  "Resett";
    $lang['button_update_misc'] =  "Oppdater forskjellige innstillinger";
    $lang['button_update_address'] =  "Oppdater denne addressinnstilling";
    $lang['button_update_all_addresses'] =  "Oppdater ALLE adresseinnstillinger";
    $lang['button_make_primary'] =  "Sett standard";
    $lang['button_change_login_info'] =  "Oppdater innlogingsinnstillinger";

    // Links
    $lang['link_settings'] =  "Tilbake til dine innstilinger side";
?>
