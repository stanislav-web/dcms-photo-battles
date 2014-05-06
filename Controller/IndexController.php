<?php
if(!defined('BATTLE')) die('Доступ запрещен!');

/**
 * Контроллер приложения
 * @package DCMS
 * @subpackage Модуль Фотобатлов для CiTY-HeaRTs.Ru
 * @version 1.1
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @date 18.08.2013
 */
class IndexController extends IndexModel {
    
    /**
     * Задаю заголовки контроллерам,
     * отвечающих за вывод страниц
     */
    const INDEX_TITLE               =   'Фото Батлы';    
    const RULES_TITLE               =   'Правила проведения батлов';
    const ACTIVE_TITLE              =   'Активные батлы';
    const WAITING_TITLE             =   'Ожидающие противников батлы';
    const JOIN_TITLE                =   'Присоединиться';
    const MY_TITLE                  =   'Мой батл';
    
    /**
     * Массив для фильтрации $_GET параметров
     * @access public
     * @var array $GET_data
     */
    public $GET = array();

    /**
     * Массив для фильтрации $_POST параметров
     * @access public
     * @deprecated since version 1.0 Не использую... Так чисто для примера
     * @var array $POST_data
     */
    public $POST = array();

    /**
     * Массив для фильтрации $_COOKIE параметров
     * @access public
     * @deprecated since version 1.0 Не использую... Так чисто для примера
     * @var array $COOK_data
     */
    public $COOK = array();

    /**
     * Массив для хранения переменных , передаваемых в шаблон
     * @access private
     * @var array $_var
     */
    private $_var = array();

    /**
     * Хранение переменных настроек системы
     * @access protected
     * @var string $_SET
     */
    protected $_SET = array();

    /**
     * Хранение переменных авторизации юзера
     * @access protected
     * @var string $_USER
     */
    protected $_USER = array();
    
    /**
     * Состояние об ошибках
     * @access private
     * @var boolean $_ERROR
     */
    private $_ERROR = false;
    
    /**
     * Конструктор инициализации входящих параметров
     * @@return object
     */
    function __construct($set, $user, $db)
    {
        if(!empty($set))     $this->_SET = $this->__filter_vars($set);
        else                 throw new Exception('Не определен массив с настройками!');
        parent::__construct($db); // вызываем родительский конструктор
        
        if(!empty($user))    
        {
            $this->_USER = $this->__filter_vars($user);
			
            /**
             * Тут же в BootsTrap делаю проверку на VIP
             */
			 
            $vip = $this->getStatusVipUser();
            if($vip) $this->_USER['vip'] = 1;
            
            $this->getBoootsTrap();
	}
        else $this->_ERROR = Error::GLOBAL_NOT_AUTHORIZED;
                  
        /*
         * Фильтрую входящие данные
         */
        $this->GET  =       $this->__filter_vars($_GET);
        $this->POST =       $this->__filter_vars($_POST);
        $this->COOK =       $this->__filter_vars($_COOKIE);
    }

    /**
     * __data_clean($input) - Метод очистки суперглобальных массивов
     * @access protected
     * @param string $input строка , которую необходимо обработать
     * @@return string обраьботанная строка
     */
    protected function __data_clean($input)
    {
        return strip_tags(htmlspecialchars(trim(mysql_real_escape_string($input))));
    }
    
    /**
     * __filter_vars($input) - Метод фильтрации соперглобальных массивов GPC
     * @access protected
     * @param string $input строка, которую необходимо обработать
     * @@return string
     */
    protected function __filter_vars($input)
    {
        foreach($input as $k => &$v)
        {
            if(is_array($v)) $v = $this->__filter_vars($v);
            else $v = self::__data_clean($v);
            unset($v);
        }
        return $input;
    }
    
    /**
     * _setobj($name, $value) - Метод установки переменных в шаблон
     * @param string $name имя в шаблоне
     * @param mixed $value значение переменной
     * @access protected
     * @return mixed
     */
    protected function _setobj($name, $value)
    {
        return $this->_var[$name] = $value;
    }

    /**
     * __get($name) - Магический метод для сокращения цепочки свойств (переменных)
     * @param string $name имя для массива
     * @access private
     * @@return mixed object
     */
    private function __get($name)
    {
        if(isset($this->_var[$name])) return $this->_var[$name];
        return '';
    }
    
    /**
     *  processTo($view) редирект на вид
     *  @access protected
     *  @return array
     */
    protected function processTo($view)
    {
        header('Location: ?view='.$view);
    } 
    
    /**
     *  adminAction() Действия админа
     *  Простенький терминальный экшн
     *  @access protected
     *  @return array
     */
    protected function adminAction()
    {   
        // Защищаю от лузеров )))
        if($this->_ERROR) $this->processTo('index');  
        if($this->_USER[group_access] < MINADMIN) $this->processTo('index');        
        
        // Все хорошо, теперь работаю
        
        if($this->GET) // Действия только по ссылкам
        {
            switch($this->GET['action'])
            {
                case 'userdel': // удаление пользователя из батла
                    if(isset($this->GET['id']))
                    {
                        //@TODO Доделать декремент счетчика участников при удалении
                        $url = parse_url($_SERVER['HTTP_REFERER']);		
			parse_str($url['query'], $arr);
			$action    = isset($arr['view']) ? $arr['view'] : 'index'; 
                        if($this->dropObject(array(
                            'user_id'   =>  $this->GET['id'],
                        ), TABLEVIEW)) $this->processTo($action);
                    }
                break;
				
                case 'battledel': // Удаление батла и всех его участников
                    if(isset($this->GET['id']))
                    {
			$url = parse_url($_SERVER['HTTP_REFERER']);		
			parse_str($url['query'], $arr);
			$action    = isset($arr['view']) ? $arr['view'] : 'index';
						
			if($this->dropObject(array(
                            'id'   =>  $this->GET['id'],
                        ), TABLE))
			{
                            // Удален батл, удаляю всех его участников
                            if($this->dropObject(array(
				'battle_id'   =>  $this->GET['id'],
                            ), TABLEVIEW)) $this->processTo($action);    				
			}
                    }
                break;            
                default:
                    $this->processTo('index');    
                break;
            }
        }
        else  $this->processTo('index');
    } 
    
    /**
     *  indexAction() Обработчик главной
     *  @access protected
     *  @return array
     */
    protected function indexAction()
    {
        if(!$this->_ERROR)
        {
            $items = $this->getRandCompetitors();
            if(!$items) $this->_ERROR = Error::INDEX_NOT_FOUND_ACTIVE;
            else
            {
                // Вывожу рандомно активных участников
                $this->_setobj('items', $items);   
            }
        }
        
        $this->_setobj('error', $this->_ERROR);  // ошибка
        $this->_SET[title] = self::INDEX_TITLE;
        $this->_setobj('activec', count($this->getBattlesByStatus(1)));  // активные
        $this->_setobj('deactivec', count($this->getBattlesByStatus(0)));  // неактивные        
        $params = array('set' => $this->_SET, 'user' => $this->_USER, 'db' => $this->_DB); // перекручиваю DCMS )))
        return $params;
    }
    
    /**
     *  rulesAction() Обработчик правил
     *  @access protected
     *  @return array
     */
    protected function rulesAction()
    {
        if($this->_ERROR) $this->_setobj('error', $this->_ERROR);  // ошибка         
        
        $this->_SET[title] = self::RULES_TITLE;
        $this->_setobj('activec', count($this->getBattlesByStatus(1)));  // активные
        $this->_setobj('deactivec', count($this->getBattlesByStatus(0)));  // неактивные        
        $params = array('set' => $this->_SET, 'user' => $this->_USER, 'db' => $this->_DB); // перекручиваю DCMS )))
        return $params;
    } 
    
    /**
     *  activeAction() Ожидающие батлы
     *  @access protected
     *  @return array
     */
    protected function activeAction()
    {
        if(!$this->_ERROR)
        {  
            $bID = $this->getBattlesByStatus(1); // получаю батлы, которые уже проходят 
            //Debug::deBug($bID);
            if(!isset($this->GET['details']))
            {   
                if(!$bID) 
                {
                    // Нет активных батлов
                    $this->_ERROR = Error::ACTIVE_NO_COMPETITORS;                    
                    $this->_setobj('error', $this->_ERROR);          
                }
        
                if(!$this->_ERROR)
                {
                    // Если батлы есть, передаю параметры на выход

                    $chek = $this->getMyAnyBattle();
                    $itemResult = array();
                    foreach ($bID as $v)
                    {
                        // Делаю мердж объектов, чтобы связать батл с пользовтелем и голосами
                        // который был последний
                        $itemResult[$v->id] = array_merge(
                                (array)$this->getLiderCompetitorByBattleId($v->id),
                                (array)$v
                        );  
			if($v->id == $chek->jid) $itemResult[$v->id]['mine'] = 1;					
                    }
                    
                    $this->_setobj('mine', $this->getCompetitor(1));              
                    $this->_setobj('items', $itemResult);              
                    $this->_setobj('category', $chek);              
                }            
            }
            else // Детальный просмотр батла по ID
            {
                // Делаю проверку, туда ли мы попали?
                $bParams = $this->getBattleParamsByID($this->GET['details']);
                if(!$bParams) // Такой батл не найден
                {   
                    $this->_ERROR = Error::ACTIVE_NOT_FOUND;                    
                    $this->_setobj('error', $this->_ERROR);                      
                }
                else
                {
                    $issetVote = $this->issetVote($this->GET['details']); // Проверка вхождения голоса в батл
                    
                    if(isset($this->GET['vote']) && is_numeric($this->GET['vote'])) //  @TODO Работа над голосованием
                    {
                        $user = $this->getUserById($this->GET['vote']);
                        if(!$user) // Проверяю есть ли вообще у нас такой участник и учавствует ли он?
                        {
                            $this->_ERROR = Error::ACTIVE_USER_NOT_FOUND;                    
                            $this->_setobj('error', $this->_ERROR);                             
                        }
                        elseif($this->USER['pol'] == $bID[0]->gender) // Проверяю, идет ли голос за противоположный пол?
                        {
                            $this->_ERROR = Error::ACTIVE_VOTE_WRONG_GENDER;                    
                            $this->_setobj('error', $this->_ERROR);   
                        }
                        elseif($issetVote) // Проверка на голосование в этом бате
                        {
                            $this->_ERROR = Error::ACTIVE_VOTE_EXIST;                    
                            $this->_setobj('error', $this->_ERROR);                              
                        }
                        else // Все отлично! Голос можно подавать ;))
                        {
                            $this->setVote($this->GET['vote'], $this->GET['details']);
                            $this->_setobj('success', Success::template(Success::ACTIVE_VOTE_SUCCESS, $this->getUserParamsById($this->GET['vote'])->nick));
                            // перегружаю заново этот метод, так как необъодимо пересчитать голоса
                            $bParams = $this->getBattleParamsByID($this->GET['details']);

                        }
                    }
                    $this->_setobj('isset', $issetVote);
                    $cParams = $this->getMyBattleCategoryParams($this->GET['details']); // параметры категории батла
                    $this->_setobj('items', $bParams);                 
                    $this->_setobj('category', $cParams);              
                }
            }            
        }   
        else $this->_setobj('error', $this->_ERROR);  // ошибка
        
        $this->_SET[title] = self::ACTIVE_TITLE;
        $this->_setobj('activec', count($this->getBattlesByStatus(1)));  // активные
        $this->_setobj('deactivec', count($this->getBattlesByStatus(0)));  // неактивные        
        $params = array('set' => $this->_SET, 'user' => $this->_USER, 'db' => $this->_DB); // перекручиваю DCMS )))
        return $params;
    } 
    
    /**
     *  waitingAction() Ожидающие батлы
     *  @access protected
     *  @return array
     */
    protected function waitingAction()
    {
        if(!$this->_ERROR)
        {  
            $bID = $this->getBattlesByStatus(0); // получаю батлы, которые еще не начались 
            //Debug::deBug($bID);
            if(!isset($this->GET['details']))
            {   
                if(!$bID) 
                {
                    // Батлов еще нет
                    $this->_ERROR = Error::WAITING_NO_COMPETITORS;                    
                    $this->_setobj('error', $this->_ERROR);          
                }
        
                if(!$this->_ERROR)
                {
                    // Если батлы есть, передаю параметры на выход
                    $chek = $this->getMyAnyBattle();
                    $itemResult = array();
                    foreach ($bID as $v)
                    {
                        // Делаю мердж объектов, чтобы связать батл с пользовтелем
                        // который был последний
                        $itemResult[$v->id] = array_merge(
                                (array)$this->getLastCompetitorByBattleId($v->id),
                                (array)$v
                        );  
			if($v->id == $chek->jid) $itemResult[$v->id]['mine'] = 1;					
                    }
                    
                    $this->_setobj('mine', $this->getCompetitor(0));              
                    $this->_setobj('items', $itemResult);              
                    $this->_setobj('category', $chek);              
                }            
            }
            else // Детальный просмотр батла по ID
            {
                // Делаю проверку, туда ли мы попали?
                $bParams = $this->getBattleParamsByID($this->GET['details']);
                if(!$bParams) // Такой батл не найден
                {   
                    $this->_ERROR = Error::WAITING_NOT_FOUND;                    
                    $this->_setobj('error', $this->_ERROR);                      
                }
                else
                {
                    $cParams = $this->getMyBattleCategoryParams($this->GET['details']); // параметры категории батла
                    $this->_setobj('items', $bParams);                 
                    $this->_setobj('category', $cParams);              
                }
            }            
        }   
        else $this->_setobj('error', $this->_ERROR);  // ошибка
        
        $this->_SET[title] = self::WAITING_TITLE;
        $this->_setobj('activec', count($this->getBattlesByStatus(1)));  // активные
        $this->_setobj('deactivec', count($this->getBattlesByStatus(0)));  // неактивные        
        $params = array('set' => $this->_SET, 'user' => $this->_USER, 'db' => $this->_DB); // перекручиваю DCMS )))
        return $params;
    } 
    
    /**
     *  joinAction() Страница создания батла
     *  @access protected
     *  @return array
     */
    protected function joinAction()
    {
        if(!$this->_ERROR) // Не менять приоритет проверки ошибок!
        { 
            if($this->_USER['ban'] > 0 || $this->_USER['ban_pr']) // Ошибка, если забанен
            {
                $this->_ERROR = Error::JOIN_BANNED;                
                $this->_setobj('error', $this->_ERROR);
            }

            $ava = $this->getAvatar();

            if(!$ava) // Ошибка, если юзер пытается нырнуть без аватары  
            {
                $this->_ERROR = Error::JOIN_NONE_AVATAR;                
                $this->_setobj('error', $this->_ERROR);                
            }            
            
            if($this->getCompetitor() && !$this->_ERROR)// Ошибка! Батл не создан, пользователь уже учавствует
            {
                $this->_ERROR = Error::JOIN_USER_EXIST;   
                $this->_setobj('error', $this->_ERROR);      
            }
            if(!empty($this->POST) && !$this->_ERROR)
            {
                if(isset($this->POST['create']))
                {
                    
                    //@TODO Доделать проверку на лимит батлов для одного пола
                    //@see init.php с настройками лимитов
                    $countG = $this->countByGender($this->_USER['pol']);
                    if($countG->count_gender >= MAXNONACTIVE) // Ошибка при создании батла, если достиг лимит однопольных батлов в загажнике ))
                    {
                        $this->_ERROR = Error::JOIN_ERROR_LIMIT_DETECTED;                
                        $this->_setobj('error', $this->_ERROR);
                    } 
                                        
                    // Создаю батл
                    if($this->_USER['vip'] < 1 && !$this->_ERROR) // Ошибка при создании батла, если это не VIP
                    {
                        $this->_ERROR = Error::JOIN_ONLY_FOR_VIP;                
                        $this->_setobj('error', $this->_ERROR);
                    } 
                    
                    if(!$this->_ERROR)
                    {
                        //@TODO Создаю VIP'ом
                        $this->setBattleView($this->setBattle());
                        $this->_setobj('success', Success::JOIN_CREATE_SUCCESS);
                        
                        $this->sendMailPrivate( // Отправляю письмо в приват
                                $this->_USER['id'],
                                Success::template(Success::JOIN_CREATE_PRIVAT, DS.MODULE.DS.'?view=my')
                        );
                    }
                }
                elseif(isset($this->POST['join']))
                {
                    if(isset($this->POST['details'])) // присоединяюсь к выбранному
                    {
                        // Делаю повторную проверку на всякий случай
                        // Программа должна работать как этого хочет этого программист
                        // а не лузер

                        // Делаю проверку, туда ли мы попали?
                        $bParams = $this->getBattleParamsByID($this->POST['details']);
                        if(!$bParams) // Такой батл не найден
                        {   
                            $this->_ERROR = Error::JOIN_NOT_FOUND;                    
                            $this->_setobj('error', $this->_ERROR);                      
                        }                        
                        else
                        {
                            // Делаю проверку пола ))
                            $cParams = $this->getMyBattleCategoryParams($this->POST['details']); // параметры категории батла
                            if($this->_USER['pol'] != $cParams->gender)
                            {
                                $this->_ERROR = Error::JOIN_ERROR_WRONG_GENDER;            
                                $this->_setobj('error', $this->_ERROR);                    
                            }
                            elseif($cParams->active == 1) // проверка на доступность батла, еси он не укомплектован
                            {
                                $this->_ERROR = Error::JOIN_TO_LATE;            
                                $this->_setobj('error', $this->_ERROR);                         
                            }
                            else
                            {
                                // Все нормально! Поздравляем, вы попали туда куда хотели )))
                                // Записываю его по $freeBattle->id, но кол-во игроков увеличиваю на 1
								
				// Записываю пользователя в выбранный батл
								
				$this->setBattleView($this->POST['details']);										
				$this->setBattle(1, $this->POST['details']);
								
				// Присоединяю пользователя
                                $this->_setobj('success', Success::JOIN_MERGE_SUCCESS);
                                
                                $this->sendMailPrivate( // Отправляю письмо в приват
                                    $this->_USER['id'],
                                    Success::template(Success::JOIN_MERGE_PRIVAT, DS.MODULE.DS.'?view=my')
                                );                                
                            }
                        }
                    }
                    else
                    {
                        // Присоединяюсь к ближайшему
                        
                        $freeBattle = $this->getFreeBattle(); // ищу свободные батлы для этого пользователя и возвращаю его ID
                        if(!isset($freeBattle->id))
                        {
                            // Если нет свободных батлов, выдаю сообщение об ошибке
                            $this->_ERROR = Error::JOIN_NONE_FREE;                
                            $this->_setobj('error', $this->_ERROR);                        
                        }
                        else
                        {
                            // Записываю его по $freeBattle->id, и кол-во игроков увеличиваю на 1
                            $this->setBattleView($this->setBattle(1, $freeBattle->id));
                            $this->_setobj('success', Success::JOIN_MERGE_SUCCESS);
                            
                            // Проверяю, последний ли это участник?
                            // Если да, то стартую батл и отправляю письмо уже о старте
                            
                            if(($freeBattle->players + 1) == MAX)
                            {
                                // Старт!
                                $time = $this->setStart($freeBattle->id);
                                // Уведомляю пользователя о старте
                                $this->sendMailPrivate( // Отправляю письмо в приват
                                    $this->_USER['id'],
                                    Success::template(Success::JOIN_START, Format::dateFormat($time), DS.MODULE.DS.'?view=my')
                                );
                            }
                            else
                            {
                                // Уведомляю пользователя об ожидании
                                $this->sendMailPrivate( // Отправляю письмо в приват
                                    $this->_USER['id'],
                                    Success::template(Success::JOIN_MERGE_PRIVAT, DS.MODULE.DS.'?view=my')
                                );
                            }
                        }                          
                    }
                }
                else
                {
                    // Ошибка, не верные параметры
                    $this->_ERROR = Error::JOIN_ERROR_PARAMS;   
                    $this->_setobj('error', $this->_ERROR);                      
                }
            }
            
            if(isset($this->GET['details']) && !$this->_ERROR) // Детальный просмотр батла по ID с возможностью присоединиться
            {
                // Делаю проверку, туда ли мы попали?
                $bParams = $this->getBattleParamsByID($this->GET['details']);
                if(!$bParams) // Такой батл не найден
                {   
                    $this->_ERROR = Error::JOIN_NOT_FOUND;                    
                    $this->_setobj('error', $this->_ERROR);                      
                }
                else
                {
                    // Делаю проверку пола ))
                    $cParams = $this->getMyBattleCategoryParams($this->GET['details']); // параметры категории батла
                    
                    if($this->_USER['pol'] != $cParams->gender)
                    {
                        $this->_ERROR = Error::JOIN_ERROR_WRONG_GENDER;            
                        $this->_setobj('error', $this->_ERROR);                    
                    }
                    elseif($cParams->active == 1)
                    {
                        $this->_ERROR = Error::JOIN_TO_LATE;            
                        $this->_setobj('error', $this->_ERROR);                         
                    }
                    else
                    {
                        $this->_setobj('items', $bParams);                 
                        $this->_setobj('category', $cParams);
                    }
                }
            }
        }
        else $this->_setobj('error', $this->_ERROR);  // ошибка
        
        $this->_SET[title] = self::JOIN_TITLE;
        $this->_setobj('activec',   count($this->getBattlesByStatus(1)));  // активные
        $this->_setobj('deactivec', count($this->getBattlesByStatus(0)));  // неактивные        
        $params = array('set' => $this->_SET, 'user' => $this->_USER, 'db' => $this->_DB); // перекручиваю DCMS )))
        return $params;
    }     
    
    /**
     *  myAction() Обработчик для Моих Батлов
     *  @access protected
     *  @return array
     */
    protected function myAction()
    {
        if(!$this->_ERROR)
        {           
            $bID = $this->getMyBattleID(); // получаю ID своего батла
            if(!$bID) 
            {
                // Я без участия
                $this->_ERROR = Error::MY_NOT_TAKE_A_PART;
                $this->_setobj('error', $this->_ERROR);  // я нигде не учавствую        
            }
            else
            {
                $bPrams = $this->getBattleParamsByID($bID->battle_id);
                if(empty($bPrams))
                {
                    // Получаю параметры батла
                    $this->_ERROR = Error::MY_ERROR_PARAMS;
                    $this->_setobj('error', $this->_ERROR);  // ошибка;  
                }                
                else
                {
                    // Все нормально. Если батлы есть, передаю параметры на выход
                    $cParams = $this->getMyBattleCategoryParams($bID->battle_id); // параметры категории батла
                    $this->_setobj('category', $cParams);              
                    $this->_setobj('items', $bPrams);                        
                }
            }
        }
        else $this->_setobj('error', $this->_ERROR);  // ошибка
        $this->_setobj('activec', count($this->getBattlesByStatus(1)));  // активные
        $this->_setobj('deactivec', count($this->getBattlesByStatus(0)));  // неактивные        
        $this->_SET[title] = self::MY_TITLE;
        $params = array('set' => $this->_SET, 'user' => $this->_USER, 'db' => $this->_DB); // перекручиваю DCMS )))
        return $params;        
    }     
}
