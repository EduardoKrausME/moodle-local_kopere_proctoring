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
 * phpcs:disable moodle.Files.LangFilesOrdering.IncorrectOrder
 *
 * Portuguese Brazilian lang file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['description_pending'] = 'Conclua ou aguarde os seguintes itens obrigatórios antes que a prova possa ser iniciada:';
$string['description_ready'] = 'Todos os itens obrigatórios foram aprovados. Você já pode iniciar a prova.';
$string['enabled'] = 'Ativar Proctoring';
$string['locked_default_message'] = 'A prova está temporariamente bloqueada devido às regras de proctoring.';
$string['locked_title'] = 'Prova bloqueada';
$string['managekopere_proctoringplugins'] = 'Gerenciar plugins de políticas de Proctoring';
$string['managekopere_proctoringplugins_desc'] = 'Ative, desative e organize a ordem dos plugins. A ordem definida aqui também será usada para exibir os dados aos alunos.';
$string['movedownplugin'] = 'Mover plugin para baixo';
$string['moveupplugin'] = 'Mover plugin para cima';
$string['pluginname'] = 'Kopere Proctoring';
$string['pluginstatus_activate'] = 'Ativar';
$string['pluginstatus_active'] = 'Ativo';
$string['pluginstatus_deactivate'] = 'Desativar';
$string['pluginstatus_inactive'] = 'Inativo';
$string['privacy:metadata:local_kopere_proctoring:files_snapshot'] = 'Armazena imagens de evidência capturadas durante a fiscalização.';
$string['privacy:metadata:local_kopere_proctoring_att'] = 'Armazena dados de fiscalização e de aceite do contrato para cada tentativa do questionário.';
$string['privacy:metadata:local_kopere_proctoring_att:attemptid'] = 'O identificador da tentativa do questionário.';
$string['privacy:metadata:local_kopere_proctoring_att:contract'] = 'Indica se o usuário aceitou o contrato de fiscalização.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_geo'] = 'O texto opcional de geolocalização registrado no momento do aceite do contrato.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_ip'] = 'O endereço IP registrado no momento do aceite do contrato.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_screenresolution'] = 'A resolução de tela registrada no momento do aceite do contrato.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_time'] = 'A data e hora em que o contrato foi aceito.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_useragent'] = 'O user agent do navegador registrado no momento do aceite do contrato.';
$string['privacy:metadata:local_kopere_proctoring_att:time'] = 'A data e hora em que o registro de fiscalização da tentativa foi criado.';
$string['privacy:metadata:local_kopere_proctoring_att:userid'] = 'O identificador do usuário.';
$string['privacy:metadata:local_kopere_proctoring_log'] = 'Armazena eventos de fiscalização registrados durante uma tentativa do questionário.';
$string['privacy:metadata:local_kopere_proctoring_log:actionvalue'] = 'O valor da ação registrado para o evento.';
$string['privacy:metadata:local_kopere_proctoring_log:attemptid'] = 'O identificador da tentativa do questionário.';
$string['privacy:metadata:local_kopere_proctoring_log:ip'] = 'O endereço IP registrado para o evento.';
$string['privacy:metadata:local_kopere_proctoring_log:screenresolution'] = 'A resolução de tela registrada para o evento.';
$string['privacy:metadata:local_kopere_proctoring_log:time'] = 'A data e hora em que o evento foi registrado.';
$string['privacy:metadata:local_kopere_proctoring_log:useragent'] = 'O user agent do navegador registrado para o evento.';
$string['privacy:metadata:local_kopere_proctoring_log:userid'] = 'O identificador do usuário.';
$string['proctoring_warning'] = '<p>Ao ativar o <strong>Proctoring</strong>, o layout da prova será ajustado automaticamente para que <strong>todas as questões sejam exibidas em uma única página</strong> e essa alteração evita que o aluno precise navegar entre páginas durante a tentativa, reduzindo o risco de interrupções, recarregamentos ou encerramento indevido da sessão do Proctoring.</p>
<p>Para isso, o valor da configuração <code>Layout</code> do Quiz será alterado automaticamente, garantindo que a prova seja apresentada em página única enquanto o Proctoring estiver ativo.</p>';
$string['reload_required_button'] = 'Recarregar página';
$string['reload_required_message'] = 'O monitoramento da prova foi ocultado ou removido. Para continuar com segurança, recarregue a página.';
$string['reload_required_title'] = 'Recarregue a página';
$string['reorder'] = 'Reordenar';
$string['return_button'] = 'Entendi, voltar para a prova';
$string['start_button'] = 'Iniciar prova';
$string['start_title'] = 'Acesso à prova';
$string['status'] = 'Status';
$string['subplugintype_proctoring_policy'] = 'Política de fiscalização';
$string['subplugintype_proctoring_policy_plural'] = 'Políticas de fiscalização';
$string['subplugintype_proctoringpolicy'] = 'Políticas de Proctoring';
$string['subplugintype_proctoringpolicy_plural'] = 'Políticas de proctoring';
$string['understand_error'] = 'Marque que você entendeu esta regra antes de iniciar a prova.';
$string['understand_label'] = 'Entendo';
$string['unknown'] = 'Desconhecido';
$string['userdata_activity'] = 'ID da atividade';
$string['userdata_attempt_details'] = 'Detalhes da tentativa';
$string['userdata_attempt_label'] = 'Tentativa';
$string['userdata_attemptid'] = 'ID da tentativa';
$string['userdata_contract_details'] = 'Detalhes do contrato';
$string['userdata_contractaccepted'] = 'Contrato aceito';
$string['userdata_contracttime'] = 'Contrato aceito em';
$string['userdata_created'] = 'Criado em';
$string['userdata_finished'] = 'Finalizado em';
$string['userdata_geo'] = 'Geolocalização';
$string['userdata_heading'] = 'Dados do Kopere Proctoring de {$a}';
$string['userdata_ip'] = 'IP';
$string['userdata_log_action'] = 'Ação';
$string['userdata_logs'] = 'Logs';
$string['userdata_nav'] = 'Dados do usuário';
$string['userdata_noattempts'] = 'Nenhum dado de proctoring foi encontrado para este usuário.';
$string['userdata_nologs'] = 'Nenhum log encontrado para esta tentativa.';
$string['userdata_screenresolution'] = 'Resolução da tela';
$string['userdata_started'] = 'Iniciado em';
$string['userdata_time'] = 'Hora';
$string['userdata_title'] = 'Dados de proctoring - {$a}';
$string['userdata_total_attempts'] = 'Tentativas';
$string['userdata_total_contracts'] = 'Contratos aceitos';
$string['userdata_useragent'] = 'Agente do usuário';
