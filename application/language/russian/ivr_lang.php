<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['ivr_title'] = 'IVR Меню';
$lang['ivr_new'] = 'Новое IVR меню';
$lang['ivr_edit'] = 'Редактировать IVR меню';
$lang['ivr_view'] = 'Просмотр IVR меню';

// Form fields
$lang['ivr_name'] = 'Название меню';
$lang['ivr_campaign'] = 'Кампания';
$lang['ivr_select_campaign'] = '-- Выберите кампанию --';
$lang['ivr_audio_file'] = 'Аудио файл';
$lang['ivr_current_audio'] = 'Текущее аудио';
$lang['ivr_timeout'] = 'Тайм-аут (секунды)';
$lang['ivr_max_digits'] = 'Макс. цифр';

// Actions section
$lang['ivr_actions'] = 'Действия IVR';
$lang['ivr_dtmf_digit'] = 'DTMF цифра';
$lang['ivr_action_type'] = 'Тип действия';
$lang['ivr_action_value'] = 'Значение действия';
$lang['ivr_add_action'] = 'Добавить действие';

// Action types
$lang['ivr_action_hangup'] = 'Повесить трубку';
$lang['ivr_action_extension'] = 'Внутренний номер';
$lang['ivr_action_call_extension'] = 'Позвонить на номер';
$lang['ivr_action_ivr'] = 'IVR меню';
$lang['ivr_action_voicemail'] = 'Голосовая почта';
$lang['ivr_action_queue'] = 'Очередь';
$lang['ivr_action_playback'] = 'Воспроизвести';

// Help text
$lang['ivr_help_audio'] = 'Загрузите файл WAV или MP3 (макс. 10MB)';
$lang['ivr_help_audio_convert'] = 'Файл будет автоматически преобразован в формат Asterisk (8000Hz, моно WAV).';
$lang['ivr_help_current_file'] = 'Текущий файл';
$lang['ivr_help_timeout'] = 'Время ожидания ввода пользователя';
$lang['ivr_help_timeout_default'] = 'Время ожидания ввода DTMF (по умолчанию: 3 секунды)';
$lang['ivr_help_max_digits'] = 'Максимальное количество цифр для сбора';
$lang['ivr_help_dtmf'] = 'Нажмите цифру (0-9, *, #) или "timeout" для отсутствия ввода';
$lang['ivr_help_action_placeholder'] = 'например, PJSIP/100';

// Buttons
$lang['ivr_create'] = 'Создать IVR меню';
$lang['ivr_update'] = 'Обновить IVR меню';
$lang['ivr_manage_menus'] = 'Управление IVR меню';

// Sections
$lang['ivr_section_settings'] = 'Настройки IVR меню';
$lang['ivr_section_dtmf_actions'] = 'DTMF действия';

// Table columns
$lang['ivr_id'] = 'ID';
$lang['ivr_operations'] = 'Операции';

// DTMF options
$lang['ivr_dtmf_select'] = '-- Выбрать --';
$lang['ivr_dtmf_star'] = '* (Звездочка)';
$lang['ivr_dtmf_hash'] = '# (Решетка)';
$lang['ivr_dtmf_invalid'] = 'i (Неверный ввод)';
$lang['ivr_dtmf_timeout'] = 't (Тайм-аут)';

// View page
$lang['ivr_menu_details'] = 'Детали меню';
$lang['ivr_play_audio'] = 'Воспроизвести аудио';
$lang['ivr_pause'] = 'Пауза';
$lang['ivr_audio_not_supported'] = 'Ваш браузер не поддерживает аудио элементы.';
$lang['ivr_seconds'] = 'секунды';
$lang['ivr_no_actions'] = 'DTMF действия не настроены';
$lang['ivr_action_goto_ivr'] = 'Перейти к IVR';

// Messages
$lang['ivr_no_menus'] = 'IVR меню не найдены. Создайте свое первое IVR меню!';
$lang['ivr_confirm_delete'] = 'Вы уверены, что хотите удалить это IVR меню? Это действие необратимо.';
$lang['ivr_audio_required'] = 'Аудио файл обязателен';
$lang['ivr_at_least_one_action'] = 'Требуется хотя бы одно действие';
