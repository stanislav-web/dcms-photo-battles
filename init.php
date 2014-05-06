<?php
/**
 * Иницилизатор ядра CMS
 * @package DCMS
 * @subpackage Модуль Фотобатлов для CiTY-HeaRTs.Ru
 * @version 1.1
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @date 18.08.2013
 */

// Это можно настраивать

define('MAX', 5);                           // макс. кол-во участников
define('HOURS', 24);                        // время в часах, на сколько проводить батлы
define('POINTS', 1);                        // сколько рейтинга получают за каждый голос
define('MINADMIN', 7);                      // минимальный уровень администрирования
define('MAXNONACTIVE', 5);                  // максимальное кол-во не начатых батлов одного пола (т.е , сколько максимум хранить в ожидании)

////////////////////////////////////////////////////////////////////////////////

define('BATTLE', true);                     // защата от прямого доступа к Модели, Контроллеру и шаблонам
define('DS', DIRECTORY_SEPARATOR);          // разделитель директорий по умолчанию
define('ROOT', $_SERVER['DOCUMENT_ROOT']);  // корневая директория
define('MODULE', 'battles');                // название модуля
define('TABLE', 'mod_battles');             // таблица категорий батла (Category)
define('TABLEVIEW', 'mod_battles_view');    // таблица содержимого категорий (Products)
define('TABLEVOTES', 'mod_battles_votes');  // таблица записи голосов (Properties)

/**
 * Системные функции ядра DCMS
 */
require_once ROOT.DS.'sys'.DS.'inc'.DS.'start.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'compress.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'sess.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'home.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'settings.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'db_connect.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'ipua.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'fnc.php';
require_once ROOT.DS.'sys'.DS.'inc'.DS.'user.php';


$__autoload = array(   // строго в таком порядке
    'Debug'            => ROOT.DS.MODULE.DS.'Helper'.DS.'Debug.php',
    'Error'            => ROOT.DS.MODULE.DS.'Helper'.DS.'Error.php',
    'Success'          => ROOT.DS.MODULE.DS.'Helper'.DS.'Success.php',
    'IndexModel'       => ROOT.DS.MODULE.DS.'Model'.DS.'IndexModel.php',
    'IndexController'  => ROOT.DS.MODULE.DS.'Controller'.DS.'IndexController.php',
    'IndexView'        => ROOT.DS.MODULE.DS.'View'.DS.'IndexView.php',
    'Format'           => ROOT.DS.MODULE.DS.'Helper'.DS.'Format.php',
);

/**
 * __autoload($class) Автозагрузчик классов
 * @param string $class название класса
 * @access public
 * @return class подключенный класс
 */
function __autoload($class)
{
    global $__autoload;
    if(isset($__autoload[$class]))
    {
        if(!file_exists($__autoload[$class])) throw new Exception('В автозагрузке не найден служебный класс '.$__autoload[$class].' . Возможно он был удален случайно');
        else require_once($__autoload[$class]);
    }
}

// строго в таком порядке

__autoload('Debug');
__autoload('Error');
__autoload('Success');
__autoload('IndexModel');
__autoload('IndexController');
__autoload('IndexView');
__autoload('Format');