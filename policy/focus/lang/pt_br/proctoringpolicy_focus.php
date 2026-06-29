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
$string['enabled_desc'] = 'Se ativado, alterações de foco serão monitoradas nas tentativas do questionário.';
$string['form_enabled_label'] = 'Aplicar política de foco a este questionário';
$string['form_limit_label'] = 'Mudanças de foco permitidas (blur / oculto)';
$string['form_message_label'] = 'Mensagem de violação exibida quando o aluno sai da página';
$string['form_start_message_label'] = 'Mensagem inicial exibida antes do início da prova';
$string['heading_desc'] = 'Define quantas vezes o aluno pode sair da janela do questionário antes que a prova seja submetida.';
$string['heading_info'] = 'Monitora mudanças de foco durante a prova, como trocas de aba ou acesso a outros aplicativos. A política exibe uma instrução breve antes do início da avaliação e apresenta uma mensagem de aviso quando o aluno sai da página ou alterna para outra aba.';
$string['limit_default'] = 'Mudanças de foco permitidas por padrão';
$string['limit_default_desc'] = 'Número máximo de saída da prova permitidos antes de bloquear a prova.';
$string['message_default'] = 'Mensagem padrão de violação';
$string['message_default_desc'] = 'Mensagem HTML padrão exibida apenas quando o aluno sai da página ou muda o foco do navegador.';
$string['message_default_text'] = '<h2>Saída da página da prova detectada</h2>
<p><strong>Você saiu da página da prova.</strong></p>
<p>Durante a realização da prova, não é permitido acessar outras abas, janelas ou aplicativos. Retorne imediatamente à página da prova.</p>
<p>Esta ocorrência foi registrada e será revisada pelo professor.</p>';
$string['pluginname'] = 'Foco na prova ';
$string['requirement_label'] = 'Entender a política de foco na prova';
$string['start_limit_label'] = 'Mudanças de foco permitidas:';
$string['start_message_default'] = 'Mensagem inicial padrão';
$string['start_message_default_desc'] = 'Mensagem curta exibida antes do início da prova para explicar que o aluno deve permanecer na página da prova.';
$string['start_message_default_text'] = '<p>Durante toda a prova, você deve permanecer nesta página e não é permitido alternar para outras abas, janelas, aplicativos ou programas até concluir e enviar a avaliação.</p>
<p>Caso você saia desta página ou acesse outro ambiente durante a prova, essa ação será registrada pelo sistema e considerada uma violação das regras da avaliação e enviada para revisão pelo Professor.</p>';
$string['teacher_info'] = 'Ative isto quando o aluno precisar permanecer na página da prova. Use a mensagem inicial para explicar a regra antes do início da prova e use a mensagem de violação para avisar o aluno apenas após a página perder o foco.';
