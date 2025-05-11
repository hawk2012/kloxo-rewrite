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

if (isset($argv[1])) {
    $pass = $argv[1];
} else {
    $pass = "";
}

// Проверка, что пароль не пустой
if (empty($pass)) {
    print("Error: Password cannot be empty.\n");
    exit(1);
}

print("Stopping MySQL\n");
shell_exec("service mysql stop");

// Запуск MySQL с пропуском проверки прав доступа
print("Starting MySQL with skip grant tables\n");
shell_exec("mysqld_safe --skip-grant-tables >/dev/null 2>&1 &");

// Ожидание запуска MySQL
sleep(10);

// Сброс пароля для root пользователя
print("Resetting MySQL root password\n");
$query = "ALTER USER 'root'@'localhost' IDENTIFIED BY '$pass';";
system("mysql -u root -e \"$query\"", $return);

while ($return) {
    print("MySQL could not connect, retrying...\n");
    sleep(10);
    system("mysql -u root -e \"$query\"", $return);
}

// Применение изменений
system("mysql -u root -e \"FLUSH PRIVILEGES;\"", $flush_return);
if ($flush_return) {
    print("Error: Failed to flush privileges.\n");
    exit(1);
}

// Остановка MySQL
print("Killing MySQL process\n");
shell_exec("killall mysqld");

// Пауза перед перезапуском
print("Sleeping for 10 seconds\n");
sleep(10);

// Перезапуск MySQL
print("Restarting MySQL service\n");
system("service mysql restart");

print("Password successfully reset to \"$pass\"\n");