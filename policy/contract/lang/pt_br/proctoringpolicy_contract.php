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
 * proctoringpolicy_contract.php
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['accept_label'] = 'Declaro que li e compreendi os termos acima e me comprometo a realizar a prova de forma honesta, ética e responsável.';
$string['cancel_button'] = 'Cancelar';
$string['contract_title'] = 'Compromisso de Honestidade Acadêmica';
$string['enabled'] = 'Ativar contrato ';
$string['enabled_cm'] = 'Exigir aceite do contrato para esta prova';
$string['enabled_desc'] = 'Se ativado, os alunos devem ler e aceitar um contrato de honestidade antes de iniciar a prova.';
$string['error_not_accepted'] = 'Você deve aceitar o contrato antes de iniciar a prova.';
$string['heading_info'] = 'Adiciona uma etapa obrigatória de confirmação de honestidade antes do início da prova e só permite que o aluno avance para o fluxo da avaliação após o aceite explícito.';
$string['message_cm'] = 'Texto do contrato para esta prova';
$string['message_cm_help'] = 'Este texto será exibido aos alunos, que deverão aceitá-lo antes de iniciar a prova.';
$string['message_default'] = 'Texto padrão do contrato de honestidade';
$string['message_default_desc'] = 'Texto padrão do contrato de honestidade exibido antes do início da prova. <i>No questionário pode alterar este valor</i>';
$string['message_default_text'] = '<p><strong>Eu,</strong> <u>{name}</u>, ciente da importância da integridade acadêmica, declaro que:</p>
<ol>
    <li><strong>Realizarei esta avaliação individualmente</strong>, sem ajuda de outras pessoas, materiais não autorizados ou recursos tecnológicos externos (como mecanismos de busca, redes sociais ou inteligência artificial).</li>
    <li><strong>Comprometo-me a não colar, plagiar, falsificar ou obter vantagem indevida</strong> durante esta prova ou atividade avaliativa.</li>
    <li><strong>Não realizarei nenhuma tentativa de manipulação técnica</strong> do sistema de avaliação, incluindo, mas não se limitando a: múltiplos acessos simultâneos, uso de dispositivos paralelos, alteração de arquivos do navegador ou scripts de automação.</li>
    <li><strong>Reconheço que a integridade acadêmica é essencial</strong> para meu desenvolvimento pessoal e profissional, e que atitudes desonestas comprometem não apenas meu aprendizado, mas também o respeito aos colegas e à instituição.</li>
    <li>Estou ciente de que <strong>qualquer violação deste compromisso poderá resultar em sanções acadêmicas</strong>, conforme as normas institucionais, incluindo anulação da prova, reprovação ou outras penalidades aplicáveis.</li>
</ol>';
$string['pdf_acceptance_date'] = 'Data e hora do aceite:';
$string['pdf_accepted_text'] = 'TEXTO ACEITO';
$string['pdf_cpf'] = 'CPF:';
$string['pdf_digitally_signed_at'] = 'Assinado digitalmente em: {$a}';
$string['pdf_email'] = 'E-mail:';
$string['pdf_full_name'] = 'Nome completo:';
$string['pdf_intro_text'] = 'Este documento comprova o aceite digital dos termos configurados para este questionário pelo aluno identificado abaixo, utilizando métodos de certificação eletrônica.';
$string['pdf_ip_address'] = 'Endereço IP:';
$string['pdf_issue_date'] = 'Data de emissão:';
$string['pdf_legal_notice'] = 'Este documento possui validade jurídica nos termos da Medida Provisória brasileira nº 2.200-2/2001.';
$string['pdf_main_title'] = 'TERMO DE ACEITE E CERTIFICAÇÃO DIGITAL';
$string['pdf_receipt_title'] = 'COMPROVANTE DE ASSINATURA DIGITAL';
$string['pdf_signature_details'] = 'DETALHES DA ASSINATURA DIGITAL';
$string['pdf_signature_validated'] = 'ASSINATURA DIGITAL VALIDADA';
$string['pdf_student_data'] = 'DADOS DO ALUNO';
$string['pdf_subject'] = 'Comprovante de assinatura digital';
$string['pdf_unique_hash'] = 'Hash único (SHA-256):';
$string['pdf_useragent'] = 'UserAgent:';
$string['pdf_username'] = 'Matrícula:';
$string['pdf_validated_by'] = 'Validade confirmada pelo sistema de assinatura acadêmica {$a}.';
$string['pdf_validation_title'] = 'CERTIFICAÇÃO DE VALIDADE';
$string['pdf_verification_code'] = 'Código de verificação: {$a}';
$string['pdf_verify_at'] = 'Verifique a autenticidade em:';
$string['pluginname'] = 'Contrato de honestidade';
$string['proof_link'] = 'Abrir comprovante de aceite digital (PDF)';
$string['proof_not_accepted'] = 'O comprovante de aceite fica disponível apenas após o contrato ter sido aceito.';
$string['requirement_label'] = 'Ler e aceitar os termos de proctoring';
$string['teacher_info'] = 'Use esta política quando o aluno precisar ler e aceitar regras, um código de honra, um aviso de privacidade ou termos da instituição antes que a primeira questão seja exibida. Você pode personalizar o texto do contrato para esta prova.';
$string['verification_code_label'] = 'Código de verificação:';
$string['verification_exam'] = 'Questionário:';
$string['verification_invalid_desc'] = 'O código {$a} é inválido, não pertence a este site ou ainda não foi aceito digitalmente.';
$string['verification_invalid_title'] = 'Documento não encontrado';
$string['verification_page_intro'] = 'Use o código abaixo para validar a autenticidade de um comprovante de aceite de contrato gerado por este site Moodle.';
$string['verification_page_title'] = 'Verificação de aceite digital';
$string['verification_valid_badge'] = 'DOCUMENTO VÁLIDO';
