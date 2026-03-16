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
 * contract_service.php
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_contract;

use context_module;
use context_system;
use dml_exception;
use local_kopere_proctoring\policy\cm_config;
use moodle_exception;
use moodle_url;
use stdClass;

/**
 * Class contract_service
 */
class contract_service {
    /**
     * Return the site shortname formatted for the header.
     *
     * @return string
     */
    public static function get_site_shortname(): string {
        global $SITE;

        return trim(format_string($SITE->shortname, true, [
            'context' => context_system::instance(),
        ]));
    }

    /**
     * Return the site fullname formatted for the header.
     *
     * @return string
     */
    public static function get_site_fullname(): string {
        global $SITE;

        return trim(format_string($SITE->fullname, true, [
            'context' => context_system::instance(),
        ]));
    }

    /**
     * Return the shortname token used in the document code.
     *
     * @return string
     */
    public static function get_site_shortname_token(): string {
        $token = \core_text::strtoupper(self::get_site_shortname());
        $token = preg_replace('/[^A-Z0-9]+/', '', $token);

        if ($token === '') {
            $token = 'MOODLE';
        }

        return $token;
    }

    /**
     * Convert the attempt record id into a stable hexadecimal code.
     *
     * @param int $attid
     * @return string
     */
    public static function get_attid_hex(int $attid): string {
        return strtoupper(bin2hex(pack('N', $attid)));
    }

    /**
     * Build the public document code.
     *
     * @param stdClass $att
     * @return string
     */
    public static function get_document_code(stdClass $att): string {
        return 'DOC-' . self::get_site_shortname_token() . '-' . self::get_attid_hex((int)$att->id);
    }

    /**
     * Decode a document code and return the attempt record id.
     *
     * @param string $code
     * @return int|null
     */
    public static function get_attid_from_document_code(string $code): ?int {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        if (!preg_match('/^DOC\-([A-Z0-9]+)\-([A-F0-9]{8})$/i', $code, $matches)) {
            return null;
        }

        $expectedtoken = self::get_site_shortname_token();
        if (\core_text::strtoupper($matches[1]) !== $expectedtoken) {
            return null;
        }

        return hexdec($matches[2]);
    }

    /**
     * Return the verification URL.
     *
     * @param string $documentcode
     * @return moodle_url
     */
    public static function get_verify_url(string $documentcode): moodle_url {
        return new moodle_url('/local/kopere_proctoring/policy/contract/v/index.php', [
            'code' => $documentcode,
        ]);
    }

    /**
     * Return the PDF URL for authenticated users.
     *
     * @param int $attemptid
     * @return moodle_url
     */
    public static function get_pdf_url(int $attemptid): moodle_url {
        return new moodle_url('/local/kopere_proctoring/policy/contract/index.php', [
            'attemptid' => $attemptid,
        ]);
    }

    /**
     * Load the local attempt record by Moodle attempt id.
     *
     * @param int $attemptid
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_att_by_attemptid(int $attemptid): stdClass {
        global $DB;

        return $DB->get_record('local_kopere_proctoring_att', [
            'attemptid' => $attemptid,
        ], '*', MUST_EXIST);
    }

    /**
     * Load the local attempt record by document code.
     *
     * @param string $code
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_att_by_document_code(string $code): ?stdClass {
        global $DB;

        $attid = self::get_attid_from_document_code($code);
        if (empty($attid)) {
            return null;
        }

        $att = $DB->get_record('local_kopere_proctoring_att', ['id' => $attid]);
        if (!$att) {
            return null;
        }

        if (self::get_document_code($att) !== trim($code)) {
            return null;
        }

        return $att;
    }

    /**
     * Return the user for the local attempt record.
     *
     * @param stdClass $att
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_user(stdClass $att): stdClass {
        global $DB;

        return $DB->get_record('user', ['id' => $att->userid], '*', MUST_EXIST);
    }

    /**
     * Return attempt / quiz / course module metadata.
     *
     * @param stdClass $att
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_attempt_meta(stdClass $att): stdClass {
        global $DB;

        $sql = 'SELECT qa.id AS attemptid,
                       qa.quiz,
                       cm.id AS cmid,
                       cm.course,
                       q.name AS quizname
                  FROM {quiz_attempts} qa
                  JOIN {quiz} q
                    ON q.id = qa.quiz
                  JOIN {modules} m
                    ON m.name = :modname
                  JOIN {course_modules} cm
                    ON cm.instance = q.id
                   AND cm.module = m.id
                 WHERE qa.id = :attemptid';

        return $DB->get_record_sql($sql, [
            'modname' => 'quiz',
            'attemptid' => $att->attemptid,
        ], MUST_EXIST);
    }

    /**
     * Return the effective contract message for an attempt.
     *
     * @param stdClass $att
     * @param stdClass $user
     * @return string
     * @throws dml_exception
     */
    public static function get_contract_html(stdClass $att, stdClass $user): string {
        $meta = self::get_attempt_meta($att);
        $globalmessage = get_config('proctoringpolicy_contract', 'message_default');
        $message = cm_config::get('contract', 'message', (int)$meta->cmid, $globalmessage);

        return str_replace([
            '{name}',
            '{\$a}',
        ], fullname($user), $message);
    }

    /**
     * Return a plain text excerpt of the accepted contract.
     *
     * @param string $html
     * @param int $limit
     * @return string
     */
    public static function get_contract_excerpt(string $html, int $limit = 900): string {
        $text = trim(preg_replace('/\s+/', ' ', html_to_text($html, 0, false)));

        if ($text === '') {
            return '';
        }

        if (\core_text::strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(\core_text::substr($text, 0, $limit - 3)) . '...';
    }

    /**
     * Create the unique SHA-256 hash used in the PDF.
     *
     * @param stdClass $att
     * @return string
     */
    public static function get_unique_hash(stdClass $att): string {
        $hashbase = implode('|', [
            (int)$att->id,
            (string)($att->contract_useragent ?? ''),
            (string)($att->contract_ip ?? ''),
            (int)($att->contract_time ?? 0),
        ]);

        return hash('sha256', $hashbase);
    }

    /**
     * Return the formatted acceptance date.
     *
     * @param int|null $timestamp
     * @return string
     */
    public static function format_datetime(?int $timestamp): string {
        if (empty($timestamp)) {
            return '-';
        }

        return userdate($timestamp, '%d/%m/%Y %H:%M %Z');
    }

    /**
     * Return the formatted issue date.
     *
     * @param int|null $timestamp
     * @return string
     */
    public static function format_issue_datetime(?int $timestamp): string {
        if (empty($timestamp)) {
            $timestamp = time();
        }

        return userdate($timestamp, '%d/%m/%Y, %H:%M %Z');
    }

    /**
     * Mask the user idnumber when it looks like a CPF.
     *
     * @param stdClass $user
     * @return string
     */
    public static function get_masked_idnumber(stdClass $user): string {
        $idnumber = preg_replace('/\D+/', '', (string)($user->idnumber ?? ''));

        if (strlen($idnumber) !== 11) {
            return '';
        }

        return 'XXX.' . substr($idnumber, 3, 3) . '.' . substr($idnumber, 6, 3) . '-XX';
    }

    /**
     * Record the contract acceptance.
     *
     * @param stdClass $att
     * @param array $payload
     * @return stdClass
     * @throws dml_exception
     */
    public static function accept(stdClass $att, array $payload): stdClass {
        global $DB;

        if (!empty($att->contract)) {
            return $att;
        }

        $att->contract = 1;
        $att->contract_ip = (string)($payload['ip'] ?? '');
        $att->contract_useragent = substr((string)($payload['useragent'] ?? ''), 0, 255);
        $att->contract_screenresolution = substr((string)($payload['screenresolution'] ?? ''), 0, 255);
        $att->contract_geo = substr((string)($payload['geo'] ?? ''), 0, 255);
        $att->contract_time = (int)($payload['time'] ?? time());

        $DB->update_record('local_kopere_proctoring_att', $att);

        return $att;
    }

    /**
     * Validate access to the PDF.
     *
     * @param stdClass $att
     * @return void
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function require_pdf_access(stdClass $att): void {
        global $USER;

        $meta = self::get_attempt_meta($att);
        $context = context_module::instance((int)$meta->cmid);

        if ((int)$USER->id === (int)$att->userid) {
            return;
        }

        if (has_capability('mod/quiz:viewreports', $context) || has_capability('moodle/site:config', $context)) {
            return;
        }

        throw new moodle_exception('nopermissions', 'error', '', 'download contract proof');
    }
}
