<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * proctoringpolicy_notifications.php
 *
 * @package   proctoringpolicy_notifications
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['body_cm'] = 'Corpo para esta prova';
$string['body_default'] = 'Corpo padrão';
$string['body_default_text'] = 'Curso: {coursename}<br>Prova: {quizname}<br>Evento: {event}<br>Motivo: {reason}';
$string['email_from_name'] = 'Notificações de proctoring';
$string['enabled'] = 'Ativar sistema de notificações';
$string['enabled_cm'] = 'Ativar notificações para esta prova';
$string['enabled_desc'] = 'Se ativado, esta política poderá enviar notificações por e-mail com base em eventos da prova.';
$string['event_attempt_finished'] = 'Tentativa finalizada';
$string['event_exam_locked'] = 'Prova bloqueada pelas regras de proctoring';
$string['event_suspicious_activity'] = 'Atividade suspeita detectada';
$string['heading_info'] = 'Envia notificações automáticas por e-mail a partir de eventos detectados com os alunos.';
$string['moment_attemptfinished'] = 'Quando a tentativa for finalizada';
$string['moment_cm'] = 'Momento de disparo da notificação';
$string['moment_cm_help'] = 'Define quando as notificações serão enviadas para esta prova.';
$string['moment_default'] = 'Momento de disparo padrão';
$string['moment_default_attemptfinished'] = 'Quando a tentativa for finalizada';
$string['moment_default_desc'] = 'Define quando as notificações devem ser enviadas por padrão.';
$string['moment_default_examlocked'] = 'Quando a prova for bloqueada';
$string['moment_default_none'] = 'Não enviar notificações';
$string['moment_default_suspicious'] = 'Em caso de atividade suspeita';
$string['moment_examlocked'] = 'Quando a prova for bloqueada';
$string['moment_none'] = 'Não enviar notificações';
$string['moment_suspicious'] = 'Em caso de atividade suspeita';
$string['pluginname'] = 'Notificações ';
$string['recipients_cm'] = 'Destinatários para esta prova';
$string['recipients_cm_help'] = 'E-mails separados por vírgula. Se ficar vazio, o padrão geral do site será usado.';
$string['recipients_default'] = 'Destinatários padrão (e-mails separados por vírgula)';
$string['recipients_default_desc'] = 'Lista de e-mails que receberão notificações se não houver substituição no nível do módulo.';
$string['subject_cm'] = 'Assunto para esta prova';
$string['subject_default'] = 'Assunto padrão';
$string['subject_default_desc'] = 'Assunto padrão dos e-mails de notificação. Você pode usar os placeholders: 
<ul>
    <li>{coursename}</li>
    <li>{quizname}</li>
    <li>{userid}</li>
    <li>{username}</li>
    <li>{event}</li>
    <li>{reason}</li>
</ul>';
$string['body_default_desc'] = 'Corpo padrão dos e-mails de notificação. Você pode usar os placeholders:
<ul>
    <li>{coursename}</li>
    <li>{quizname}</li>
    <li>{userid}</li>
    <li>{username}</li>
    <li>{event}</li>
    <li>{reason}</li>
</ul>';
$string['teacher_info'] = 'Use esta política para alertar coordenadores, supervisores ou equipes de suporte quando ocorrer atividade suspeita, quando a prova for bloqueada ou quando a tentativa for finalizada.';
