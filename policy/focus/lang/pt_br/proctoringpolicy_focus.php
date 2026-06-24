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
 * proctoringpolicy_focus.php
 *
 * @package   proctoringpolicy_focus
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Ativar foco ';
$string['enabled_desc'] = 'Se ativado, alterações de foco e visibilidade serão monitoradas nas tentativas do questionário.';
$string['form_enabled_label'] = 'Aplicar política de foco a este questionário';
$string['form_limit_label'] = 'Mudanças de foco permitidas (blur / oculto)';
$string['form_message_label'] = 'Mensagem de violação exibida quando o aluno sai da página';
$string['form_start_message_label'] = 'Mensagem inicial exibida antes do início da prova';
$string['heading'] = 'Foco e visibilidade da janela';
$string['heading_desc'] = 'Define quantas vezes o aluno pode sair da janela do questionário (blur ou troca de aba) antes que a tentativa seja bloqueada.';
$string['heading_info'] = 'Monitora mudanças de foco do navegador e estado de visibilidade, como eventos de blur, trocas de aba ou janelas ocultas, durante a tentativa. A política pode exibir uma instrução curta antes do início da tentativa e uma mensagem de aviso separada apenas depois que o aluno sair da página ou trocar de aba.';
$string['legend'] = 'Foco e visibilidade da janela';
$string['limit_default'] = 'Mudanças de foco permitidas por padrão';
$string['limit_default_desc'] = 'Número máximo de eventos de perda de foco (blur ou aba oculta) permitidos antes de bloquear a tentativa.';
$string['message_default'] = 'Mensagem padrão de violação';
$string['message_default_desc'] = 'Mensagem HTML padrão exibida apenas quando o aluno sai da página ou muda o foco do navegador.';
$string['message_default_text'] = '<h2>Violação de foco da prova detectada</h2>
<p><strong>Você saiu da página da prova ou mudou para outra janela/aba.</strong></p>
<p>Retorne para a prova imediatamente. Este evento poderá ser revisado pelo professor ou pela equipe de suporte.</p>';
$string['pluginname'] = 'Foco ';
$string['start_limit_label'] = 'Mudanças de foco permitidas:';
$string['start_message_default'] = 'Mensagem inicial padrão';
$string['start_message_default_desc'] = 'Mensagem curta exibida antes do início da tentativa para explicar que o aluno deve permanecer na página da prova.';
$string['start_message_default_text'] = '<p>Você deve permanecer nesta página da prova e não pode alternar abas, janelas ou aplicativos durante a tentativa.</p>';
$string['teacher_info'] = 'Ative isto quando o aluno precisar permanecer na página da prova. Use a mensagem inicial para explicar a regra antes do início da tentativa e use a mensagem de violação para avisar o aluno apenas após a página perder o foco.';
