<?php
//
//    Kloxo, Hosting Panel
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2011     LxCenter
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

include_once "../install_common.php";

function lxins_main()
{
    global $argv, $downloadserver;
    $opt = parse_opt($argv);
    $dir_name = dirname(__FILE__);
    $installtype = $opt['install-type'];
    $dbroot = isset($opt['db-rootuser']) ? $opt['db-rootuser'] : "root";
    $dbpass = isset($opt['db-rootpassword']) ? $opt['db-rootpassword'] : "";
    $osversion = find_os_version();
    $arch = trim(`arch`);

    if (!char_search_beg($osversion, "ubuntu") && !char_search_beg($osversion, "debian")) {
        print("Kloxo is only supported on Ubuntu and Debian.\n");
        exit;
    }

    if (file_exists("/usr/local/lxlabs/kloxo")) {
        // Ask Reinstall
        if (get_yes_no("Kloxo seems already installed. Do you wish to continue?") == 'n') {
            print("Installation Aborted.\n");
            exit;
        }
    } else {
        // Ask License
        if (get_yes_no("Kloxo is using AGPL-V3.0 License. Do you agree with the terms?") == 'n') {
            print("You did not agree to the AGPL-V3.0 license terms.\n");
            print("Installation aborted.\n\n");
            exit;
        } else {
            print("Installing Kloxo = YES\n\n");
        }
    }

    // Ask for InstallApp
    print("InstallApp: PHP Applications like PHPBB, WordPress, Joomla etc\n");
    print("When you choose Yes, be aware of downloading about 350MB of data!\n");
    if (get_yes_no("Do you want to install the InstallAPP software?") == 'n') {
        print("Installing InstallApp = NO\n");
        print("You can install it later with /script/installapp-update\n\n");
        $installappinst = false;
    } else {
        print("Installing InstallApp = YES\n\n");
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
        }
    }

    print("Prepare installation directory\n");
    system("mkdir -p /usr/local/lxlabs/kloxo");
    chdir("/usr/local/lxlabs/kloxo");
    system("mkdir -p /usr/local/lxlabs/kloxo/log");
    @unlink("kloxo-current.zip");
    print("Downloading latest Kloxo release\n");
    system("wget {$downloadserver}download/kloxo/production/kloxo/kloxo-current.zip");
    print("\n\nInstalling Kloxo.....\n\n");
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
    system("/usr/local/lxlabs/ext/php/php $dir_name/installall.php");
    file_put_contents("/etc/default/spamassassin", "SPAMDOPTIONS=\" -v -d -p 783 -u lxpopuser\"");
    print("Creating Vpopmail database...\n");
    system("sh $dir_name/vpop.sh $dbroot \"$dbpass\" lxpopuser $mypass");
    system("chmod -R 755 /var/log/apache2/");
    system("chmod -R 755 /var/log/apache2/fpcgisock >/dev/null 2>&1");
    system("mkdir -p /var/log/kloxo/");
    system("mkdir -p /var/log/news");
    system("ln -sf /usr/sbin/sendmail /usr/lib/sendmail");
    system("echo `hostname` > /etc/mailname");
    system("service postfix restart >/dev/null 2>&1 &");
    system("service dovecot restart >/dev/null 2>&1 &");

    $dbfile = "/home/kloxo/httpd/webmail/horde/scripts/sql/create.mysql.sql";
    if (file_exists($dbfile)) {
        if ($dbpass == "") {
            system("mysql -u $dbroot <$dbfile");
        } else {
            system("mysql -u $dbroot -p$dbpass <$dbfile");
        }
    }

    system("mkdir -p /home/kloxo/httpd");
    chdir("/home/kloxo/httpd");
    @unlink("skeleton-disable.zip");
    system("chown -R lxlabs:lxlabs /home/kloxo/httpd");
    system("/etc/init.d/kloxo restart >/dev/null 2>&1 &");
    chdir("/usr/local/lxlabs/kloxo/httpdocs/");
    system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/install/create.php --install-type=$installtype --db-rootuser=$dbroot --db-rootpassword=$dbpass");
    system("/usr/local/lxlabs/ext/php/php /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");
    system("/bin/rm /usr/local/lxlabs/kloxo/bin/misc/secure-webmail-mysql.phps");

    if ($installappinst) {
        system("/script/installapp-update"); // First run (gets installappdata)
        system("/script/installapp-update"); // Second run (gets applications)
    }

    print("Congratulations. Kloxo has been installed successfully on your server as $installtype \n");
    if ($installtype === 'master') {
        print("You can connect to the server at https://<ip-address>:7777 or http://<ip-address>:7778\n");
        print("Please note that first is secure SSL connection, while the second is normal one.\n");
        print("The login and password are 'admin' 'admin'. After Logging in, you will have to change your password to something more secure\n");
        print("We hope you will find managing your hosting with Kloxo refreshingly pleasurable, and also we wish you all the success on your hosting venture\n");
        print("Thanks for choosing Kloxo to manage your hosting, and allowing us to be of service\n");
    } else {
        print("You should open the port 7779 on this server, since this is used for the communication between master and slave\n");
        print("To access this slave, to go admin->servers->add server, give the ip/machine name of this server. The password is 'admin'. The slave will appear in the list of slaves, and you can access it just like you access localhost\n\n");
    }
}

lxins_main();