<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['campaigns_title'] = 'Кампании';
$lang['campaigns_new'] = 'Новая кампания';
$lang['campaigns_edit'] = 'Редактировать кампанию';
$lang['campaigns_view'] = 'Просмотр кампании';

// Form fields
$lang['campaigns_name'] = 'Название кампании';
$lang['campaigns_description'] = 'Описание';
$lang['campaigns_concurrent_calls'] = 'Одновременных звонков';
$lang['campaigns_retry_times'] = 'Попытки повтора';
$lang['campaigns_retry_delay'] = 'Задержка повтора (секунды)';
$lang['campaigns_trunk_type'] = 'Тип транка';
$lang['campaigns_trunk_value'] = 'Значение транка';
$lang['campaigns_callerid'] = 'ID звонящего';
$lang['campaigns_record_calls'] = 'Записывать звонки';

// Trunk types
$lang['campaigns_trunk_custom'] = 'Произвольный';
$lang['campaigns_trunk_pjsip'] = 'PJSIP';
$lang['campaigns_trunk_sip'] = 'SIP';
$lang['campaigns_select_trunk'] = 'Выберите транк';

// Agent destination
$lang['campaigns_agent_dest_type'] = 'Тип назначения';
$lang['campaigns_agent_dest_value'] = 'Значение назначения';
$lang['campaigns_agent_dest_custom'] = 'Произвольный';
$lang['campaigns_agent_dest_extension'] = 'Внутренний номер';
$lang['campaigns_agent_dest_ivr'] = 'IVR';
$lang['campaigns_select_ivr'] = 'Выберите IVR меню';

// Help text
$lang['campaigns_help_max_calls'] = 'Максимальное количество одновременных звонков';
$lang['campaigns_help_trunk_value'] = 'Используйте ${EXTEN} для подстановки номера';
$lang['campaigns_help_record_calls'] = 'Оба канала будут записаны и смешаны в стерео MP3';
$lang['campaigns_help_custom'] = 'Введите полную строку набора (например, PJSIP/100, Local/100@from-internal)';
$lang['campaigns_help_extension'] = 'Введите номер внутреннего телефона (например, 100)';
$lang['campaigns_help_ivr'] = 'Выберите IVR меню из выпадающего списка ниже';

// Sections
$lang['campaigns_section_basic'] = 'Основная информация';
$lang['campaigns_section_trunk'] = 'Настройки транка';
$lang['campaigns_section_agent'] = 'Назначение оператора';

// Buttons
$lang['campaigns_create'] = 'Создать кампанию';
$lang['campaigns_update'] = 'Обновить кампанию';
$lang['campaigns_start'] = 'Запустить';
$lang['campaigns_pause'] = 'Приостановить';
$lang['campaigns_stop'] = 'Остановить';
$lang['campaigns_upload_numbers'] = 'Загрузить номера';
$lang['campaigns_manage_ivr'] = 'Управление IVR меню';

// Table columns
$lang['campaigns_id'] = 'ID';
$lang['campaigns_total_numbers'] = 'Всего номеров';
$lang['campaigns_pending'] = 'В ожидании';
$lang['campaigns_completed'] = 'Завершено';

// Messages
$lang['campaigns_no_campaigns'] = 'Кампании не найдены. Создайте свою первую кампанию!';
$lang['campaigns_confirm_delete'] = 'Вы уверены, что хотите удалить эту кампанию? Это действие необратимо.';
$lang['campaigns_confirm_start'] = 'Вы уверены, что хотите запустить эту кампанию?';
$lang['campaigns_confirm_stop'] = 'Вы уверены, что хотите остановить эту кампанию?';
$lang['campaigns_error'] = 'Ошибка';
$lang['campaigns_failed_control'] = 'Не удалось управлять кампанией';
