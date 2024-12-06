<?php
/*
Copyright 2017, https://asterisk-pbx.ru

This file is part of Asterisk Call Center Stats.
Asterisk Call Center Stats is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

Asterisk Call Center Stats is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Asterisk Call Center Stats.  If not, see
<http://www.gnu.org/licenses/>.
 */

$dayp[0] = "Воскресенье";
$dayp[1] = "Понедельник";
$dayp[2] = "Вторник";
$dayp[3] = "Среда";
$dayp[4] = "Четверг";
$dayp[5] = "Пятница";
$dayp[6] = "Суббота";

$yearp[0] = "Январь";
$yearp[1] = "Февраль";
$yearp[2] = "Март";
$yearp[3] = "Апрель";
$yearp[4] = "Май";
$yearp[5] = "Июнь";
$yearp[6] = "Июль";
$yearp[7] = "Август";
$yearp[8] = "Сентябрь";
$yearp[9] = "Октябрь";
$yearp[10] = "Ноябрь";
$yearp[11] = "Декабрь";

$months['01'] = "Январь";
$months['02'] = "Февраль";
$months['03'] = "Март";
$months['04'] = "Апрель";
$months['05'] = "Май";
$months['06'] = "Июнь";
$months['07'] = "Июль";
$months['08'] = "Август";
$months['09'] = "Сентябрь";
$months['10'] = "Октябрь";
$months['11'] = "Ноябрь";
$months['12'] = "Декабрь";

// Menu options
$lang['ru']['menu_home'] = "Главная";
$lang['ru']['menu_answered'] = "Принятые";
$lang['ru']['menu_unanswered'] = "Пропущенные";
$lang['ru']['menu_distribution'] = "Распределенные";
$lang['ru']['ans_cdr'] = "Прин. выз.";
$lang['ru']['unans_cdr'] = "Проп. выз.";
$lang['ru']['compare'] = "Сравнение";
$lang['ru']['realtime'] = "Реалтайм";
$lang['ru']['trunks'] = "По транкам";
$lang['ru']['search'] = "Поиск";
$lang['ru']['regs'] = "Регистр.";
$lang['ru']['outbound'] = "Исходящие";
$lang['ru']['agents'] = "Агенты";
$lang['ru']['queues'] = "Очереди";
$lang['ru']['distr_by_agents_dates'] = "По агентам";
$lang['ru']['distr_by_agents_hours'] = "По часам";

// tooltips
$lang['ru']['pdfhelp'] = "Экспортировать в .pdf";
$lang['ru']['csvhelp'] = "Экспортировать в CSV файл для обработки в табличном редакторе";
$lang['ru']['gotop'] = "Перейти наверх страницы";

// Index page
$lang['ru']['ALL'] = "ВСЕ";
$lang['ru']['ALLS'] = "Всего";
$lang['ru']['lower'] = "Ниже  ...";
$lang['ru']['higher'] = "Выше ...";
$lang['ru']['select_queue'] = "Выберите очереди";
$lang['ru']['select_agent'] = "Выберите агентов";
$lang['ru']['select_timeframe'] = "Ввберите интервал времени";
$lang['ru']['queue'] = "Очередь";
$lang['ru']['start'] = "Начальная дата";
$lang['ru']['end'] = "Конечная дата";
$lang['ru']['display_report'] = "Показать отчет";
$lang['ru']['shortcuts'] = "Шаблоны";
$lang['ru']['today'] = "Сегодня";
$lang['ru']['this_week'] = "Эта неделя";
$lang['ru']['this_month'] = "Этот месяц";
$lang['ru']['last_three_months'] = "Последние 3 месяца";
$lang['ru']['available'] = "Доступные";
$lang['ru']['selected'] = "Выбранные";
$lang['ru']['invaliddate'] = "Неправильный диапазон дат";

// Answered page
$lang['ru']['answered_calls_by_agent'] = "Принятые вызовы по агентам";
$lang['ru']['agent_in_call_dur'] = "Общее время агента в разговоре (мин.)";
$lang['ru']['answered_calls_by_queue'] = "Принятые вызовы по очередям";
$lang['ru']['anws_unanws_by_hour'] = "Принятые/Пропущенные по часам";
$lang['ru']['report_info'] = "Информация об отчете";
$lang['ru']['period'] = "Период";
$lang['ru']['answered_calls'] = "Принятые вызовы";
$lang['ru']['transferred_calls'] = "Переадресованные вызовы";
$lang['ru']['secs'] = "сек";
$lang['ru']['minutes'] = "мин";
$lang['ru']['hours'] = "ч";
$lang['ru']['calls'] = "выз.";
$lang['ru']['Calls'] = "Выз.";
$lang['ru']['RTCalls'] = "Вызовы";
$lang['ru']['agent'] = "Агент";
$lang['ru']['avg'] = "Средн.";
$lang['ru']['avg_calltime'] = "Средн. продолж.";
$lang['ru']['avg_holdtime'] = "Средн. время ожидания";
$lang['ru']['percent'] = "%";
$lang['ru']['total'] = "Общее";
$lang['ru']['calltime'] = "Время разговора";
$lang['ru']['holdtime'] = "Время ожидания";
$lang['ru']['total_time_agent'] = "Общая длительность по агентам (сек)";
$lang['ru']['no_calls_agent'] = "Количество звонков по агентам";
$lang['ru']['call_response'] = "Распределение принятых вызовов по периодам ожидания перед ответом";
$lang['ru']['within'] = "за ";
$lang['ru']['answer'] = "Ответ";
$lang['ru']['count'] = "Количество";
$lang['ru']['call_abandon'] = "Уровень отказов";
$lang['ru']['recordfile'] = "Запись";
$lang['ru']['dur_by_period'] = "Распределение продолжительности вызовов по периодам";

$lang['ru']['delta'] = "Дельта";
$lang['ru']['disconnect_cause'] = "Причина разъединения";
$lang['ru']['cause'] = "Причина";
$lang['ru']['agent_hungup'] = "Агент откл.";
$lang['ru']['caller_hungup'] = "Абонент откл.";
$lang['ru']['caller'] = "Абонент";
$lang['ru']['transfers'] = "Переадресации";
$lang['ru']['to'] = "На";
//

$lang['ru']['0-5sec'] = "0-5 сек";
$lang['ru']['6-10sec'] = "6-10 сек";
$lang['ru']['11-15sec'] = "11-15 сек";
$lang['ru']['16-20sec'] = "16-20 сек";
$lang['ru']['21-25sec'] = "21-25 сек";
$lang['ru']['26sec'] = "26+ сек";
$lang['ru']['0-25sec'] = "0-25 сек";

$lang['ru']['10sec'] = "0-10 сек";
$lang['ru']['15sec'] = "0-15 сек";
$lang['ru']['20sec'] = "10-20 сек";
$lang['ru']['30sec'] = "20-30 сек";
$lang['ru']['40sec'] = "30-40 сек";
$lang['ru']['50sec'] = "40-50 сек";
$lang['ru']['60sec'] = "50-60 сек";
$lang['ru']['60sec'] = "50-60 сек";
$lang['ru']['60sec'] = "50-60 сек";
$lang['ru']['60sec'] = "50-60 сек";
$lang['ru']['61sec'] = "61+ сек";
$lang['ru']['time'] = "Время";
$lang['ru']['event'] = "Событие";
$lang['ru']['callid'] = "Uniqueid";
$lang['ru']['qcallid'] = "Queue uniqueid";
$lang['ru']['page'] = "Страница";
$lang['ru']['of'] = "из";
//
$lang['ru']['5sec'] = "5 сек";
$lang['ru']['_10sec'] = "10 сек";
$lang['ru']['15sec'] = "15 сек";
$lang['ru']['_20sec'] = "20 сек";
$lang['ru']['25sec'] = "25 сек";
$lang['ru']['30sec'] = "30 сек";
$lang['ru']['31sec'] = "31+ сек";
// Unanswered page
$lang['ru']['unanswered_calls'] = "Пропущенные вызовы";
$lang['ru']['number_unanswered'] = "Количество пропущенных вызовов";
$lang['ru']['avg_wait_before_dis'] = "Среднее время ожидания перед разъединением (без таймаута)";
$lang['ru']['avg_queue_pos_at_dis'] = "Средняя позиция в очереди при разъединении";
$lang['ru']['avg_queue_start'] = "Средняя начальная позиция в очереди";
$lang['ru']['user_abandon'] = "Польз. покинул";
$lang['ru']['abandon'] = "Покинул";
$lang['ru']['timeout'] = "Тайм-аут";
$lang['ru']['unanswered_calls_qu'] = "Пропущенные вызовы по очередям";
$lang['ru']['unanswered_by_period_all'] = "Общее распределение отказов по периодам (без таймаута):";
$lang['ru']['unanswered_by_period_queue'] = "Распределение отказов в период ожидания по очередям (без таймаута):";
$lang['ru']['hangupposition'] = "Поз. выхода";
$lang['ru']['enterposition'] = "Поз. входа";
$lang['ru']['user_abandon_calls'] = "Детализация: Пользователь покинул";
$lang['ru']['user_abandon_calls_timeout'] = "Детализация: Таймаут";

// Distribution
$lang['ru']['totals'] = "Обзор";
$lang['ru']['number_enter_calls'] = "Количество полученных вызовов";
$lang['ru']['number_answered'] = "Количество принятых вызовов";
$lang['ru']['number_unanswered'] = "Количество пропущенных вызовов";
$lang['ru']['agent_login'] = "Входов агентов";
$lang['ru']['agent_logoff'] = "Выходов агентов";
$lang['ru']['call_distrib_day'] = "Распределение вызовов по дням";
$lang['ru']['call_distrib_hour'] = "Распределение вызовов по часам.";
$lang['ru']['call_distrib_week'] = "Распределение вызовов по дням недели";
$lang['ru']['call_distrib_month'] = "Распределение вызовов по месяцам";
$lang['ru']['date'] = "Дата";
$lang['ru']['month'] = "Месяц";
$lang['ru']['day'] = "День";
$lang['ru']['days'] = "Дней";
$lang['ru']['hour'] = "Часы";
$lang['ru']['enterqueue'] = "Получено";
$lang['ru']['answered'] = "Принято";
$lang['ru']['unanswered'] = "Потеряно";
$lang['ru']['percent_answered'] = "% Отв";
$lang['ru']['percent_unanswered_this'] = "% Неотв";
$lang['ru']['percent_unanswered'] = "% Неотв";
$lang['ru']['alogin'] = "Агентов";
$lang['ru']['login'] = "Вход";
$lang['ru']['logoff'] = "Выход";
$lang['ru']['answ_by_day'] = "Принятые вызовы по дням недели";
$lang['ru']['unansw_by_day'] = "Пропущенные вызовы по дням недели";
$lang['ru']['avg_call_time_by_day'] = "Средняя длительность звонка по дням недели";
$lang['ru']['avg_hold_time_by_day'] = "Среднее длительность ожидания по дням недели";
$lang['ru']['answ_by_hour'] = "Принятые вызовы по часам";
$lang['ru']['unansw_by_hour'] = "Пропущенные вызовы по часам";
$lang['ru']['avg_call_time_by_hr'] = "Средняя длительность звонка по часам";
$lang['ru']['avg_hold_time_by_hr'] = "Средняя длительность ожидания по часам";
$lang['ru']['page'] = "Страница";
$lang['ru']['export'] = "Экспорт таблицы:";

$lang['ru']['server_time'] = "Время сервера:";
$lang['ru']['php_parsed'] = "Парсинг: ";
$lang['ru']['seconds'] = "сек.";
$lang['ru']['current_agent_status'] = "Текущий статус агента";
$lang['ru']['hide_loggedoff'] = "Скрыть незарег.";
$lang['ru']['agent_status'] = "Статус Агента";
$lang['ru']['state'] = "Состояние";
$lang['ru']['durat'] = "Время разг.";
$lang['ru']['clid'] = "CLID";
$lang['ru']['last_in_call'] = "Последний вызов";
$lang['ru']['not_in_use'] = "свободен";
$lang['ru']['pause'] = "на паузу";
$lang['ru']['unpause'] = "c паузы";
$lang['ru']['busy'] = "вызов";
$lang['ru']['unavailable'] = "недоступен";
$lang['ru']['unknown'] = "неизвестно";
$lang['ru']['dialout'] = "разг.";
$lang['ru']['no_info'] = "нет инф.";
$lang['ru']['min_ago'] = "мин. назад";
$lang['ru']['queue_summary'] = "Общ. инф.";
$lang['ru']['staffed'] = "Активн. агентов";
$lang['ru']['talking'] = "Говорят";
$lang['ru']['paused'] = "На паузе";
$lang['ru']['calls_waiting'] = "Вызовов в очереди";
$lang['ru']['oldest_call_waiting'] = "Время ожидания";
$lang['ru']['calls_waiting_detail'] = "Вызовы в очереди подробно";
$lang['ru']['position'] = "Позиция";
$lang['ru']['callerid'] = "Callerid";
$lang['ru']['wait_time'] = "Время ожидания";
//cdr
$lang['ru']['cdr'] = "RAW";
$lang['ru']['filter'] = "Выбрать";
$lang['ru']['page_rows'] = "Строк:";
$lang['ru']['hidden_ringnoanswer'] = "Скрыть RINGNOANSWER";

?>
