<?php
if(!defined('BATTLE')) die('Доступ запрещен!');

/**
 * Класс парсинга ошибок пользователей
 * @package DCMS
 * @subpackage Модуль Фотобатлов для CiTY-HeaRTs.Ru
 * @version 1.1
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @date 18.08.2013
 */
class Error {
    
    const GLOBAL_NOT_AUTHORIZED     =   'Ошибка! Только для авторизированных пользователей';
    
    const INDEX_NOT_FOUND_ACTIVE    =   "<div class='waiting'>\n
                                            <div class='left'>\n
                                                <img src='template/assets/chat.png'>\n
                                            </div>\n
                                            <div class='right'>\n
                                                На данный момент нет активных батлов.<br>\n
                                                Присоединяйтесь и приводите друзей!<br>\n
                                                Участвуйте, побеждайте и получайте рейтинг %!
                                            </div>\n
                                         </div>\n";
    
    const WAITING_NO_COMPETITORS    =   'Нет никого в ожидании. Будьте первым участником и получайте рейтинг %!';
    const WAITING_NOT_FOUND         =   'Батл не найден, возможно его закрыл администратор :(';
    
    const MY_NOT_TAKE_A_PART        =   'Батла с вашим участием не найдено. Скорее всего он уже прошел :(';
    const MY_ERROR_PARAMS           =   'Ошибка системы! Не удалось получить параметры моего батла, возможно ктото лазил в базе';
    
    const JOIN_ONLY_FOR_VIP         =   'Батлы могут создавать только VIP пользователи!';
    const JOIN_BANNED               =   'Вы не можете принять участия в батле так как вы забанены!';
    const JOIN_USER_EXIST           =   'Вы уже принимаете участие в батле. Дождитесь пожалуйста его финала';
    const JOIN_ERROR_WRONG_GENDER   =   'Вы не можете присоединиться к этому батлу, так как в нем учавствует противоположный пол :))';
    const JOIN_ERROR_PARAMS         =   'Взлом не удался!';
    const JOIN_ERROR_LIMIT_DETECTED =   'На данный момент не возможно создать батл. Достиг лимит ожидающий старта батлов! Вы сможете только  присоединиться';
    const JOIN_NOT_FOUND            =   'Батл не найден, возможно его закрыл администратор :(';
    const JOIN_NONE_AVATAR          =   'Принимать участия могут только пользователи у которых установлен аватар';
    const JOIN_NONE_FREE            =   'К сожалению, для Вас нет свободных для участия мест. Вы можете организовать батл имея статус VIP';
    const JOIN_TO_LATE              =   'К сожалению вы опоздали на этот батл! Прием заявок окончен';
    
    const ACTIVE_NO_COMPETITORS     =   'Активных батлов нет! Присоединяйся и получай рейтинг %!';
    const ACTIVE_NOT_FOUND          =   'Батл не найден, возможно его закрыл администратор :(';
    const ACTIVE_USER_NOT_FOUND     =   'Пользователь, за которого вы хотите проголосовать, не найден.';
    const ACTIVE_VOTE_WRONG_GENDER  =   'Голосование разрешено только за участника противоположного пола';
    const ACTIVE_VOTE_EXIST         =   'Вы уже проголосовали в этом батле единожды! Сдесь для вас голосование закрыто';

}
