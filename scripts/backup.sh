#!/bin/bash

HOST=localhost #сервер БД
DB_NAME=dttcom #Имя БД
USER=root #Имя пользователя базы данных
PASSWD=xxxxxx #Пароль от базы данных
CHARSET=utf8 #Кодировка базы данных (utf8)

DBFILENAME=dttcom_db #Имя дампа базы данных
ARFILENAME=dttcom_www #Имя архива с файлами
DATADIR=/backups/ #Путь к каталогу где будут храниться резервные копии
SRCFILES=/home/bitrix/www/ #Путь к каталогу файлов для архивирования
PREFIX=`date +%F` #Префикс по дате для структурирования резервных копий

#delete old 7 days backups
/usr/bin/find $DATADIR -not -mtime -7 -mindepth 1 -delete >/dev/null 2>&1

#create folder
mkdir $DATADIR/$PREFIX

#MySQL dump
mysqldump --user=$USER --host=$HOST --password=$PASSWD --default-character-set=$CHARSET --databases $DB_NAME | gzip > $DATADIR/$PREFIX/$DBFILENAME-`date +%F--%H-%M`.sql.gz

#Src dump
tar -czpf $DATADIR/$PREFIX/$ARFILENAME-`date +%F--%H-%M`.tar.gz $SRCFILES 2> /dev/null
