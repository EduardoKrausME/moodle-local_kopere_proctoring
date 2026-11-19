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
 * Tests the localised Moodle profile link without changing access rules.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use core_user\output\myprofile\tree;
use moodle_url;

require_once(__DIR__ . '/../lib.php');

/**
 * The profile content and its destination follow Moodle's own strings.
 */
final class profile_navigation_test extends advanced_testcase {
    /**
     * The profile node uses the already-translated user-data label.
     */
    public function test_profile_node_uses_localised_content_and_preserves_url(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $tree = new tree();

        $this->assertTrue(\local_kopere_proctoring_myprofile_navigation($tree, $user, true, null));
        $this->assertArrayHasKey('local_proctoring_3', $tree->nodes);
        $node = $tree->nodes['local_proctoring_3'];
        $this->assertSame('contact', $node->parentcat);
        $this->assertSame(get_string('pluginname', 'local_kopere_proctoring'), $node->title);
        $this->assertSame(get_string('userdata_nav', 'local_kopere_proctoring'), $node->content);
        $this->assertSame(
            (new moodle_url('/local/kopere_proctoring/user-data.php', ['id' => $user->id]))->out(false),
            $node->url->out(false)
        );
    }

    /**
     * The node renders the requested profile, not the viewing user's profile.
     */
    public function test_other_users_profile_keeps_the_same_localised_label(): void {
        $this->resetAfterTest();
        $viewer = $this->getDataGenerator()->create_user();
        $subject = $this->getDataGenerator()->create_user();
        $this->setUser($viewer);
        $tree = new tree();

        $this->assertTrue(\local_kopere_proctoring_myprofile_navigation($tree, $subject, false, null));
        $node = $tree->nodes['local_proctoring_3'];
        $this->assertSame(get_string('userdata_nav', 'local_kopere_proctoring'), $node->content);
        $this->assertStringContainsString('id=' . $subject->id, $node->url->out(false));
        $this->assertStringNotContainsString('id=' . $viewer->id . '&', $node->url->out(false));
    }
}
