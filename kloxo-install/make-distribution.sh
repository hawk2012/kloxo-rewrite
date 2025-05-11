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
# This script creates kloxo-install.zip for download server.
# 

echo "################################"
echo "### Start packaging"

# Переход в корневую директорию проекта
cd ../ || { echo "Failed to change directory"; exit 1; }

# Удаление предыдущего архива
rm -f ../kloxo-install/kloxo-install.zip

echo "### Creating zip package..."

# Создание архива с исключением ненужных файлов и директорий
zip -r9 ./kloxo-install/kloxo-install.zip ./kloxo-install -x \
"*/CVS/*" \
"*/.svn/*" \
"*.svn/*" \
"*.CVS/*" \
"*.*~" \
"*/kloxo-packer.sh" \
"*/kloxo-patcher.sh" \
"*/kloxo-install-master.sh" \
"*/kloxo-install-slave.sh" \
"*/kloxo-installer.sh" \
"*/make-distribution.sh"

# Проверка успешности создания архива
if [ $? -eq 0 ]; then
    echo "### Packaging completed successfully"
else
    echo "### Error occurred during packaging"
    exit 1
fi

echo "### Finished"
echo "################################"

# Возвращение в директорию kloxo-install
cd ./kloxo-install || { echo "Failed to change directory"; exit 1; }

# Вывод информации о созданном архиве
ls -lh kloxo-install.zip