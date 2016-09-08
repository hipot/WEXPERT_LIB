
# lib

## lib - аналог папки /bitrix/php_interface/include/lib/

Тут расположены ООП-сущьности, наиболее часто используемые классы.

Напоминаю, что lib - это аналог папки /bitrix/php_interface/include/lib/

В данный момент содержит:

    CImg
    CConsole
    FatalErrorsMailer
    weBitrixAuth


Также в данной папке расположен файл с наиболее часто используемыми функциями:
Файл function.php.

Про структурирование в данной папке можно почитать по адресу:
http://hipot.wexpert.ru/Codex/php_interface-structure-and-js-template-folder/#init

## Автозагрузка классов для папки /bitrix/php_interface/include/lib/ через simple_loader.php

Имя файла должно совпадать с именем класса, чтобы автозагрузка работала.

Подробнее можно почитать про автозагрузку по адресу:
http://hipot.wexpert.ru/Codex/ctil-napisaniya-koda-v-studii-webexpert/#Adv-Autoload
