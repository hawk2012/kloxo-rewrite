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


class remote { }
$downloadserver = "https://raw.githubusercontent.com/hawk2012/kloxo-rewrite/main/ "; // Используем GitHub

function slave_get_db_pass() {
    $file = "/usr/local/lxlabs/kloxo/etc/slavedb/dbadmin";
    if (!file_exists($file)) {
        return null;
    }
    $var = file_get_contents($file);
    $rmt = unserialize($var);
    return $rmt->data['mysql']['dbpassword'];
}

function addLineIfNotExistTemp($filename, $pattern, $comment) {
    $cont = our_file_get_contents($filename);

    if (!preg_match("+$pattern+i", $cont)) {
        our_file_put_contents($filename, "\n$comment \n\n", true);
        our_file_put_contents($filename, $pattern, true);
        our_file_put_contents($filename, "\n\n\n", true);
    } else {
        print("Pattern '$pattern' Already present in $filename\n");
    }
}

function check_default_mysql($dbroot, $dbpass) {
    system("service mysql restart");

    if ($dbpass) {
        exec("echo \"show tables\" | mysql -u $dbroot -p\"$dbpass\" mysql", $out, $return);
    } else {
        exec("echo \"show tables\" | mysql -u $dbroot mysql", $out, $return);
    }

    if ($return) {
        print("Fatal Error: Could not connect to Mysql Localhost using user $dbroot and password \"$dbpass\"\n");
        print("If this is a brand new install, you can completely remove mysql by running the commands below\n");
        print("            sudo apt remove --purge mysql-server\n");
        print("And then run the installer again\n");
        exit;
    }
}

function parse_opt($argv) {
    unset($argv[0]);
    if (!$argv) {
        return null;
    }
    foreach ($argv as $v) {
        if (strstr($v, "=") === false || strstr($v, "--") === false) {
            continue;
        }
        $opt = explode("=", $v);
        $opt[0] = substr($opt[0], 2);
        $ret[$opt[0]] = $opt[1];
    }
    return $ret;
}

function our_file_get_contents($file) {
    $string = null;

    $fp = fopen($file, "r");

    if (!$fp) {
        return null;
    }

    while (!feof($fp)) {
        $string .= fread($fp, 8192);
    }
    fclose($fp);
    return $string;
}

function our_file_put_contents($file, $contents, $appendflag = false) {

    if ($appendflag) {
        $flag = "a";
    } else {
        $flag = "w";
    }

    $fp = fopen($file, $flag);

    if (!$fp) {
        return null;
    }

    fwrite($fp, $contents);

    fclose($fp);
}

function password_gen() {
    $data = mt_rand(2, 30);
    $pass = "lx" . $data; // lx is an identifier
    return $pass;
}

function strtil($string, $needle) {
    if (strrpos($string, $needle)) {
        return substr($string, 0, strrpos($string, $needle));
    } else {
        return $string;
    }
}

function strtilfirst($string, $needle) {
    if (strpos($string, $needle)) {
        return substr($string, 0, strpos($string, $needle));
    } else {
        return $string;
    }
}

function strfrom($string, $needle) {
    return substr($string, strpos($string, $needle) + strlen($needle));
}

function char_search_beg($haystack, $needle) {
    if (strpos($haystack, $needle) === 0) {
        return true;
    }
    return false;
}

function install_apt_sources($osversion) {
    global $downloadserver;

    // Добавление репозитория Kloxo в /etc/apt/sources.list.d/kloxo.list
    $repo_file = "/etc/apt/sources.list.d/kloxo.list";
    if (!file_exists($repo_file)) {
        $repo_content = "deb [trusted=yes] " . $downloadserver . " $osversion main\n";
        our_file_put_contents($repo_file, $repo_content);
        system("apt update");
    } else {
        print("Kloxo repository already configured.\n");
    }
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

/**
 * Get Yes/No answer from stdin
 * @param string $question question text
 * @param char $default default answer (optional)
 * @return char 'y' for Yes or 'n' for No
 */
function get_yes_no($question, $default = 'n') {
    if ($default != 'y') {
        $default = 'n';
        $question .= ' [y/N]: ';
    } else {
        $question .= ' [Y/n]: ';
    }
    for (;;) {
        print $question;
        flush();
        $input = fgets(STDIN, 255);
        $input = trim($input);
        $input = strtolower($input);
        if ($input == 'y' || $input == 'yes' || ($default == 'y' && $input == '')) {
            return 'y';
        }
        else if ($input == 'n' || $input == 'no' || ($default == 'n' && $input == '')) {
            return 'n';
        }
    }
}