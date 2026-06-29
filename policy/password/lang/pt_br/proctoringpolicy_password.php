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
 * proctoringpolicy_password.php
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['action_approve'] = 'Aprovar';
$string['action_copy'] = 'Copiar senha';
$string['action_deny'] = 'Negar';
$string['action_refresh'] = 'Atualizar';
$string['admin_lastcheck_nochange'] = 'Última verificação em {$a}: nada novo.';
$string['admin_lastcheck_updated'] = 'Tabela atualizada em {$a}.';
$string['admin_norequests'] = 'Nenhuma solicitação pendente.';
$string['admin_popover_body'] = 'Há {$a} solicitação(ões) de senha aguardando liberação.';
$string['admin_popover_open'] = 'Abrir solicitações';
$string['admin_popover_title'] = 'Solicitação de senha aguardando';
$string['admin_refreshing'] = 'Atualizado em';
$string['adminpage'] = 'Solicitações de senha';
$string['adminpage_title'] = 'Solicitações de senha para provas presenciais';
$string['ajax_blocked'] = 'Você está temporariamente bloqueado devido a muitas tentativas incorretas.';
$string['ajax_invalidaction'] = 'Ação inválida.';
$string['ajax_ok'] = 'OK';
$string['column_actions'] = 'Ações';
$string['column_attempt'] = 'Tentativa';
$string['column_browser'] = 'Navegador';
$string['column_cmid'] = 'Atividade';
$string['column_course'] = 'Curso';
$string['column_created'] = 'Solicitado em';
$string['column_ip'] = 'IP';
$string['column_password'] = 'Senha de aprovação';
$string['column_status'] = 'Status';
$string['column_user'] = 'Aluno';
$string['enabled'] = 'Ativar senha';
$string['enabled_cm'] = 'Exigir senha/aprovação para esta prova';
$string['enabled_desc'] = 'Se ativado, os alunos devem ser liberados por um professor usando uma senha numérica de uso único ou uma aprovação explícita.';
$string['heading_info'] = 'Adiciona uma etapa de liberação controlada pelo professor antes do início da tentativa. Os alunos solicitam aprovação ou informam uma senha numérica de uso único, enquanto a política aplica limitação de erros e bloqueio temporário para envios inválidos repetidos.';
$string['js_status_approved'] = 'Aprovado.';
$string['js_status_blocked'] = 'Você está temporariamente bloqueado.';
$string['js_status_denied'] = 'Solicitação negada pelo professor.';
$string['js_status_pending'] = 'Solicitação enviada. Aguardando aprovação.';
$string['js_toomany_errors'] = 'Muitas tentativas incorretas. Aguarde 10 minutos.';
$string['maxerrors'] = 'Máximo de tentativas incorretas em 10 minutos';
$string['maxerrors_desc'] = 'Após este número de tentativas incorretas de senha em uma janela de 10 minutos, o aluno ficará bloqueado por 10 minutos.';
$string['pluginname'] = 'Senha ';
$string['quiz_pending_title'] = 'Alunos aguardando senha nesta prova';
$string['requirement_label'] = 'Aprovação do professor ou senha da prova';
$string['rolesallowed'] = 'Papéis permitidos para aprovar senhas';
$string['rolesallowed_desc'] = 'Usuários com pelo menos um destes papéis no contexto do curso podem acessar a página de administração de senhas e aprovar solicitações dos alunos.';
$string['status_approved'] = 'Aprovado';
$string['status_blocked'] = 'Bloqueado';
$string['status_denied'] = 'Negado';
$string['status_pending'] = 'Pendente';
$string['student_enter_password'] = 'Para continuar, é necessário informar uma senha. O pedido foi enviado automaticamente ao professor, que poderá liberar o acesso ou fornecer a senha.';
$string['student_not_enabled'] = 'A Política de senha não está ativada para esta prova.';
$string['student_request_sent'] = 'Solicitação enviada.';
$string['student_submit_password'] = 'Enviar senha';
$string['student_title'] = 'Senha da prova';
$string['student_toomany_errors'] = 'Muitas tentativas incorretas. Aguarde 10 minutos antes de tentar novamente.';
$string['student_waiting'] = 'Aguardando aprovação do professor...';
$string['student_wrong_password'] = 'Senha inválida.';
$string['teacher_info'] = 'Ative para provas presenciais ou supervisionadas em que o professor precisa liberar manualmente os alunos no momento correto. Os alunos podem aguardar aprovação ou informar a senha fornecida pelo professor.';
