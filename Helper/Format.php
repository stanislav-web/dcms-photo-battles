<?php
/**
 * Класс форматирования различного вида строк. Вытащил из своего Zend'a ))
 * @package Zend Framework 2
 * @subpackage SWEB Library
 * @since PHP >=5.3.xx
 * @version 2.15
 * @author Stanislav WEB | Lugansk <stnisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /vendor/SW/library/String/Format.php
 */
class Format {
    
    /**
     * declareRight($number, $array) Метод спряжения окончаний числительных
     * @param int $number номер 0-9
     * @param array $array массив со спряжениями окончаний array('день', 'дня', 'дней')
     * @return string результат преобразования
     */
    public static function declareRight($number, $array)
    {   
        $cases = array(2, 0, 1, 1, 1, 2);
        return $number." ".$array[($number%100>4&&$number%100<20) ? 2 : $cases[min($number%10, 5)]];
    }
    
    /**
     * dateFormat($time) Вывод читабельной даты
     * @param int (timestamp) $time UNIX time
     * @return string результат преобразования в читабельный вид
     */    
    public static function dateFormat($time)
    {
        $times = date('H:i',$time);
        
        $day = date('j',$time);
        $aMonths = array('Дек', 'Янв', 'Фев', 'Марта', 'Апр', 'Мая', 'Июня', 'Июля', 'Авг', 'Сен', 'Окт', 'Ноя');
        $month = date('n',$time);
        $name_month = $aMonths[$month];
        return $day.' '.$name_month.' в '.$times;       
    }  
    
    /**
     * getOverTime($start_time, $end_time, $std_format = false) Метод определения разницы времени из timestamp формата
     * @param type  $start_time time()
     * @param type  $end_time время окончания
     * @param boolen  $std_format форматирование
     * @return string результат преобразования
     */
    public static function getOverTime($start_time, $end_time, $std_format = false)
    {       
        $total_time = $end_time - $start_time;
        $days       = floor($total_time /86400);        
        $hours      = floor($total_time /3600);     
        $minutes    = intval(($total_time/60) % 60);        
        $seconds    = intval($total_time % 60);     
        $results = "";
        if($std_format == false)
        {
            if($days > 0)       $results  .= self::declareRight($days,     array('день', 'дня', 'дней')). " ";     
            if($hours > 0)      $results  .= self::declareRight($hours,    array('час', 'часа', 'часов')). " ";          
            if($minutes > 0)    $results  .= self::declareRight($minutes,  array('минута', 'минуты', 'минут')). " ";  
            //if($seconds > 0)    $results  .= self::declareRight($seconds,  array('секунда', 'секунды', 'секунд'));
        }
        else
        {
            if($days > 0) $results = self::declareRight($days,     array('день', 'дня', 'дней'));
            $results = sprintf("%s%02d:%02d:%02d",$results, $hours, $minutes, $seconds);
        }
        return $results;
    }
}