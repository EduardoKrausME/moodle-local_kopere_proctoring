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
 * proctoringpolicy_securitysignals.php
 *
 * @package   proctoringpolicy_securitysignals
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Ativar sinais de segurança';
$string['enabled_cm'] = 'Ativar sinais de segurança para esta prova';
$string['enabled_desc'] = 'Se ativado, o navegador emitirá sinais leves de integridade/devtools e relatará alterações suspeitas.';
$string['event_devtools_suspected'] = 'Devtools suspeito';
$string['event_integrity_changed'] = 'Integridade do cliente alterada';
$string['event_suspicious_activity'] = 'Atividade suspeita';
$string['heading_info'] = 'Coleta telemetria leve de segurança do lado do navegador, como alterações de integridade e possível uso de ferramentas de desenvolvedor, e envia pulsos periódicos que podem ser correlacionados com eventos suspeitos de proctoring.';
$string['js_warn_devtools'] = 'Atividade suspeita detectada.';
$string['js_warn_integrity'] = 'Integridade de segurança alterada.';
$string['pluginname'] = 'Segurança';
$string['pulsems_cm'] = 'Intervalo do pulso (segundos)';
$string['pulsems_default'] = 'Intervalo padrão do pulso (segundos)';
$string['pulsems_default_desc'] = 'Com que frequência o cliente envia pulsos de segurança quando uma atividade suspeita é detectada.';
$string['teacher_info'] = 'Ative isto quando quiser sinais técnicos extras do navegador sobre alterações suspeitas no ambiente. Isso não substitui outras políticas, mas adiciona mais contexto para auditoria e análise de incidentes.';
