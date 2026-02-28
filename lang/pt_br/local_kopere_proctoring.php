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
 * ptbr lang file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['contract_desc'] = 'Antes de iniciar o questionário, o aluno assina virtualmente um compromisso de honestidade.';
$string['contract_label'] = 'Exigir assinatura?';
$string['contract_legend'] = 'Compromisso de honestidade';
$string['copypaste_desc'] = 'Marque se copiar e colar texto <b>não</b> é permitido.';
$string['copypaste_label'] = 'Bloquear copiar e colar';
$string['copypaste_legend'] = 'Copiar e colar';
$string['copypaste_limit_desc'] = 'Quantas ações de copiar/colar são permitidas antes que a tentativa seja encerrada? Cada ação exibe a mensagem abaixo; quando o limite é atingido, a tentativa é encerrada.';
$string['copypaste_limit_label'] = 'Limite de copiar e colar';
$string['copypaste_message_desc'] = 'Mensagem exibida quando o usuário tenta copiar ou colar texto durante o questionário.';
$string['copypaste_message_label'] = 'Mensagem ao copiar/colar';
$string['enable'] = 'Ativar?';
$string['fullscreen_desc'] = 'Marque se o questionário pode ser feito <b>apenas</b> em modo de tela cheia.';
$string['fullscreen_label'] = 'Tela cheia';
$string['fullscreen_legend'] = 'Tela cheia';
$string['fullscreen_limit_desc'] = 'Quantas vezes o aluno pode sair da tela cheia antes que a tentativa seja encerrada? Cada saída exibe a mensagem abaixo; quando o limite é atingido, a tentativa é encerrada.';
$string['fullscreen_limit_label'] = 'Encerrar ao sair';
$string['fullscreen_message_desc'] = 'Mensagem exibida quando o usuário sai da tela cheia.';
$string['fullscreen_message_label'] = 'Mensagem ao sair da tela cheia';
$string['mail_desc'] = 'Quando a tentativa for concluída, enviar um e-mail ao aluno com os registros de monitoramento. Isso aumenta a conscientização sobre o acompanhamento e desencoraja trapaças em futuros questionários.';
$string['mail_label'] = 'Enviar e-mail ao aluno';
$string['mail_legend'] = 'Notificar o aluno ao finalizar o questionário';
$string['message_contract'] = 'Mensagem do compromisso';
$string['message_contract_desc'] = 'Mensagem exibida com o compromisso de honestidade antes do início da tentativa.';
$string['modulename'] = 'Kopere Proctoring';
$string['open_dashboard'] = 'Abrir Kopere Proctoring';
$string['pluginname'] = 'Kopere Proctoring';
$string['return_exam'] = 'Entendi, voltar para o exame';
$string['settings'] = 'Configurar Kopere Proctoring';
$string['settings_contract'] = 'Contrato de honestidade';
$string['settings_contract_desc'] = 'O aluno deve assinar virtualmente um contrato antes da prova.';
$string['settings_contract_heading'] = 'Contrato de honestidade';
$string['settings_contract_message'] = 'Texto padrão do contrato';
$string['settings_contract_message_default'] = '<h2>Compromisso de Honestidade Acadêmica</h2>
<p><strong>Eu,</strong> <u>{name}</u>, ciente da importância da integridade acadêmica, declaro que:</p>
<ol>
    <li><strong>Realizarei esta avaliação individualmente</strong>, sem auxílio de outras pessoas, materiais não autorizados ou recursos tecnológicos externos (como mecanismos de busca, redes sociais ou inteligências artificiais).</li>
    <li><strong>Comprometo-me a não colar, plagiar, falsificar ou obter vantagem indevida</strong> durante esta prova ou atividade avaliativa.</li>
    <li><strong>Abster-me-ei de qualquer tentativa de manipulação técnica</strong> do sistema de avaliação, incluindo, mas não se limitando a: múltiplos acessos simultâneos, uso de dispositivos paralelos, alteração de arquivos do navegador ou uso de scripts automatizados.</li>
    <li><strong>Reconheço que a integridade acadêmica é essencial</strong> para meu desenvolvimento pessoal e profissional, e que atitudes desonestas comprometem não apenas meu aprendizado, mas também o respeito aos colegas e à instituição.</li>
    <li>Estou ciente de que <strong>qualquer violação deste compromisso poderá resultar em sanções acadêmicas</strong>, conforme os regulamentos institucionais, incluindo anulação da prova, reprovação ou outras penalidades cabíveis.</li>
</ol>';
$string['settings_contract_message_desc'] = 'Mensagem exibida ao aluno antes do exame.';
$string['settings_contract_start_warning'] = 'Você deve concordar antes de iniciar o exame.';
$string['settings_copypaste'] = 'Bloquear copiar e colar';
$string['settings_copypaste_desc'] = 'Bloqueia copiar e colar durante o exame.';
$string['settings_copypaste_heading'] = 'Copiar e colar';
$string['settings_copypaste_limit'] = 'Limite de copiar/colar';
$string['settings_copypaste_limit_desc'] = 'Número de vezes que o aluno pode copiar ou colar antes de o exame ser encerrado.';
$string['settings_copypaste_message'] = 'Mensagem ao copiar/colar';
$string['settings_copypaste_message_default'] = '<h2>🚫 Atenção! Tentativa de copiar/colar detectada.</h2>
<p><strong>⚠️ Se esta ação se repetir, seu exame será automaticamente encerrado</strong>, impedindo a continuidade.</p>
<p>Esta ação <strong>foi registrada</strong> e será revisada pela equipe responsável.</p>
<p>Por favor, mantenha seu compromisso com a honestidade acadêmica.</p>';
$string['settings_copypaste_message_desc'] = 'Mensagem exibida ao aluno ao tentar copiar ou colar.';
$string['settings_copypaste_message_init'] =
    '<p><strong>As funções de copiar e colar estão desativadas</strong>.<br>Se o sistema detectar <strong>qualquer tentativa de copiar ou colar texto</strong>, a avaliação será <strong>imediatamente encerrada</strong>, com o incidente registrado para análise pela equipe responsável.</p>';
$string['settings_fullscreen'] = 'Exigir tela cheia';
$string['settings_fullscreen_desc'] = 'O exame só pode ser realizado com o navegador em modo de tela cheia.';
$string['settings_fullscreen_heading'] = 'Tela cheia';
$string['settings_fullscreen_limit'] = 'Limite de saídas da tela cheia';
$string['settings_fullscreen_limit_desc'] = 'Número de vezes que o aluno pode sair da tela cheia antes de o exame ser encerrado.';
$string['settings_fullscreen_message'] = 'Mensagem ao sair da tela cheia';
$string['settings_fullscreen_message_default'] = '<h2>🚫 Atenção! Você saiu do <strong>modo Tela Cheia</strong> durante o exame.</h2>
<p>Você deve <strong>permanecer em modo Tela Cheia durante todo o exame</strong>.</p>
<p>⚠️ Se sair novamente, seu exame será automaticamente encerrado, impedindo a continuidade.</p>
<p>Esta ação <strong>foi registrada</strong> e será revisada pela equipe responsável.</p>
<p>Por favor, mantenha seu compromisso com a honestidade acadêmica.</p>';
$string['settings_fullscreen_message_desc'] = 'Mensagem exibida ao aluno ao sair da tela cheia.';
$string['settings_fullscreen_message_init'] =
    '<p>Este exame deve ser realizado em <strong>modo de tela cheia</strong>.<br>Se você <strong>sair do modo de tela cheia durante o exame</strong>, a tentativa será considerada <strong>fraude</strong> e a avaliação será <strong>encerrada automaticamente</strong>, com o incidente registrado para análise pela equipe responsável.</p>';
$string['settings_mail'] = 'Enviar e-mail após o exame';
$string['settings_mail_desc'] = 'Envia um e-mail ao aluno com os registros do exame após a conclusão.';
$string['settings_mail_heading'] = 'Notificação';
$string['settings_mail_moment'] = 'Notificar aluno em tentativa de trapaça';
$string['settings_mail_moment_desc'] = 'Se ativado, o aluno será imediatamente notificado por e-mail assim que o sistema detectar que ele saiu do modo <strong>Tela Cheia</strong> ou tentou <strong>copiar/colar</strong> durante o exame. Isso serve como um alerta preventivo para desencorajar novas tentativas.';
$string['settings_webcam'] = 'Exigir webcam';
$string['settings_webcam_desc'] = 'O exame requer o uso de webcam.';
$string['settings_webcam_heading'] = 'Webcam';
$string['settings_webcam_message'] = 'Mensagem antes da webcam';
$string['settings_webcam_message_default'] = '<h2>📷 Compartilhamento de Webcam Obrigatório</h2>
<p>Para garantir a segurança e integridade, <strong>sua webcam deve estar compartilhada durante todo o exame</strong>.</p>
<p>A captura de vídeo será utilizada exclusivamente para monitoramento e prevenção de fraudes, seguindo as diretrizes de privacidade e proteção de dados.</p>
<p>Se o acesso à câmera não for concedido, <strong>o exame não poderá iniciar ou será automaticamente encerrado</strong>.</p>
<p>Certifique-se de estar em um ambiente adequado, bem iluminado e com webcam funcional.</p>';
$string['settings_webcam_message_desc'] = 'Mensagem exibida acima do player da câmera.';
$string['settings_webcam_start_warning'] = 'Sua webcam deve estar funcionando antes de iniciar o exame.';
$string['start_exam'] = 'Iniciar Exame';
$string['webcam_desc'] = 'Uma webcam é necessária para realizar o questionário; sem ela, a tentativa não pode começar.';
$string['webcam_label'] = 'Exigir webcam';
$string['webcam_legend'] = 'Webcam';
$string['webcam_message_desc'] = 'Mensagem exibida acima da pré-visualização da câmera.';
$string['webcam_message_label'] = 'Mensagem antes da webcam';
