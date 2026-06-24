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
 * proctoringpolicy_evidence.php
 *
 * @package   proctoringpolicy_evidence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['allowdownload_cm'] = 'Permitir download de evidências nesta prova';
$string['allowdownload_default'] = 'Permitir download de evidências (professores)';
$string['allowdownload_default_desc'] = 'Se ativado, usuários com permissão para revisar tentativas poderão baixar arquivos de evidência.';
$string['cleanup_task_name'] = 'Limpeza de evidências do Kopere Proctoring';
$string['enabled'] = 'Ativar gerenciamento de evidências';
$string['enabled_cm'] = 'Ativar evidências para esta prova';
$string['enabled_desc'] = 'Se ativado, evidências (imagens da webcam, capturas de tela e outros artefatos) serão gerenciadas e armazenadas de acordo com as regras abaixo.';
$string['event_evidence_deleted'] = 'Evidência excluída pelas regras de retenção';
$string['event_evidence_stored'] = 'Evidência armazenada';
$string['heading'] = 'Evidências ';
$string['heading_info'] = 'Controla o ciclo de vida das evidências de proctoring geradas pela tentativa, como capturas de tela, imagens da webcam e artefatos relacionados. A política define tempo de retenção, número máximo de arquivos por tentativa e permissões de download para revisores.';
$string['legend'] = 'Configurações de evidências';
$string['log_cleanup'] = 'Limpeza de evidências executada: coursemodule={$a->cmid}, attempt={$a->attemptid}';
$string['maxfiles_cm'] = 'Máximo de arquivos de evidência por tentativa (esta prova)';
$string['maxfiles_cm_help'] = 'Número máximo de arquivos de evidência que serão mantidos para cada tentativa. Arquivos extras poderão ser descartados ou limpos com base neste limite.';
$string['maxfiles_default'] = 'Máximo padrão de arquivos de evidência por tentativa';
$string['maxfiles_default_desc'] = 'Número máximo de arquivos de evidência (imagens da webcam, capturas de tela etc.) que serão mantidos por tentativa. Use 0 para sem limite.';
$string['pluginname'] = 'Evidências ';
$string['retention_cm'] = 'Retenção (dias) para esta prova';
$string['retention_cm_help'] = 'Número de dias que as evidências serão mantidas após a tentativa ser finalizada. Use 0 para manter para sempre.';
$string['retention_default'] = 'Retenção padrão (dias)';
$string['retention_default_desc'] = 'Número de dias que as evidências devem ser mantidas antes de serem removidas. Use 0 para manter para sempre.';
$string['teacher_info'] = 'Use esta política para manter evidências visuais para auditoria posterior. Defina por quanto tempo os arquivos serão armazenados, quantos arquivos podem ser mantidos por tentativa e se revisores poderão baixar as evidências armazenadas.';
