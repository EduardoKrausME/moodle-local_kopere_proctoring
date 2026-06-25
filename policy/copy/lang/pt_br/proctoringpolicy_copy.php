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
 * proctoringpolicy_copy.php
 *
 * @package   proctoringpolicy_copy
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Ativar proteção contra copiar/colar';
$string['enabled_cm'] = 'Ativar proteção contra copiar/colar para esta prova';
$string['enabled_desc'] = 'Se ativado, esta política bloqueará ações de copiar, recortar, colar e menu de contexto durante a prova.';
$string['heading_info'] = 'Durante a realização da prova, este recurso intercepta ações de copiar, recortar e colar, além do clique com o botão direito do mouse. Antes do início da prova, uma breve instrução é exibida ao aluno. Caso ele tente executar alguma dessas ações, uma mensagem de aviso será apresentada.';
$string['limit_cm'] = 'Limite de avisos para esta prova';
$string['limit_default'] = 'Limite padrão de avisos';
$string['limit_default_desc'] = 'Número de tentativas bloqueadas permitidas antes que a mensagem deixe de ser exibida (0 significa sem limite).';
$string['message_cm'] = 'Mensagem de violação exibida quando o aluno sai da página';
$string['message_default'] = 'Mensagem padrão de violação';
$string['message_default_desc'] = 'Mensagem exibida apenas quando o usuário tenta copiar, recortar, colar ou botão direito do mouse.';
$string['message_default_text'] = '<h2>Tentativa de copiar/colar bloqueada.</h2>
<p><strong>Durante a prova, não é permitido copiar, recortar, colar, selecionar todo o conteúdo, imprimir ou usar o botão direito do mouse.</strong></p>
<p>Esta ação foi registrada e poderá ser revisada pela equipe responsável.</p>';
$string['pluginname'] = 'Copiar e colar';
$string['start_message_cm'] = 'Mensagem inicial exibida antes do início da prova';
$string['start_message_default'] = 'Mensagem inicial padrão';
$string['start_message_default_desc'] = 'Mensagem curta exibida antes do início da tentativa para explicar que copiar e colar não são permitidos.';
$string['start_message_default_text'] = '<p>Durante esta tentativa, não é permitido copiar, recortar, colar, imprimir ou usar o botão direito do mouse.</p>';
$string['teacher_info'] = 'Ative isto para bloquear ações de copiar e colar durante a tentativa. Use a mensagem inicial para explicar a regra antes do início da prova e use a mensagem de violação para avisar o aluno apenas quando a regra for quebrada.';
