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
 * admin.js
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    'use strict';

    function getString(key, fallback) {
        var component = 'proctoringpolicy_password';
        try {
            if (window.M && window.M.util && window.M.util.get_string) {
                return window.M.util.get_string(key, component);
            }
        } catch (e) {
            // Ignore and use fallback.
        }
        return fallback || '';
    }

    function init(config) {
        if (!config || !config.url) {
            return;
        }

        var $container = $('[data-region="password-admin-content"]');
        var $meta = $('[data-region="password-admin-meta"]');

        if ($container.length === 0) {
            return;
        }

        var loading = false;

        var refresh = function() {
            if (loading) {
                return;
            }

            loading = true;
            $.ajax({
                url: config.url,
                method: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (!response) {
                    return;
                }

                if (typeof response.html === 'string') {
                    $container.html(response.html);
                }

                if ($meta.length && response.lastupdated) {
                    $meta.text(
                        getString('admin_refreshing', 'Automatically refreshing every 10 seconds.') + ' ' + response.lastupdated
                    );
                }
            }).always(function() {
                loading = false;
            });
        };

        window.setInterval(refresh, Number(config.interval || 10000));
    }

    return {
        init: init
    };
});
