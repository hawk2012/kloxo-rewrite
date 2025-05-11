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
//

include_once "htmllib/lib/include.php";

createNewCertificate();

function createNewCertificate()
{
    global $gbl, $login, $ghtml;

    $cerpath = "server.crt";
    $keypath = "server.key";
    $requestpath = "a.csr";

    // Настройки для сертификата
    $ltemp["countryName"] = "IN"; // Страна (например, IN для Индии)
    $ltemp["stateOrProvinceName"] = "Bn"; // Регион или штат
    $ltemp["localityName"] = "Bn"; // Город
    $ltemp["organizationName"] = "LxCenter"; // Организация
    $ltemp["organizationalUnitName"] = "Kloxo"; // Подразделение организации
    $ltemp["commonName"] = "Kloxo"; // Имя сервера (например, домен)
    $ltemp["emailAddress"] = "contact@lxcenter.org"; // Email

    // Генерация закрытого ключа
    $privkey = openssl_pkey_new([
        "private_key_bits" => 2048, // Размер ключа (2048 бит рекомендуется)
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);

    if (!$privkey) {
        die("Failed to generate private key.\n");
    }

    // Экспорт закрытого ключа в файл
    openssl_pkey_export_to_file($privkey, $keypath);

    // Создание CSR (Certificate Signing Request)
    $csr = openssl_csr_new($ltemp, $privkey);
    if (!$csr) {
        die("Failed to create CSR.\n");
    }

    // Экспорт CSR в файл
    openssl_csr_export_to_file($csr, $requestpath);

    // Подписание сертификата (самоподписанный сертификат)
    $sscert = openssl_csr_sign($csr, null, $privkey, 365); // Срок действия: 365 дней
    if (!$sscert) {
        die("Failed to sign certificate.\n");
    }

    // Экспорт сертификата в файл
    openssl_x509_export_to_file($sscert, $cerpath);

    // Перемещение файлов в целевую директорию
    $src = getcwd();
    $dest = '/usr/local/lxlabs/kloxo/ext/lxhttpd/conf';

    // Создание директорий для сертификатов и ключей
    @mkdir("$dest/ssl.crt", 0755, true);
    @mkdir("$dest/ssl.key", 0755, true);

    // Перемещение файлов
    rename("$src/$cerpath", "$dest/ssl.crt/$cerpath");
    rename("$src/$keypath", "$dest/ssl.key/$keypath");
    rename("$src/$requestpath", "$dest/$requestpath");

    echo "SSL certificate and key successfully created and moved to $dest\n";
}