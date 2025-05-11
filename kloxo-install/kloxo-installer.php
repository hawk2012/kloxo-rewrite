<?php
//    Kloxo, Hosting Control Panel
//
//    Copyright (C) 2000-2009	LxLabs
//    Copyright (C) 2009-2011	LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

$downloadserver = "https://raw.githubusercontent.com/hawk2012/kloxo-rewrite/main/ ";

function lxins_main()
{
    global $argv, $downloadserver;
    $opt = parse_opt($argv);
    $dir_name = dirname(__FILE__);
    $installtype = $opt['install-type'];
    $installversion = (isset($opt['version'])) ? $opt['version'] : null;
    $dbroot = "root";
    $dbpass = "";
    $osversion = find_os_version();
    $arch = trim(`arch`);

    //--- Create temporary flags for install
    system("mkdir -p /var/cache/kloxo/");
    system("echo 1 > /var/cache/kloxo/kloxo-install-firsttime.flg");

    if (!char_search_beg($osversion, "ubuntu") && !char_search_beg($osversion, "debian")) {
        print("Kloxo is only supported on Ubuntu and Debian.\n");
        exit;
    }

    if (file_exists("/usr/local/lxlabs/kloxo")) {
        //--- Ask Reinstall
        if (get_yes_no("Kloxo seems already installed. Do you wish to continue?") == 'n') {
            print("Installation Aborted.\n");
            exit;
        }
    } else {
        //--- Ask License
        if (get_yes_no("Kloxo is using AGPL-V3.0 License. Do you agree with the terms?") == 'n') {
            print("You did not agree to the AGPL-V3.0 license terms.\n");
            print("Installation aborted.\n");
            exit;
        } else {
            print("Installing Kloxo = YES\n");
        }
    }

    //--- Ask for InstallApp
    print("InstallApp: PHP Applications like PHPBB, WordPress, Joomla, etc.\n");
    print("When you choose Yes, be aware of downloading about 350MB of data!\n");
    if (get_yes_no("Do you want to install the InstallAPP software?") == 'n') {
        print("Installing InstallApp = NO\n");
        print("You can install it later with /script/installapp-update\n");
        $installappinst = false;
        //--- Temporary flag so InstallApp won't be installed
        system("echo 1 > /var/cache/kloxo/kloxo-install-disableinstallapp.flg");
    } else {
        print("Installing InstallApp = YES\n");
        $installappinst = true;
    }

    print("Adding System users and groups (nouser, nogroup and lxlabs, lxlabs)\n");
    system("groupadd nogroup || true");
    system("useradd nouser -g nogroup -s '/sbin/nologin' || true");
    system("groupadd lxlabs || true");
    system("useradd lxlabs -g lxlabs -s '/sbin/nologin' || true");

    print("Installing Kloxo APT repository for updates\n");
    install_apt_repo($osversion);

    $packages = array("sendmail", "exim4", "vsftpd", "postfix", "vpopmail", "qmail", "pure-ftpd", "imap");
    $list = implode(" ", $packages);
    print("Removing packages $list...\n");
    foreach ($packages as $package) {
        exec("apt remove --purge -y $package > /dev/null 2>&1 || true");
    }

    $packages = array(
        "php-mbstring", "php-mysql", "which", "gcc", "php-imap", "php-pear", "php-dev",
        "apache2", "libapache2-mod-php", "zip", "unzip", "mysql-server", "curl",
        "autoconf", "automake", "libtool", "bogofilter", "openssl", "pure-ftpd"
    );
    $list = implode(" ", $packages);

    while (true) {
        print("Installing packages $list...\n");
        system("apt update && apt install -y $list", $return_value);
        if (file_exists("/usr/local/lxlabs/ext/php/php")) {
            break;
        } else {
            print("APT Gave Error... Trying Again...\n");
            if (get_yes_no("Try again?") == 'n') {
                print("- EXIT: Fix the problem and install Kloxo again.\n");
                exit;
            }
        }
    }

    print("Prepare installation directory\n");
    system("mkdir -p /usr/local/lxlabs/kloxo");

    if ($installversion) {
        if (substr($installversion, 0, 4) == '6.0.') {
            print("\n*** Need additional files installing $installversion (less than 6.1.0) ***\n");
            print("      Run 'sh /script/kloxo-installer.sh' (without argument)\n");
            exit;
        }
        chdir("/usr/local/lxlabs/kloxo");
        system("mkdir -p /usr/local/lxlabs/kloxo/log");
        @unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
        print("Downloading Kloxo {$installversion} release\n");
        system("wget {$downloadserver}kloxo/kloxo-{$installversion}.zip");
        system("mv -f ./kloxo-{$installversion}.zip ./kloxo-current.zip");
    } else {
        if (file_exists("../kloxo-current.zip")) {
            //--- Install from local file if exists
            @unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
            print("Local copying Kloxo release\n");
            system("mkdir -p /var/cache/kloxo");
            system("cp -rf ../kloxo-current.zip /usr/local/lxlabs/kloxo");
        } else {
            chdir("/usr/local/lxlabs/kloxo");
            system("mkdir -p /usr/local/lxlabs/kloxo/log");
            @unlink("/usr/local/lxlabs/kloxo/kloxo-current.zip");
            print("Downloading latest Kloxo release\n");
            system("wget {$downloadserver}kloxo/kloxo-current.zip");
        }
    }

    print("\nInstalling Kloxo.....\n");
    system("unzip -oq kloxo-current.zip", $return);
    if ($return) {
        print("Unzipping the core Failed.. Most likely it is corrupted. Report it at https://github.com/hawk2012/kloxo-rewrite/issues \n");
        exit;
    }
    unlink("kloxo-current.zip");
    system("chown -R lxlabs:lxlabs /usr/local/lxlabs/");
    chdir("/usr/local/lxlabs/kloxo/httpdocs/");
    system("service mysql start");

    if ($installtype !== 'slave') {
        check_default_mysql($dbroot, $dbpass);
    }

    $mypass = password_gen();
    print("Prepare defaults and configurations...\n");
    install_main();
    file_put_contents("/etc/default/spamassassin", "SPAMDOPTIONS=\" -v -d -p 783 -u lxpopuser\"");

    print("\nCreating Vpopmail database...\n");
    system("sh $dir_name/kloxo-linux/vpop.sh $dbroot \"$dbpass\" lxpopuser $mypass");
    system("chmod -R 755 /var/log/apache2/");
    system("chmod -R 755 /var/log/apache2/fpcgisock >/dev/null 2>&1");
    system("mkdir -p /var/log/kloxo/");
    system("mkdir -p /var/log/news");
    system("ln -sf /var/qmail/bin/sendmail /usr/sbin/sendmail");
    system("ln -sf /var/qmail/bin/sendmail /usr/lib/sendmail");
    system("echo `hostname` > /var/qmail/control/me");
    system("service qmail restart >/dev/null 2>&1 &");
    system("service courier-imap restart >/dev/null 2>&1 &");

    print("Prepare /home/kloxo/httpd...\n");
    system("mkdir -p /home/kloxo/httpd");
    chdir("/home/kloxo/httpd");
    @unlink("skeleton-disable.zip");
    system("chown -R lxlabs:lxlabs /home/kloxo/httpd");
    system("/etc/init.d/kloxo restart >/dev/null 2>&1 &");
    chdir("/usr/local/lxlabs/kloxo/httpdocs/");

    system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");

    if ($installappinst) {
        print("Install InstallApp...\n");
        system("/script/installapp-update"); // First run (gets installappdata)
        system("/script/installapp-update"); // Second run (gets applications)
    }

    //--- Remove all temporary flags because the end of install
    print("\nRemove Kloxo install flags...\n");
    system("rm -rf /var/cache/kloxo/*-version");
    system("rm -rf /var/cache/kloxo/kloxo-install-*.flg");

    //--- Prevent mysql socket problem (especially on 64bit system)
    if (!file_exists("/var/run/mysqld/mysqld.sock")) {
        print("Create mysql.sock...\n");
        system("service mysql stop");
        system("mksock /var/run/mysqld/mysqld.sock");
        system("service mysql start");
    }

    //--- Prevent for MySQL not starting after reboot for fresh Kloxo slave install
    print("Setting MySQL to always run after reboot and restart now...\n");
    system("systemctl enable mysql");
    system("service mysql restart");

    //--- Set ownership for Kloxo httpdocs dir
    system("chown -R lxlabs:lxlabs /usr/local/lxlabs/kloxo/httpdocs");

    print("\nCongratulations. Kloxo has been installed successfully on your server as $installtype\n");
    if ($installtype === 'master') {
        print("You can connect to the server at:\n");
        print("	https://<ip-address>:7777 - secure SSL connection, or\n");
        print("	http://<ip-address>:7778 - normal one.\n");
        print("The login and password are 'admin' 'admin'. After logging in, you will have to\n");
        print("change your password to something more secure\n");
        print("We hope you will find managing your hosting with Kloxo\n");
        print("refreshingly pleasurable, and also we wish you all the success\n");
        print("on your hosting venture\n");
        print("Thanks for choosing Kloxo to manage your hosting, and allowing us to be of\n");
        print("service\n");
    } else {
        print("You should open the port 7779 on this server, since this is used for\n");
        print("the communication between master and slave\n");
        print("To access this slave, go to admin->servers->add server,\n");
        print("give the IP/machine name of this server. The password is 'admin'.\n");
        print("The slave will appear in the list of slaves, and you can access it\n");
        print("just like you access localhost\n");
    }

    print("\n");
    print("---------------------------------------------\n");
}

function install_general_mine($value) {
    $value = implode(" ", $value);
    print("Installing $value ....\n");
    system("apt update && apt install -y $value");
}

function installcomp_mail() {
    system('pear channel-update "pear.php.net"'); // to remove old channel warning
    system("pear upgrade --force pear"); // force is needed
    system("pear upgrade --force Archive_Tar"); // force is needed
    system("pear upgrade --force structures_graph"); // force is needed
    system("pear install log");
}

function install_main() {
    $installcomp['mail'] = array("vpopmail", "courier-imap", "courier-authlib", "qmail", "spamassassin", "ezmlm-toaster", "autorespond-toaster");
    $installcomp['web'] = array("apache2", "pure-ftpd");
    $installcomp['dns'] = array("bind9");
    $installcomp['database'] = array("mysql-server");

    $comp = array("web", "mail", "dns", "database");
    $serverlist = $comp;

    foreach ($comp as $c) {
        flush();
        if (array_search($c, $serverlist) !== false) {
            print("Installing $c Components....");
            $req = $installcomp[$c];
            $func = "installcomp_$c";
            if (function_exists($func)) {
                $func();
            }
            install_general_mine($req);
            print("\n");
        }
    }

    $options_file = "/etc/bind/named.conf.options";
    $example_options = "acl \"lxcenter\" {
";
    $example_options .= "\tlocalhost;
";
    $example_options .= "};
";
    $example_options .= "options {
";
    $example_options .= "\tmax-transfer-time-in 60;
";
    $example_options .= "\ttransfer-format many-answers;
";
    $example_options .= "\ttransfers-in 60;
";
    $example_options .= "\tauth-nxdomain yes;
";
    $example_options .= "\tallow-transfer { \"lxcenter\"; };
";
    $example_options .= "\tallow-recursion { \"lxcenter\"; };
";
    $example_options .= "\trecursion no;
";
    $example_options .= "\tversion \"LxCenter-1.0\";
";
    $example_options .= "};
";
    $example_options .= "# Remove # to see all DNS queries
";
    $example_options .= "# logging {
";
    $example_options .= "#\t channel query_logging {
";
    $example_options .= "#\t\t file \"/var/log/named_query.log\";
";
    $example_options .= "#\t\t versions 3 size 100M;
";
    $example_options .= "#\t\t print-time yes;
";
    $example_options .= "#\t };
";
    $example_options .= "#\t category queries {
";
    $example_options .= "#\t\t query_logging;
";
    $example_options .= "#\t };
";
    $example_options .= "# };
";

    if (!file_exists($options_file)) {
        touch($options_file);
        chown($options_file, "bind");
    }
    $cont = file_get_contents($options_file);
    $pattern = "options";
    if (!preg_match("+$pattern+i", $cont)) {
        file_put_contents($options_file, "$example_options\n");
    }

    $pattern = 'include "/etc/kloxo.named.conf";';
    $file = "/etc/bind/named.conf";
    $comment = "//Kloxo";
    addLineIfNotExist($file, $pattern, $comment);
    touch("/etc/kloxo.named.conf");
    chown("/etc/kloxo.named.conf", "bind");
}

function install_apt_repo($osversion) {
    global $downloadserver;
    if (!file_exists("/etc/apt/sources.list.d")) {
        print("No sources.list.d dir detected!\n");
        return;
    }
    if (file_exists("/etc/apt/sources.list.d/kloxo.list")) {
        print("Kloxo APT repository file already present.\n");
        return;
    }
    $repo_content = "deb [trusted=yes] {$downloadserver} $osversion main\n";
    file_put_contents("/etc/apt/sources.list.d/kloxo.list", $repo_content);
    system("apt update");
}

function find_os_version() {
    if (file_exists("/etc/os-release")) {
        $release = trim(file_get_contents("/etc/os-release"));
        if (preg_match('/Ubuntu/i', $release)) {
            return "ubuntu";
        } elseif (preg_match('/Debian/i', $release)) {
            return "debian";
        }
    }
    print("This Operating System is currently *NOT* supported.\n");
    exit;
}

function resetDBPassword($user, $pass)
{
    print("Stopping MySQL\n");
    shell_exec("service mysql stop");
    print("Start MySQL with skip grant tables\n");
    shell_exec("mysqld_safe --skip-grant-tables >/dev/null 2>&1 &");
    print("Using MySQL to flush privileges and reset password\n");
    sleep(10);
    system("echo \"UPDATE mysql.user SET authentication_string=PASSWORD('$pass') WHERE User='$user'; FLUSH PRIVILEGES;\" | mysql -u $user mysql", $return);
    while ($return) {
        print("MySQL could not connect, will sleep and try again\n");
        sleep(10);
        system("echo \"UPDATE mysql.user SET authentication_string=PASSWORD('$pass') WHERE User='$user'; FLUSH PRIVILEGES;\" | mysql -u $user mysql", $return);
    }
    print("Password reset successfully. Now killing MySQL softly\n");
    shell_exec("killall mysqld");
    print("Sleeping 10 seconds\n");
    shell_exec("sleep 10");
    print("Restarting the actual MySQL service\n");
    system("service mysql restart");
    print("Password successfully reset to \"$pass\"\n");
}

function addLineIfNotExist($filename, $pattern, $comment) {
    if (file_exists($filename)) {
        $cont = file_get_contents($filename);
    } else {
        $cont = '';
    }
    if (!preg_match("+$pattern+i", $cont)) {
        file_put_contents($filename, "\n$comment \n\n", FILE_APPEND);
        file_put_contents($filename, $pattern, FILE_APPEND);
        file_put_contents($filename, "\n\n\n", FILE_APPEND);
    } else {
        print("Pattern '$pattern' Already present in $filename\n");
    }
}

lxins_main();