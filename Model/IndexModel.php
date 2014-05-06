<?php
if(!defined('BATTLE')) die('Доступ запрещен!');
/**
 * Модель приложения
 * @package DCMS
 * @subpackage Модуль Фотобатлов для CiTY-HeaRTs.Ru
 * @version 1.1
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @date 18.08.2013
 */
class IndexModel {

    protected $_DB = null;

    /**
     * Конструктор инициализации входящих параметров
     * Жаль что сделано не через PDO, и я тут имею уже ID соединения,
     * поэтому сделаю просто QUERY обертку (((
     * @param object $db Resource #id
     * @return object DCMS DB Id
     */
    function __construct($db)
    {
        $this->_DB = $db;
        return $this->_DB;
    }

    /**
     * getObjects($sql) Описываю CRUD действия. Чтение в объект (множественный результат)
     * @param string $sql SQL query 
     * @return objects
     */
    protected function getObjects($sql)
    {
        //Debug::deBug($sql);
        $query = mysql_query($sql, $this->_DB);
        if(!$query) throw new Exception('Ошибка при выборке нескольких объектов: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
        else
        {
            $return = array();
            while($line = mysql_fetch_object($query))
            {
                $result[] = $line;
            }
            return $result;            
        }
    }
    
    /**
     * getObject($sql) Описываю CRUD действия. Чтение в объект (одно)
     * @param string $sql SQL query 
     * @return object
     */
    protected function getObject($sql)
    {
        //Debug::deBug($sql);
        $query = mysql_query($sql, $this->_DB);
        if(!$query) throw new Exception('Ошибка при выборке объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
        else
        {
            $line = mysql_fetch_object($query);
            return $line;
        }
    }
    
    /**
     * dropObject($sql) Описываю CRUD действия. Удаление записи (одно)
     * @param array $st выражение ( ключ => значение как INT!!)
     * @param string $table таблица
     * @return type
     */
    protected function dropObject($st = array(), $table)
    {
        $key = key($st);
        $sql = "DELETE FROM `".$table."` WHERE `".$key."` = ".$st[$key];
        $query = mysql_query("DELETE FROM `".$table."` WHERE `".$key."` = ".$st[$key], $this->_DB);
        if(!$query) throw new Exception('Ошибка при удалении объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
        else return true;
    }
    
    /**
     *  getMyBattleID() ID моего батла
     *  @return object db
     */
    protected function getMyBattleID()
    {
        $sql = "SELECT battle_id FROM `".TABLEVIEW."`
                WHERE user_id = ".(int)$this->_USER['id']." LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }
	
    /**
     *  getBoootsTrap() Самый важный! Определить в конструкторе контроллера
     *  Сверяед даты завершения и очищает все записи, категории, детали и голоса
     *  @return object db truncate all false rows
     */
    protected function getBoootsTrap()
    {
        
        // Сначала беру активных на проверку
        
        $sql = "SELECT id, finish FROM `".TABLE."` WHERE active = '1'";
        $res = $this->getObjects($sql);       
        
        // Делаю у всех проверку на окончания времени
        
        foreach($res as $v)
        {
            if(time() >= $v->finish) // >=
            {
                // Попались.... Прежде чем удалять,
                // необходимо сделать рассылку с результатами 0_0
                // Достаю всех участников
                $users = $this->getBattleParamsFortruncate($v->id);
                
                // Строю таблицу уастников
                $table = ''; $c = 1;
                foreach ($users as $t)
                {
                    if($c < 3) $table .= "[b]".$c++.". [url=/info.php?id=".$t->user_id."]".$this->getUserParamsById($t->user_id)->nick."[/url] - %".$t->votes."\n[/b]";
                    else $table .= $c++.". [url=/info.php?id=".$t->user_id."]".$this->getUserParamsById($t->user_id)->nick."[/url] - %".$t->votes."\n";
                }
                
                // Начисляю %рейтинг первым 2м. 0 - 1, 1 - 2 место
                for($i=0; $i<sizeof($users); $i++)
                {
                    if($i <=1) // это победители! Начисляю рейтинг и отправляю особые письма
                    {
                        $this->sendMailPrivate( // Отправляю письмо с результатами в приваты
                                    $users[$i]->user_id,
                                    Success::template(
                                            Success::FINAL_TABLE_WIN,
                                            ($i+1),
                                            $v->id,
                                            Format::declareRight($users[$i]->votes, array("голос", "голоса", "голосов")),
                                            $table)
                        );  
                        $this->setRating($users[$i]->user_id, $users[$i]->votes); // ретинг ++!
                    }
                    else
                    {
                        $this->sendMailPrivate( // Отправляю письмо с результатами в приваты
                                    $users[$i]->user_id,
                                   Success::template(
                                            Success::FINAL_TABLE_LOOSE,
                                            ($i+1),
                                            $v->id,
                                            Format::declareRight($users[$i]->votes, array("голос", "голоса", "голосов")),
                                            $table)
                        );  
                    }
                }
                
                // Наконец то удаляю!
                
                $this->dropObject(array('id'        =>  $v->id), TABLE);        // Категории
                $this->dropObject(array('battle_id' =>  $v->id), TABLEVIEW);    // Участники
                $this->dropObject(array('battle_id' =>  $v->id), TABLEVOTES);   // Голосования
            }
        }
    }    
    
    /**
     *  getRandCompetitors() Вывод участников случайным образом
     *  немного логики, чтобы было оптимально без RAND () и прочей херни
     *  Для ленивых показую
     *  @return object db
     */
    protected function getRandCompetitors()
    {
        $sql = "SELECT COUNT(*) FROM `".TABLEVIEW."` WHERE `status` = '1'";
        $count = mysql_result(mysql_query($sql, $this->_DB), 0);
        $query = array();
        while (sizeof($query) < MAX)
        {
            $query[] = "(SELECT * FROM `".TABLEVIEW."` WHERE `status` = '1' LIMIT ".rand(0, $count).", 1)";
        }
        $query = implode(' UNION ', $query);
        $res = mysql_query($query, $this->_DB); 
        if(!$res) throw new Exception('Ошибка при рандомизации объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));         else
        {
            $result = array();
            $return = array();
            while($line = mysql_fetch_object($res))
            {
                $result[] = $line;
            }            
            foreach ($result as $v)
            {
                $isset = array('isset' =>$this->issetVote($v->battle_id)); // Проверка вхождения голоса в батл
                $sql = "SELECT nick, ank_city, ank_o_sebe, pol FROM `user` WHERE  id = {$v->user_id} LIMIT 1";
                $return[$v->user_id] = array_merge(
                        (array)$this->getObject($sql),
                         $isset,
                        (array)$v);             
            }
            return $return;            
        }
    }    
    
    /**
     *  getStatusVipUser() Проверка на VIP статус пользователя
     *  @return object db
     */
    protected function getStatusVipUser()
    {
        $sql = "SELECT id FROM `vip_status_Saint` WHERE  id_user = ".(int)$this->_USER['id']." LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }

    /**
     * setRating($user_id, $votes) Установка гонорара за победу итп...
     * @param int $user_id
     * @param int $votes голоса
     * @return type
     */
    protected function setRating($user_id, $votes)
    {
        $sql = "UPDATE `user` SET `rating` = `rating` + ".$votes."
                    WHERE id = ".(int)$user_id." LIMIT 1";
        $query  = mysql_query($sql, $this->_DB);
        if(!$query) throw new Exception('Ошибка при обновлении объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));         
    }
    
   /**
     * setStart($battle_id) Установка гонорара за победу итп...
     * @param int $battle_id
     * @param int $votes голоса
     * @param int $place занятое место
     * @return type
     */
    protected function setStart($battle_id)
    {
        $finish = time()+(HOURS*60*60);
        $sqlB = "UPDATE `".TABLE."` SET `active` = '1', `start` = ".time().", `finish` = ".$finish."
                WHERE id = ".(int)$battle_id." LIMIT 1";
        
        $sqlV = "UPDATE `".TABLEVIEW."` SET `status` = '1'
                WHERE `battle_id` = ".(int)$battle_id." LIMIT 1";
        
        $queryB  = mysql_query($sqlB, $this->_DB);        
        if(!$queryB) throw new Exception('Ошибка при обновлении объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
        $queryV  = mysql_query($sqlV, $this->_DB);        
        if(!$queryV) throw new Exception('Ошибка при обновлении объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
        return $finish; // возвращаю время окончания
    }
    
   /**
     * setVote($battle_id) Голос за фото
     * @param int $user_id ID за кого голос
     * @param int $battle_id ID батла
     * @return boolean
     */
    protected function setVote($user_id, $battle_id)
    {
        $sqli = "INSERT INTO `".TABLEVOTES."` (`user_id`,`from_user_id`, `gender`, `battle_id`)
                VALUES (".(int)$user_id.", ".(int)$this->_USER['id'].", '".(int)$this->_USER['pol']."', ".(int)$battle_id.")";

        $sqlu = "UPDATE `".TABLEVIEW."` SET `votes` = `votes` + 1
                 WHERE user_id = ".(int)$user_id;
        $queryi  = mysql_query($sqli, $this->_DB);
        if(!$queryi) throw new Exception('Ошибка при записи в объект: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));         
        $queryu  = mysql_query($sqlu, $this->_DB);        
        if(!$queryu) throw new Exception('Ошибка при обновлении объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
        else return true;
    }
    
   /**
     * issetVote($battle_id) Проверка на голосование в этом батле
     * @param int $battle_id ID батла
     * @return boolen
     */
    protected function issetVote($battle_id)
    {
        $sql = "SELECT id FROM `".TABLEVOTES."`
                WHERE `from_user_id` = '".(int)$this->_USER['id']."' AND `battle_id` = ".(int)$battle_id." LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return true;
    }
    
   /**
     * getVote($user_id) Считаю голоса участника
     * @param int $battle_id ID батла
     * @return boolen
     */
    protected function getVote($user_id)
    {
        $sql = "SELECT COUNT(`id`) as votes FROM `".TABLEVOTES."`
                WHERE `user_id` = '".(int)$user_id."'";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }
    
    /**
     *  getMyAnyBattle() Поиск батла с моим участием
     *  @return object db
     */
    protected function getMyAnyBattle()
    {
        $sql = "SELECT Category.gender  as jgender, Category.id as jid FROM `".TABLEVIEW."` AS View
                INNER JOIN `".TABLE."` AS Category ON Category.id = View.battle_id
                WHERE View.user_id = ".(int)$this->_USER['id']." LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }
    
    /**
     *  getUserParamsById() Все свойства пользователя
     *  @return object db
     */
    protected function getUserParamsById($id)
    {
        $sql = "SELECT nick FROM `user` WHERE id = '".intval($id)."' LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }
    
    /**
     *  getLastCompetitorByBattleId($battle_id) последний участник батла
     *  @param int $battle_id ID батла
     *  @return object db
     */
    protected function getLastCompetitorByBattleId($battle_id)
    {
        $sql = "SELECT User.nick AS user, User.ank_city AS city, View.photo_id AS photo, View.ext AS ext, View.user_id AS user_id
                FROM `user` AS User
                INNER JOIN `".TABLEVIEW."` AS View ON View.user_id =  User.id
                WHERE View.battle_id = ".(int)$battle_id." ORDER BY View.id DESC LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }
    
    /**
     *  getLiderCompetitorByBattleId($battle_id) достаю лидера
     *  @param int $battle_id ID батла
     *  @return object db
     */
    protected function getLiderCompetitorByBattleId($battle_id)
    {
        $sql = "SELECT COUNT(Vote.user_id) AS maxvote,
                User.nick AS user, User.ank_city AS city, View.photo_id AS photo, View.ext AS ext, View.user_id AS user_id
                FROM `user` AS User
                INNER JOIN `".TABLEVOTES."` AS Vote ON Vote.user_id =  User.id
                INNER JOIN `".TABLEVIEW."` AS View ON View.user_id =  User.id

                WHERE Vote.battle_id = ".(int)$battle_id." ORDER BY Vote.id";
        $res = $this->getObject($sql);
        if(!$res) return false;
        else return $res;
    }
    
    /**
     *  getMyBattleCategoryParams($id) Общие параметры батла
     *  @return object db
     */
    protected function getMyBattleCategoryParams($id)
    {
        $sql = "SELECT * FROM `".TABLE."`
                WHERE id = ".(int)$id." LIMIT 1";
        $res = $this->getObject($sql);
        if(!$res)  false;
        else return $res;
    }
    
    /**
     *  countPlayers() Подсчитываю всех пользователей
     *  @return object db
     */
    protected function countPlayers()
    {
        $sql = "SELECT SUM(`players`) as allplayers FROM `".TABLE."`
                GROUP BY `active`";
        $res = $this->getObject($sql);
        if(!$res)  false;
        else return $res;
    }
    
    /**
     *  countByGender() Подсчитываю неукомплектованные батлы по Gender
     *  @return object db
     */
    protected function countByGender($gender_id)
    {
        $sql = "SELECT COUNT(`id`) as count_gender FROM `".TABLE."`
                `active` = '0' AND `gender` = ".(int)$gender_id;
        $res = $this->getObject($sql);
        if(!$res)  false;
        else return $res;
    }
    
    /**
     *  sendMailPrivate($id_user, $message) Отправка сообщений в приват
     *  необходимо для различных активов пользователей
     *  @param int $id_user ID пользователя
     *  @param string $message Текст сообщения
     *  @return object db
     */   
    protected function sendMailPrivate($id_user, $message)
    {
        $sql = "INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES (0, ".(int)$id_user.", '{$message}', ".time().")";
        $query  = mysql_query($sql, $this->_DB);
        if(!$query) throw new Exception('Ошибка при записи объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
    }
    
    /**
     *  getBattleParamsByID($id) Получаю все содержимое батла по ID
     *  @param int $id ID Батла
     *  @return object db
     */
    protected function getBattleParamsByID($id)
    {
        $sql = "SELECT * FROM `".TABLEVIEW."`
                WHERE battle_id = ".(int)$id." ORDER BY `votes` DESC";
        $res = $this->getObjects($sql);
        if(!$res) return false;
        else 
        {
            $result = array();
            foreach ($res as $v)
            {
                $sql = "SELECT nick, ank_city, ank_o_sebe FROM `user` WHERE  id = {$v->user_id} LIMIT 1";
                $result[$v->user_id] = array_merge(
                        (array)$this->getObject($sql),
                        //(array)$this->getVote($v->user_id),
                        (array)$v);             
            }
            return $result;
        }
    }
    
    /**
     *  getItems() Чтение таблицы
     *  @return object db
     */
    protected function getItems()
    {
        //Debug::deBug($this->_SET);

        $sql = "SELECT * FROM `".TABLE."`
                ORDER BY id DESC LIMIT ".MAX;
        $res = $this->getObjects($sql);
        if(!$res) return false;
        else return $res;
    }
    
    /**
     *  getCompetitor($status) Проверка на участие пользователя в батле
     *  @param int $status статус участника 0 - ожидает, 1 - играет
     *  @return object db
     */
    protected function getCompetitor($status = '')
    {
        if($status !='') $addSQL = "AND status = '".$status."'";
        $sql      = "SELECT id FROM `".TABLEVIEW."` WHERE user_id = ".(int)$this->_USER['id']." {$addSQL} ORDER BY id LIMIT 1";
        $res = $this->getObject($sql)->id;
        return $res;
    } 
    
    /**
     *  getUserById() Самый распространенный метод )) всея PHP
     *  @return object db
     */
    protected function getUserById($id)
    {
        $sql      = "SELECT * FROM `".TABLEVIEW."` WHERE user_id = ".(int)$id." LIMIT 1";
        $res = $this->getObject($sql);
        return $res;
    } 
    
    /**
     *  getBattleParamsFortruncate($battle_id) Достаю всех участников этого батла
     *  с параметрами для создания рассылки
     *  @param int $battle_id ID Батла
     *  @return object db
     */
    protected function getBattleParamsFortruncate($battle_id)
    {
        $sql      = "SELECT id, votes, user_id FROM `".TABLEVIEW."` WHERE battle_id = ".$battle_id." ORDER BY votes DESC";
        $res = $this->getObjects($sql);
        return $res;
    } 
    
    /**
     *  getFreeMaleBattle() Беру свободный баттл
     *  @return object db
     */
    protected function getFreeBattle()
    {
        $sql      = "SELECT id, players FROM `".TABLE."` WHERE players < ".MAX." AND  gender = ".(int)$this->_USER['pol']." AND `active` = '0' ORDER BY id LIMIT 1";
        
        $res = $this->getObject($sql);
        return $res;
    } 
    
    /**
     *  getBattlesByStatus($status) батлы по статусам
     *  @param int $status (1 - активные, 0 - нет)
     *  @access static
     *  @return object db
     */
    protected function getBattlesByStatus($status)
    {
        $sql      = "SELECT * FROM `".TABLE."` WHERE active = '{$status}' ORDER BY players, id  DESC";

        $res = $this->getObjects($sql);
        return $res;
    } 
    
    /**
     * setBattle($i = null, $id = null) Создаю (обновляю) батл с новым пользователем
     * @param int $i инкремент участников
     * @param int $id ID Батла
     * @return object db
     */
    protected function setBattle($i = null, $id = null)
    {
        $key = $this->setKey(); // Ключ как ID
        if(!$i)
        {   
            // Создаю батл
            $sql    = "INSERT INTO `".TABLE."` (`gender`, `players`, `key`) VALUES ('{$this->_USER['pol']}', 1, {$key})";
            $query  = mysql_query($sql, $this->_DB);
            if(!$query) throw new Exception('Ошибка при записи объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
            return  mysql_insert_id($this->_DB);
        }
        else
        {
            // Обновляю
            $sql    = "UPDATE `".TABLE."` SET `players` = `players` + {$i}, `key` = '{$key}' WHERE `id` = ".intval($id)." AND `gender` = '{$this->_USER['pol']}'";
            $query  = mysql_query($sql, $this->_DB);
            if(!$query) throw new Exception('Ошибка при обновлении объекта: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));
            return $this->getLastID($key)->id; // возвращаю id по ключу
        }
    }  
    
    /**
     * getLastID($key) Последний ID после UPDATE
     * @param mix $key ключ по которому идентифицирую последний UPDATE
     * @return object db
     */
    protected function getLastID($key)
    {
        $sql      = "SELECT id FROM `".TABLE."` WHERE `key` = '{$key}' ORDER BY id LIMIT 1";
        $res = $this->getObject($sql);          
        return $res;
    }    
    
    /**
     *  getAvatar() Достаю установленный аватар пользователя
     *  @return object db
     */
    protected function getAvatar()
    {
        $sql      = "SELECT id, ras FROM `gallery_foto` WHERE id_user = ".(int)$this->_USER['id']." AND avatar = '1' ORDER BY id LIMIT 1";
        
        $res = $this->getObject($sql);          
        return $res;
    }     
    
    /**
     *  setKey() Генератор уникального числа (ключ)
     *  @return string
     */
    protected function setKey()
    {
        $rand = time();
        return $rand;
    }   
    
    /**
     *  setBattleView($id) Устанавливаю параметры батла
     *  (прежде необходимо проверить, есть ли свободные места? но это писано выше)
     *  @param int $id ID батла
     *  @return object db
     */
    protected function setBattleView($id)
    {
        $avatar = $this->getAvatar();
        $sql    = "INSERT INTO `".TABLEVIEW."` (`user_id`, `photo_id`, `ext`, `battle_id`) VALUES ({$this->_USER['id']}, {$avatar->id}, '{$avatar->ras}', {$id})";		
        $query = mysql_query($sql, $this->_DB);
        if(!$query) throw new Exception('Ошибка при добавлении пользователя в батл: #'.mysql_errno($this->_DB).' '.mysql_error($this->_DB));		
        return true; // final set
    } 
    
    function __destruct()
    {
        $this->_DB = null;
    }
}
