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
 * Deliver a verified proctoring event using the configured notification policy.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\task;

use core\task\adhoc_task;
use local_kopere_proctoring\policy\manager;

/**
 * Perform mail delivery in Moodle's cron process, not an exam AJAX request.
 */
final class send_notification extends adhoc_task {
    /**
     * Dispatch the server-controlled notification event to active policies.
     *
     * @return void
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        if (empty($data->cmid) || empty($data->attemptid) || empty($data->eventkey)) {
            return;
        }
        manager::handle_server_event(
            (string)$data->eventkey,
            (int)$data->cmid,
            (int)$data->attemptid,
            (array)($data->payload ?? [])
        );
    }
}
