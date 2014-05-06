<?php
/**
 * @subpackage Модуль Фотобатлов для CiTY-HeaRTs.Ru
 * Точка входа в приложение
 * Инициализация автозагрузчика библиотек DCMS
 * Перехват исключений из контроллеров и Моделей
 * Все обращения и операции, будет обрабатывать этот файл
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @date 18.08.2013
 */
ini_set('display_errors', 1);
error_reporting('E_ALL');

try {
    
    if(file_exists('init.php')) require_once 'init.php'; // Инициализация этой CMS
    else die('Не найден файл инициализации!');

    $app = new IndexView($set, $user, $db); // инициализирую объект Вида для Модуля
    $app->dispatch(); // вызываю диспетчер для запуска приложения
}
catch(Exception $e)
{
     echo $e->getMessage(); // если что не так в системе , делаю throw
}