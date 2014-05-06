<?php
if(!defined('BATTLE')) die('Доступ запрещен!');

/**
 * Диспетчер шаблонов
 * @package DCMS
 * @subpackage Модуль Фотобатлов для CiTY-HeaRTs.Ru
 * @version 1.1
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @date 18.08.2013
 */
class IndexView extends IndexController {

    /**
     * Доверенные шаблоны модуля
     * @access private
     * @var string $_TEMPLATES
     */
    private $_TEMPLATES = array(
        'index',
        'join',
        'active',
        'rules',
        'my',
        'waiting',
        'error',
        'admin', // терминальный вывод
        );

    /**
     * Конструктор инициализации входящих параметров из настроек
     * @param array $set Конигурация CMS
     * @param array $user Данные аунтификации
     * @param object $db Resource #id
     * @return object BattleView
     */
    function __construct($set, $user, $db)
    {
        parent::__construct($set, $user, $db); // вызываем родительский класс, передавая объект бд
    }

    /**
     * Метод __includeTemplate($name)включения шаблона в страницу
     * @param string $name имя шаблона без .html
     * @param array $params настройки конфигурации
     * @access private
     * @return file
     */
    private function __includeTemplate($name = 'index', $params)
    {
        $set    =   $params['set'];
        $user   =   $params['user'];
        $db     =   $params['db'];

        // Подключаю начинку DCMS        
        
        if(file_exists(ROOT.DS.MODULE.DS.'template'.DS.$name.'.html'))
        {
            if(file_exists(ROOT.DS.'sys'.DS.'inc'.DS.'thead.php')) require_once ROOT.DS.'sys'.DS.'inc'.DS.'thead.php';
            else throw new Exception(ROOT.DS.'sys'.DS.'inc'.DS.'thead.php - не найден');

            // Проверка аутентификации юзеров
            title($set, $user);
            aut($set[title]);  
            
            if(file_exists(ROOT.DS.MODULE.DS.'template'.DS.$name.'.html'))  require_once ROOT.DS.MODULE.DS.'template'.DS.$name.'.html';
            else  throw new Exception(ROOT.DS.MODULE.DS.'template'.DS.$name.'.html - шаблон не найден!');
            
            if(file_exists(ROOT.DS.'sys'.DS.'inc'.DS.'tfoot.php')) require_once ROOT.DS.'sys'.DS.'inc'.DS.'tfoot.php';
            else throw new Exception(ROOT.DS.'sys'.DS.'inc'.DS.'tfoot.php - не найден');
        }
        else throw new Exception('Шаблон '.ROOT.DS.MODULE.DS.'template'.DS.$name.'.html - не найден');
    }

    /**
     * dispatch() Диспетчер контроллеров
     * включает в шаблон объекты и отображает по условиям контроллеры
     * @access public final
     * @return view application
     */
    final public function dispatch()
    {        
        if(isset($this->GET['view']) && in_array($this->GET['view'], $this->_TEMPLATES))
        {
            switch($this->GET['view'])
            {
                case 'join':    // Присоединиться
                    $action = $this->joinAction();
                    if(!$action) throw new Exception('Не возможно выгрузить joinAction()');                      
                break;
            
                case 'waiting': // Ожидающие противников батлы
                    $action = $this->waitingAction();
                    if(!$action) throw new Exception('Не возможно выгрузить waitingAction()');                       
                break;
            
                case 'active':  // Все активные батлы
                    $action = $this->activeAction();
                    if(!$action) throw new Exception('Не возможно выгрузить activeAction()');                        
                break;
            
                case 'rules':   // Правила батлов
                    $action = $this->rulesAction();
                    if(!$action) throw new Exception('Не возможно выгрузить rulesAction()');                      
                break;
            
                case 'my':      // Мой батл
                    $action = $this->myAction();
                    if(!$action) throw new Exception('Не возможно выгрузить myAction()');                    
                break;
                case 'admin':   // Действия админа
                    $action = $this->adminAction();
                    if(!$action) throw new Exception('Не возможно выгрузить adminAction()');                    
                break;                
                default:        // Главная
                    $action = $this->indexAction();
                    if(!$action) throw new Exception('Не возможно выгрузить indexAction()');
                break;
            }
            $this->__includeTemplate($this->GET['view'], $action);  
        }
        else $this->__includeTemplate('index', $this->indexAction());
    }
}
