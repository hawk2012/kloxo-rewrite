#!/bin/bash
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2011	LxCenter
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# LxCenter note: uses an lxadmin path!

name=$1
pass=$2
dbuser=$3
dbpass=$4

MYSQLPR=$(which mysql)
if [ ! -f "$MYSQLPR" ]; then
    echo "FATAL ERROR: MySQL client is not installed. Please install MySQL first."
    exit 1
fi

# Проверка, запущен ли MySQL
if systemctl is-active --quiet mysql; then
    if [ -z "$pass" ]; then
        # Без пароля для root пользователя
        echo "CREATE DATABASE IF NOT EXISTS popuser; GRANT ALL PRIVILEGES ON popuser.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass';" | "$MYSQLPR" -u"$name"
        echo "CREATE DATABASE IF NOT EXISTS vpopmail; GRANT ALL PRIVILEGES ON vpopmail.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass';" | "$MYSQLPR" -u"$name"
    else
        # С паролем для root пользователя
        echo "CREATE DATABASE IF NOT EXISTS popuser; GRANT ALL PRIVILEGES ON popuser.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass';" | "$MYSQLPR" -u"$name" -p"$pass"
        echo "CREATE DATABASE IF NOT EXISTS vpopmail; GRANT ALL PRIVILEGES ON vpopmail.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass';" | "$MYSQLPR" -u"$name" -p"$pass"
    fi
else
    echo "FATAL ERROR: MySQL service is not running. Please start MySQL and try again."
    exit 1
fi

# Создание конфигурационного файла для vpopmail
echo "localhost|0|$dbuser|$dbpass|vpopmail" > /var/lib/kloxo/mail/etc/vpopmail.mysql
chmod 600 /var/lib/kloxo/mail/etc/vpopmail.mysql
chown -R lxlabs:lxlabs /var/lib/kloxo/mail/etc/vpopmail.mysql

echo "Database setup completed successfully."