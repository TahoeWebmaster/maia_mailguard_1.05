#!/usr/bin/perl

# $Id: load-sa-rules.pl 1528 2011-05-31 10:09:15Z rjl $

########################################################################
# MAIA MAILGUARD LICENSE v.1.0
#
# Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
#                   David Morton   <mortonda@dgrmm.net>
# All rights reserved.
#
# PREAMBLE
#
# This License is designed for users of Maia Mailguard
# ("the Software") who wish to support the Maia Mailguard project by
# leaving "Maia Mailguard" branding information in the HTML output
# of the pages generated by the Software, and providing links back
# to the Maia Mailguard home page.  Users who wish to remove this
# branding information should contact the copyright owner to obtain
# a Rebranding License.
#
# DEFINITION OF TERMS
#
# The "Software" refers to Maia Mailguard, including all of the
# associated PHP, Perl, and SQL scripts, documentation files, graphic
# icons and logo images.
#
# GRANT OF LICENSE
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
#
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
#
# 3. The end-user documentation included with the redistribution, if
#    any, must include the following acknowledgment:
#
#    "This product includes software developed by Robert LeBlanc
#    <rjl@renaissoft.com>."
#
#    Alternately, this acknowledgment may appear in the software itself,
#    if and wherever such third-party acknowledgments normally appear.
#
# 4. At least one of the following branding conventions must be used:
#
#    a. The Maia Mailguard logo appears in the page-top banner of
#       all HTML output pages in an unmodified form, and links
#       directly to the Maia Mailguard home page; or
#
#    b. The "Powered by Maia Mailguard" graphic appears in the HTML
#       output of all gateway pages that lead to this software,
#       linking directly to the Maia Mailguard home page; or
#
#    c. A separate Rebranding License is obtained from the copyright
#       owner, exempting the Licensee from 4(a) and 4(b), subject to
#       the additional conditions laid out in that license document.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
# "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
# LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
# FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
# COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
# BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
# OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
# ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
# TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
# USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
########################################################################

    use DBI;
    use Mail::SpamAssassin;
    use Getopt::Long;

    # SpamAssassin core rules directory ($system_rules_dir)
    my @default_rules_path = (
        "/var/lib/spamassassin/%%VERSION%%",
        "/usr/share/spamassassin",
        "%%PREFIX%%/share/spamassassin",
        "/usr/local/share/spamassassin",
    );

    # SpamAssassin local.cf directory ($local_cf_dir)
    my @site_rules_path = (
        "/etc/mail/spamassassin",
        "%%PREFIX%%/etc/mail/spamassassin",
        "%%PREFIX%%/etc/spamassassin",
        "/usr/local/etc/spamassassin",
        "/usr/pkg/etc/spamassassin",
        "/usr/etc/spamassassin",
        "/etc/spamassassin",
    );

    # SpamAssassin user_prefs directory ($user_rules_dir)
    my @user_rules_path = (
        "/var/lib/maia/.spamassassin",
        "/var/amavisd/.spamassassin",
        "/var/amavis/.spamassassin",
        "/home/amavis/.spamassassin",
        "~/.spamassassin",
    );

    # prototypes
    sub fatal($);
    sub output($);
    sub expand_macros($$$);
    sub first_existing_path($$@);
    sub scan_rule_file($$$$);
    sub scan_score_file($$$);
    sub scan_rules_directory($$$$);

    # name of this script
    my $script_name = "load-sa-rules";

    # read configuration file (/etc/maia/maia.conf)
    my $config_file = "/etc/maia/maia.conf";
    unless (my $rv = do $config_file) {
        fatal(sprintf("Couldn't parse %s: %s", $config_file, $@)) if $@;
        fatal(sprintf("Couldn't open %s", $config_file)) if (!defined($rv) || !$rv);
    };

    my $help = 0;
    my $debug = 0;
    my $quiet = 0;
    my $reload_descriptions = 0;

    GetOptions("local-cf-dir=s" => \$local_cf_dir,         # --local-cf-dir=<directory>
               "system-rules-dir=s" => \$system_rules_dir, # --system-rules-dir=<directory>
               "user-rules-dir=s" => \$user_rules_dir,     # --user-rules-dir=<directory>
               "reload_descriptions" => \$reload_descriptions, # --reload_descriptions
               "help" => \$help,                           # --help
               "debug" => \$debug,                         # --debug
               "quiet" => \$quiet);                        # --quiet

    # Resolve any debug/quiet conflicts
    if ($debug && $quiet) {
        $debug = 0;
        $quiet = 0;
        output("Warning: --debug and --quiet negate each other.");
    }

    # Display usage information
    if ($help) {
        output("load-sa-rules.pl\n" .
               "   --local-cf-dir=<directory>     : SpamAssassin local.cf directory\n" .
               "   --system-rules-dir=<directory> : SpamAssassin core rules directory\n" .
               "   --user-rules-dir=<directory>   : SpamAssassin user_prefs directory\n" .
               "   --reload_descriptions          : force reload of all descriptions\n" .
               "   --help                         : display this help text\n" .
               "   --debug                        : display detailed debugging information\n" .
               "   --quiet                        : display only error messages\n");
        exit;
    }

    my $sa = Mail::SpamAssassin->new();
    my $sa_version = $sa->VERSION;
    my $sa_prefix = $sa->{PREFIX};

    # defaults (overridden by values in /etc/maia/maia.conf)
    if (defined($local_cf_dir)) {
        $local_cf_dir = expand_macros($sa_version, $sa_prefix, $local_cf_dir);
        fatal(sprintf("Directory %s does not exist!", $local_cf_dir))
            if (!-e $local_cf_dir);
    } else {
        $local_cf_dir = first_existing_path($sa_version, $sa_prefix, @site_rules_path);
        fatal("Couldn't find local.cf directory (set \$local_cf_dir in maia.conf)")
            if (!defined($local_cf_dir));
    }
    if (defined($system_rules_dir)) {
        $system_rules_dir = expand_macros($sa_version, $sa_prefix, $system_rules_dir);
        fatal(sprintf("Directory %s does not exist!", $system_rules_dir))
            if (!-e $system_rules_dir);
    } else {
        $system_rules_dir = first_existing_path($sa_version, $sa_prefix, @default_rules_path);
        fatal("Couldn't find SpamAssassin rules directory (set \$system_rules_dir in maia.conf)")
            if (!defined($system_rules_dir));
    }
    if (defined($user_rules_dir)) {
        $user_rules_dir = expand_macros($sa_version, $sa_prefix, $user_rules_dir);
        if (!-e $user_rules_dir) {
            output(sprintf("Warning: Directory %s does not exist!  Skipping...", $user_rules_dir));
            $user_rules_dir = undef;
        }
    } else {
        $user_rules_dir = first_existing_path($sa_version, $sa_prefix, @user_rules_path);
        output("Warning: Couldn't find amavis user's user_prefs directory (optional: set \$user_rules_dir in maia.conf)")
            if (!defined($user_rules_dir) && !$quiet);
    }

    if ($debug) {
        output(sprintf("SpamAssassin core rules directory = %s", $system_rules_dir));
        output(sprintf("SpamAssassin local.cf directory = %s", $local_cf_dir));
        output(sprintf("SpamAssassin user_prefs directory = %s",
            (defined($user_rules_dir) ? $user_rules_dir : "(not found)")));
    }

    my $dbh;

    # database configuration
    if (defined($dsn) && defined($username) && defined($password)) {
        $dbh = DBI->connect($dsn, $username, $password)
            or fatal("Can't connect to the Maia database (verify \$dsn, \$username, and \$password in maia.conf)");
    } else {
        fatal("Can't connect to the Maia database (missing \$dsn, \$username, or \$password in maia.conf)");
    }

    # Scan the rules directories in this specific order:
    #
    #    1. Default rules (e.g. /usr/share/spamassassin)
    #    2. Site rules (e.g. /etc/mail/spamassassin)
    #    3. User rules (e.g. /var/lib/maia/.spamassassin)
    #
    # The order is critical, since later rules override
    # the scores of earlier ones (e.g. a user rule could
    # assign a score of 0 to a rule to disable it, etc.).
    my $rules_added = 0;
    my $rules_skipped = 0;
    my $added = 0;
    my $skipped = 0;
    ($added, $skipped) = scan_rules_directory($dbh, $system_rules_dir, $debug, 1);
    $rules_added += $added;
    $rules_skipped += $skipped;
    $added = 0;
    $skipped = 0;
    ($added, $skipped) = scan_rules_directory($dbh, $local_cf_dir, $debug, 0);
    $rules_added += $added;
    $rules_skipped += $skipped;
    if (defined($user_rules_dir)) {
        $added = 0;
        $skipped = 0;
        ($added, $skipped) = scan_rules_directory($dbh, $user_rules_dir, $debug, 0);
        $rules_added += $added;
        $rules_skipped += $skipped;
    }
    my $total = $rules_added + $rules_skipped;

    output(sprintf("%d new rules added (%d rules total), all scores updated.", 
        $rules_added, $total))
        if (!$quiet);

    # Disconnect from the database
    $dbh->disconnect;

    # We're done.
    exit;


    # Die, printing a time-stamped error message.
    sub fatal($) {
        my ($msg) = @_;

        output("FATAL ERROR: " . $msg);
        exit 1;
    }


    # Write a time-stamped string to stdout for logging purposes.
    sub output($) {
        my ($msg) = @_;
        my ($year, $month, $day, $hour, $minute, $second);

        my ($second, $minute, $hour, $day, $month, $year) = (localtime)[0,1,2,3,4,5];

        printf("%04d-%02d-%02d %02d:%02d:%02d Maia: [%s] %s\n",
               $year+1900, $month+1, $day, $hour, $minute, $second, $script_name, $msg);
    }

    
    # Perform macro replacements for %%PREFIX%% and %%VERSION%%,
    # and the '~' for home directories.
    sub expand_macros($$$) {
        my ($sa_version, $sa_prefix, $path) = @_;

        $path =~ s/%%PREFIX%%/$sa_prefix/g;
        $path =~ s/%%VERSION%%/$sa_version/g;
        $path =~ s/^~/($ENV{HOME} || $ENV{LOGDIR} || (getpwuid($>))[7])/gex;

        return $path;
    }


    # Find the first existing directory in a list.
    sub first_existing_path($$@) {
        my ($sa_version, $sa_prefix, @pathlist) = @_;

        foreach my $path (@pathlist) {
            $path = expand_macros($sa_version, $sa_prefix, $path);
            return $path if (defined $path && -e $path);
        }

        return undef;
    }


    # Scan a file for "description" strings, which map rule names 
    # to text explanations.  Each of these represents a SpamAssassin 
    # rule.  If this rule doesn't already exist in the database, 
    # insert it, with a default score of 1.0 (per SpamAssassin's 
    # documentation).
    sub scan_rule_file($$$$) {
        my ($dbh, $file, $reload_descriptions, $debug) = @_;
        my($select, $insert, $sth, $sth2, $line, @row);
        my $rules_added = 0;
        my $rules_skipped = 0;

        output(sprintf("Checking %s for new rules...", $file))
            if ($debug);
        open RULEFILE, "<" . $file
            or fatal(sprintf("Couldn't read %s", $file));
        $select = "SELECT id, rule_description FROM maia_sa_rules WHERE rule_name LIKE ?";
        $sth = $dbh->prepare($select)
            or fatal(sprintf("Couldn't prepare query: %s", $dbh->errstr));
        my($default_score, $rule_name, $rule_description);
        while ($line = <RULEFILE>) {
            if ($line =~ /^\s*describe\s*([A-Za-z0-9_]+)[\s\t]*(.*)\s*\n$/si) {

                $sth->execute($1)
                    or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                if (!(@row = $sth->fetchrow_array()) || $row[1] eq '' || $reload_descriptions) {
                    $rule_name = $1;
                    $rule_description = $2;
                    if ($debug) {
                        if ($row[1] eq '' || $reload_descriptions) {
                             output(sprintf("updating rule description: %s (%s)",
                                    $rule_name, $rule_description));
                        } else {
                   	        output(sprintf("Adding new rule: %s (%s)",
                                $rule_name, $rule_description));
                        }
                    }
               	    if ($rule_name =~ /^T_.+$/) { # test rule
               	        $default_score = 0.01;
               	    } elsif ($rule_name =~ /^__.+$/) { # meta-rule
               	        $default_score = 0.00;
               	    } else {
               	        $default_score = 1.00;
               	    }
               	    if (!@row) {
                        $insert = "INSERT INTO maia_sa_rules (rule_name, rule_description, rule_score_0, " .
                                      "rule_score_1, rule_score_2, rule_score_3) " .
                                      "VALUES (?, ?, ?, ?, ?, ?)";
                        $sth2 = $dbh->prepare($insert)
                            or fatal(sprintf("Couldn't prepare query: %s", $dbh->errstr));
                        $sth2->execute($rule_name, $rule_description, $default_score,
                                       $default_score, $default_score, $default_score)
                            or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                        $rules_added++;
                    } else {
                        $update = "UPDATE maia_sa_rules SET rule_description = ? WHERE id = ?";
                        $sth2 = $dbh->prepare($update)
                            or fatal(sprintf("Couldn't prepare query: %s", $dbh->errstr));
                        $sth2->execute($rule_description, $row[0])
                            or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                    }
                } else {
                    output(sprintf("Skipping existing rule: %s", $1))
                        if ($debug);
                    $rules_skipped++;
                }
            }
        }
        $sth->finish;
        close RULEFILE;

        output(sprintf("%d new rules added, %d existing rules skipped.", 
            $rules_added, $rules_skipped))
            if ($debug);

        return ($rules_added, $rules_skipped);
    }


    # Scan a file for "score" strings, which map rule names to 
    # numeric scores.  Any rule without an explicit score retains 
    # its default score of 1.0, per the SpamAssassin documentation.
    sub scan_score_file($$$) {
        my ($dbh, $file, $debug) = @_;
        my($select, $update, $sth, $sth2, $line, @row);

        output(sprintf("Checking %s for updated scores...", $file))
            if ($debug);
    	open SCOREFILE, "<" . $file
            or fatal(sprintf("Unable to open %s", $file));
        $select = "SELECT id FROM maia_sa_rules WHERE rule_name LIKE ?";
        $sth = $dbh->prepare($select)
            or fatal(sprintf("Couldn't prepare query: %s", $dbh->errstr));
        $update = "UPDATE maia_sa_rules SET rule_score_0 = ?, " .
                                           "rule_score_1 = ?, " .
                                           "rule_score_2 = ?, " .
                                           "rule_score_3 = ? " .
                  "WHERE id = ?";
        $sth2 = $dbh->prepare($update)
            or fatal(sprintf("Couldn't prepare query: %s", $dbh->errstr));
        my(@score, $rule_name, $rule_id);
        while ($line = <SCOREFILE>) {

            # Scores for all four rulesets explicitly provided, e.g.
            # score RULE_NAME 0 1 2 3
            if ($line =~ /^\s*score\s+([A-Za-z0-9_]+)[\s\t]+([0-9\-\.]+)[\s\t]+([0-9\-\.]+)[\s\t]+([0-9\-\.]+)[\s\t]+([0-9\-\.]+)[\s\t]*\n$/si) {
    	        $sth->execute($rule_name = $1)
                    or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                if (@row = $sth->fetchrow_array()) {
                    $score[0] = $2;
                    $score[1] = $3;
                    $score[2] = $4;
                    $score[3] = $5;
                    $rule_id = $1 if $row[0] =~ /^([1-9]+[0-9]*)$/si; # untaint
                    output(sprintf("Updating %-30s [%8.3f] [%8.3f] [%8.3f] [%8.3f]",
                           $rule_name, $score[0], $score[1], $score[2], $score[3]))
                        if ($debug);
                    $sth2->execute($score[0], $score[1], $score[2], $score[3], $rule_id)
                         or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                }

            # Scores for three rulesets explicitly provided, so set
            # the fourth ruleset score to the value of the third, per
            # the SpamAssassin documentation.
            # score RULE_NAME 1 2 3
            } elsif ($line =~ /^\s*score\s+([A-Za-z0-9_]+)[\s\t]+([0-9\-\.]+)[\s\t]+([0-9\-\.]+)[\s\t]+([0-9\-\.]+)[\s\t]*\n$/si) {

                $sth->execute($rule_name = $1)
                    or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                if (@row = $sth->fetchrow_array()) {
                    $score[0] = $2;
                    $score[1] = $3;
                    $score[2] = $4;
                    $score[3] = $4;
                    $rule_id = $1 if $row[0] =~ /^([1-9]+[0-9]*)$/si; # untaint
                    output(sprintf("Updating %-30s [%8.3f] [%8.3f] [%8.3f] [%8.3f]",
                           $rule_name, $score[0], $score[1], $score[2], $score[3]))
                        if ($debug);
                    $sth2->execute($score[0], $score[1], $score[2], $score[3], $rule_id)
                        or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                }

            # Scores for two rulesets explicitly provided, so set
            # the third and fourth scores to the value of the second,
            # per the SpamAssassin documentation.
            # score RULE_NAME 1 2
            } elsif ($line =~ /^\s*score\s+([A-Za-z0-9_]+)[\s\t]+([0-9\-\.]+)[\s\t]+([0-9\-\.]+)[\s\t]*\n$/si) {

                $sth->execute($rule_name = $1)
                    or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                if (@row = $sth->fetchrow_array()) {
                    $score[0] = $2;
                    $score[1] = $3;
                    $score[2] = $3;
                    $score[3] = $3;
                    $rule_id = $1 if $row[0] =~ /^([1-9]+[0-9]*)$/si; # untaint
                    output(sprintf("Updating %-30s [%8.3f] [%8.3f] [%8.3f] [%8.3f]",
                           $rule_name, $score[0], $score[1], $score[2], $score[3]))
                        if ($debug);
                    $sth2->execute($score[0], $score[1], $score[2], $score[3], $rule_id)
                        or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                }

            # Scores for only one ruleset explicitly provided, so set
            # the second, third, and fourth scores to the value of the
            # first, per the SpamAssassin documentation.
            # score RULE_NAME 1
            } elsif ($line =~ /^\s*score\s+([A-Za-z0-9_]+)[\s\t]+([0-9\-\.]+)[\s\t]*\n$/si) {

                $sth->execute($rule_name = $1)
                    or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                if (@row = $sth->fetchrow_array()) {
                    $score[0] = $2;
                    $score[1] = $2;
                    $score[2] = $2;
                    $score[3] = $2;
                    $rule_id = $1 if $row[0] =~ /^([1-9]+[0-9]*)$/si; # untaint
                    output(sprintf("Updating %-30s [%8.3f] [%8.3f] [%8.3f] [%8.3f]",
                           $rule_name, $score[0], $score[1], $score[2], $score[3]))
                        if ($debug);
                    $sth2->execute($score[0], $score[1], $score[2], $score[3], $rule_id)
                        or fatal(sprintf("Couldn't execute query: %s", $dbh->errstr));
                }
            }
        }
        $sth->finish;
        close SCOREFILE;
    }


    # Scan all the *.cf and user_prefs files in a subdirectory,
    # looking for SpamAssassin rules.  If $recurse is true, then
    # also check any subdirectories beneath this one.
    sub scan_rules_directory($$$$) {
    	my($dbh, $dir, $debug, $recurse) = @_;
        my(@file_list) = glob($dir . "/*");
        my $rules_added = 0;
        my $rules_skipped = 0;

        output(sprintf("Scanning %s for SpamAssassin rules", $dir))
            if ($debug);

        # depth-first traversal of any subdirectories
        if ($recurse) {
            foreach my $file (@file_list)
            {
                if (-d $file) {
                    my ($added, $skipped) = scan_rules_directory($dbh, $file, $debug, 1);
                    $rules_added += $added;
                    $rules_skipped += $skipped;
                }
            }
        }

        # look for any new rules in the directory
        foreach my $file (@file_list)
        {
            if ($file =~ /^(.+\.cf|.*user_prefs)$/si) {
                my ($added, $skipped) = scan_rule_file($dbh, $file, $reload_descriptions, $debug);
                $rules_added += $added;
                $rules_skipped += $skipped;
            }
        }

        # update the scores of the rules in the directory
        foreach my $file (@file_list)
        {
            if ($file =~ /^(.+\.cf|.*user_prefs)$/si) {
                scan_score_file($dbh, $file, $debug);
            }
        }

        return ($rules_added, $rules_skipped);
    }
