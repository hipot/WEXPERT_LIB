<?php
/**
 * Описание библиотеки runkit
 * @see http://md1.php.net/manual/ru/ref.runkit.php
 */


/**
 *    Переназначить уже определенную константу.
 *
 * @param string $constname              Имя переназначаемой константы. Имя глобальной константы или выражение       classname::constname для переназначения локальной       константы в классе.
 *
 * @param mixed $newvalue              Новое значение константы
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-constant-redefine.php
 *
 */
function runkit_constant_redefine($constname, $newvalue) {}



/**
 *    Удаляет уже определенную константу.
 *
 * @param string $constname              Имя удаляемой константы. Имя глобальной константы или       выражение classname::constname для удаления       локальной константы из класса.
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-constant-remove.php
 *
 */
function runkit_constant_remove($constname) {}



/**
 *    Конвертирует базовый класс в наследованный ("усыновляет").    Дополняет методы наследованными при необходимости.
 *
 * @param string $classname              "Усыновляемый" класс
 *
 * @param string $parentname              Родительский класс
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-class-adopt.php
 *
 */
function runkit_class_adopt($classname, $parentname) {}



/**
 *    Конвертирует наследующий класс в базовый, удаляет из него наследуемые методы.
 *
 * @param string $classname              Имя конвертируемого класса
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-class-emancipate.php
 *
 */
function runkit_class_emancipate($classname) {}



/**
 *    Объявляет константу. Схожа с функцией define(), но позволяет создавать локальные константы внутри классов.
 *
 * @param string $constname              Имя объявляемой константы. Строка для объявления глобальной константы или выражение       classname::constname для добавления локальной константы в классе.
 *
 * @param mixed $value              NULL, Bool, Long, Double, String, или тип Resource для сохранения в новой константе.
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-constant-add.php
 *
 */
function runkit_constant_add($constname, $value) {}



/**
 *    Добавляет новую функцию аналогично  create_function()
 *
 * @param string $funcname              Имя создаваемой функции
 *
 * @param string $arglist              Список аргументов функции, через запятую
 *
 * @param string $code              Код создаваемой функции
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-function-add.php
 *
 */
function runkit_function_add($funcname, $arglist, $code) {}



/**
 *    Копирует функцию с новым именем
 *
 * @param string $funcname              Имя существующей функции
 *
 * @param string $targetname              Имя новой функции, в которую необходимо копировать существующую
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-function-copy.php
 *
 */
function runkit_function_copy($funcname, $targetname) {}



/**
 *    Заменяет определение функции новой реализацией.
 *
 * @param string $funcname              Имя заменяемой функции
 *
 * @param string $arglist              Новый список аргументов, принимаемый функцией
 *
 * @param string $code              Код новой функции
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-function-redefine.php
 *
 */
function runkit_function_redefine($funcname, $arglist, $code) {}



/**
 *    Удаляет определенную функцию
 *
 * @param string $funcname              Имя удаляемой функции
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-function-remove.php
 *
 */
function runkit_function_remove($funcname) {}



/**
 *    Переименовывает функцию
 *
 * @param string $funcname              Имя переименовываемой функции
 *
 * @param string $newname              Новое имя функции
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-function-rename.php
 *
 */
function runkit_function_rename($funcname, $newname) {}



/**
 *    Обрабатывает PHP файл, импортируя функции и классы, перезаписывая при необходимости.
 *
 * @param string $filename              Имя файла, из которого будут импортированы классы и функции
 *
 * @param int $flags              Значение побитового ИЛИ семейства констант RUNKIT_IMPORT_*
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-import.php
 *
 */
function runkit_import($filename, $flags) {}



/**
 *    Проверяет PHP-синтаксис выбранного файла
 *
 * @param string $filename              PHP-файл, в котором проверять синтаксис
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-lint-file.php
 *
 */
function runkit_lint_file($filename) {}



/**
 *    Проверяет PHP-синтаксис выбранного кода
 *
 * @param string $code              PHP код для проверки синтаксиса
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-lint.php
 *
 */
function runkit_lint($code) {}



/**
 * Добавляет метод в класс
 *
 * @param string $classname              Класс, в который будет добавлен метод
 *
 * @param string $methodname              Имя добавляемого метода
 *
 * @param string $args              Список параметров, принимаемых методом, через запятую
 *
 * @param string $code              Код нового метода, который будет выполняться при вызове        methodname
 *
 * @param int $flags              Создаваемый метод может быть быть       RUNKIT_ACC_PUBLIC,       RUNKIT_ACC_PROTECTED или       RUNKIT_ACC_PRIVATE            Замечание:                Этот параметр используется только в PHP 5, потому что в предыдущих         версиях все методы являлись публичными.
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-method-add.php
 *
 */
function runkit_method_add($classname, $methodname, $args, $code, $flags) {}



/**
 * Копирование метода из одного класса в другой
 *
 * @param string $dClass              Класс назначения: имя класса, в который копируется метод
 *
 * @param string $dMethod              Метод назначение: имя метода, в который копируется метод
 *
 * @param string $sClass              Исходный класс: имя класса, из которого копируется метод
 *
 * @param string $sMethod              Исходный метод: имя метода, который копируется. Если этот параметр не указан,       используется значение dMethod.
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-method-copy.php
 *
 */
function runkit_method_copy($dClass, $dMethod, $sClass, $sMethod) {}



/**
 * Изменяет код выбранного метода
 *
 * @param string $classname              Имя класса, в котором заменяется метод
 *
 * @param string $methodname              Имя заменяемого метода
 *
 * @param string $args              Список параметров, принимаемых методом через запятую
 *
 * @param string $code              Новый код метода, который выполнится при вызове       methodname
 *
 * @param int $flags              Переназначаемый метод может быть        RUNKIT_ACC_PUBLIC,       RUNKIT_ACC_PROTECTED или       RUNKIT_ACC_PRIVATE            Замечание:                Этот параметр используется только в PHP 5, потому что в предыдущих         версиях все методы являлись публичными.
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-method-redefine.php
 *
 */
function runkit_method_redefine($classname, $methodname, $args, $code, $flags) {}



/**
 * Удаляет выбранный метод
 *
 * @param string $classname              Имя класса, из которого удаляется метод
 *
 * @param string $methodname              Имя удаляемого метода
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-method-remove.php
 *
 */
function runkit_method_remove($classname, $methodname) {}



/**
 * Переименовывает выбранный метод
 *
 * @param string $classname              Имя класса, в котором переименовывается метод
 *
 * @param string $methodname              Текущее имя переименовываемого метода
 *
 * @param string $newname              Новое имя переименовываемого метода
 *
 * @return    Возвращает TRUE в случае успешного завершения  или FALSE в случае возникновения ошибки.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-method-rename.php
 *
 */
function runkit_method_rename($classname, $methodname, $newname) {}



/**
 * Определяет, используется ли возвращаемое функцией значение
 *
 * @param
 *
 * @return    Возвращаеся TRUE, если возвращаемое функцией значение используется в    текущей области видимости, иначе FALSE
 *
 * @see http://md1.php.net/manual/ru/function.runkit-return-value-used.php
 *
 */
function runkit_return_value_used() {}



/**
 *    Задает функцию для захвата и/или обработки данных из "песочницы".
 *
 * @param object $sandbox              Экземпляр Runkit_Sandbox, вывод которого необходимо обрабатывать.
 *
 * @param mixed $callback              Имя функции для перехвата данных. Функция должна принимать один аргумент.       Вывод sandbox будет передан этой функции.       Все данные, возвращаемые функцией будут отображены в стандартном порядке.       Если этот параметр отсутствует, управление выводом песочницы не будет изменено.       Если указанной функции не существует, обработка вывода будет отключена и данные       будут выводиться в стандартном режиме.
 *
 * @return    Возвращает предыдущее имя функции или FALSE, если она не была задана.
 *
 * @see http://md1.php.net/manual/ru/function.runkit-sandbox-output-handler.php
 *
 */
function runkit_sandbox_output_handler($sandbox, $callback) {}



/**
 *    Возвращает индексный массив зарегистрированных суперглобальных переменных.
 *
 * @param
 *
 * @return    Возвращает индексный массив зарегистрированных переменных, например:    _GET, _POST, _REQUEST, _COOKIE, _SESSION, _SERVER, _ENV, _FILES
 *
 * @see http://md1.php.net/manual/ru/function.runkit-superglobals.php
 *
 */
function runkit_superglobals() {}



?>