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
 * Utility class to parse browser user agent.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\util;

/**
 * Class user_agent
 */
class user_agent {

    /**
     * Parse user agent string.
     *
     * @param string $useragent
     * @return array
     * @throws \coding_exception
     */
    public static function parse(string $useragent): array {
        $result = [
            'browser' => get_string('unknown', 'local_kopere_proctoring'),
            'browser_version' => '',
            'os' => get_string('unknown', 'local_kopere_proctoring'),
            'os_version' => '',
            'device' => 'Desktop',
            'is_mobile' => false,
            'is_tablet' => false,
            'is_bot' => false,
        ];

        $useragent = trim($useragent);

        if ($useragent === '') {
            return $result;
        }

        self::parse_os($useragent, $result);
        self::parse_browser($useragent, $result);
        self::parse_device($useragent, $result);
        self::parse_bot($useragent, $result);

        return $result;
    }

    /**
     * Convert user agent to readable text.
     *
     * Example:
     * macOS 10.15.7 · Google Chrome 149 · Desktop
     *
     * @param string $useragent
     * @return string
     * @throws \coding_exception
     */
    public static function to_text(string $useragent): string {
        $data = self::parse($useragent);

        $os = trim($data['os'] . ' ' . $data['os_version']);

        $browserversion = $data['browser_version'];
        if ($browserversion && preg_match('/^([0-9]+)/', $browserversion, $match)) {
            $browserversion = $match[1];
        }

        $browser = trim($data['browser'] . ' ' . $browserversion);

        return "<strong>OS:</strong> {$os}<br>
                <strong>Browser:</strong> {$browser}<br>
                <strong>Device:</strong> {$data['device']}";
    }

    /**
     * Parse operating system.
     *
     * @param string $useragent
     * @param array $result
     * @return void
     */
    private static function parse_os(string $useragent, array &$result): void {
        if (preg_match('/Mac OS X ([0-9_]+)/i', $useragent, $match)) {
            $result['os'] = 'macOS';
            $result['os_version'] = str_replace('_', '.', $match[1]);
            return;
        }

        if (preg_match('/Windows NT ([0-9.]+)/i', $useragent, $match)) {
            $result['os'] = 'Windows';
            $result['os_version'] = self::windows_version_name($match[1]);
            return;
        }

        if (preg_match('/Android ([0-9.]+)/i', $useragent, $match)) {
            $result['os'] = 'Android';
            $result['os_version'] = $match[1];
            return;
        }

        if (preg_match('/iPhone OS ([0-9_]+)/i', $useragent, $match)) {
            $result['os'] = 'iOS';
            $result['os_version'] = str_replace('_', '.', $match[1]);
            return;
        }

        if (preg_match('/iPad.*OS ([0-9_]+)/i', $useragent, $match)) {
            $result['os'] = 'iPadOS';
            $result['os_version'] = str_replace('_', '.', $match[1]);
            return;
        }

        if (stripos($useragent, 'Linux') !== false) {
            $result['os'] = 'Linux';
        }
    }

    /**
     * Parse browser.
     *
     * @param string $useragent
     * @param array $result
     * @return void
     */
    private static function parse_browser(string $useragent, array &$result): void {
        // Important: order matters.
        // Edge, Opera, Brave and others may also include Chrome in user agent.

        if (preg_match('/Edg\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Microsoft Edge';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/OPR\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Opera';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/CriOS\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Google Chrome';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/FxiOS\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Mozilla Firefox';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/Chrome\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Google Chrome';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/Firefox\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Mozilla Firefox';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/Version\/([0-9.]+).*Safari/i', $useragent, $match)) {
            $result['browser'] = 'Safari';
            $result['browser_version'] = $match[1];
            return;
        }

        if (preg_match('/Safari\/([0-9.]+)/i', $useragent, $match)) {
            $result['browser'] = 'Safari';
            $result['browser_version'] = $match[1];
        }
    }

    /**
     * Parse device type.
     *
     * @param string $useragent
     * @param array $result
     * @return void
     */
    private static function parse_device(string $useragent, array &$result): void {
        if (preg_match('/iPad|Tablet/i', $useragent)) {
            $result['device'] = 'Tablet';
            $result['is_tablet'] = true;
            return;
        }

        if (preg_match('/Mobile|Android|iPhone|iPod/i', $useragent)) {
            $result['device'] = 'Celular';
            $result['is_mobile'] = true;
            return;
        }

        $result['device'] = 'Desktop';
    }

    /**
     * Detect bots/crawlers.
     *
     * @param string $useragent
     * @param array $result
     * @return void
     */
    private static function parse_bot(string $useragent, array &$result): void {
        if (preg_match('/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|whatsapp/i', $useragent)) {
            $result['is_bot'] = true;
            $result['device'] = 'Robô/Crawler';
        }
    }

    /**
     * Convert Windows NT version to friendly name.
     *
     * @param string $version
     * @return string
     */
    private static function windows_version_name(string $version): string {
        return match ($version) {
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
            '5.1' => 'XP',
            default => $version,
        };
    }
}
