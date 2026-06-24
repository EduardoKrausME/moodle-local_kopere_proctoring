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
 * proctoringpolicy_fullscreen.php
 *
 * @package   proctoringpolicy_fullscreen
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Ativar tela cheia ';
$string['enabled_cm'] = 'Exigir tela cheia durante a tentativa';
$string['enabled_desc'] = 'Se ativado, a política poderá ser configurada por atividade de questionário.';
$string['heading'] = 'Tela cheia ';
$string['heading_info'] = 'Exige que o cliente permaneça no modo de tela cheia do navegador durante toda a tentativa. A política pode exibir uma instrução curta antes do início da tentativa e uma mensagem de aviso diferente apenas depois que o aluno sair do modo de tela cheia.';
$string['legend'] = 'Requisitos de tela cheia';
$string['limit_cm'] = 'Máximo de saídas permitidas';
$string['limit_cm_desc'] = 'Quantas vezes o aluno pode sair da tela cheia antes de ser bloqueado.';
$string['limit_default'] = 'Máximo padrão de saídas permitidas';
$string['limit_default_desc'] = 'Valor padrão usado ao configurar novas atividades de questionário.';
$string['message_cm'] = 'Mensagem de violação exibida quando o aluno sai da página';
$string['message_cm_desc'] = 'Mensagem exibida apenas quando o aluno sai do modo de tela cheia.';
$string['message_default'] = 'Mensagem padrão de violação';
$string['message_default_desc'] = 'Mensagem padrão exibida apenas quando o aluno sai do modo de tela cheia.';
$string['message_default_text'] = '<h2>🖥️ O modo de tela cheia foi encerrado</h2>
<p><strong>Você saiu do modo de tela cheia durante a prova.</strong></p>
<p>Retorne imediatamente para a tela cheia. Saídas repetidas podem bloquear ou encerrar a tentativa.</p>';
$string['message_init'] = 'Esta tentativa exige modo de tela cheia.';
$string['pluginname'] = 'Tela cheia ';
$string['start_message_cm'] = 'Mensagem inicial exibida antes do início da prova';
$string['start_message_default'] = 'Mensagem inicial padrão';
$string['start_message_default_desc'] = 'Mensagem curta exibida antes do início da tentativa para explicar que o modo de tela cheia é obrigatório.';
$string['start_message_default_text'] = '<p>Você deve permanecer em modo de tela cheia durante toda a tentativa.</p>';
$string['teacher_info'] = 'Ative isto quando o aluno precisar permanecer em tela cheia durante toda a prova. Use a mensagem inicial para explicar a regra antes do início da tentativa e use a mensagem de violação para avisar o aluno apenas depois que ele sair do modo de tela cheia.';
