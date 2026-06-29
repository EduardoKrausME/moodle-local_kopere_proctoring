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

    function getString(name, value) {
        try {
            return M.util.get_string(name, 'proctoringpolicy_password', value);
        } catch (e) {
            if (name === 'admin_lastcheck_nochange') {
                return 'Última verificação em ' + value + ': nada novo.';
            }
            if (name === 'admin_lastcheck_updated') {
                return 'Atualizado em ' + value + '.';
            }
            return value || '';
        }
    }

    function init(config) {
        if (!config || !config.url) {
            return;
        }

        let $container = $('[data-region="password-admin-content"]');
        let $meta = $('[data-region="password-admin-meta"]');
        let signature = String($container.attr('data-signature') || '');

        if ($container.length === 0) {
            return;
        }

        let loading = false;

        let refresh = function() {
            if (loading) {
                return;
            }

            loading = true;
            $.ajax({
                url: config.url,
                method: 'GET',
                dataType: 'json',
                cache: false,
                data: {
                    signature: signature,
                    _: Date.now()
                }
            }).done(function(response) {
                if (!response) {
                    return;
                }

                if (response.changed && typeof response.html === 'string') {
                    $container.html(response.html);
                    signature = String(response.signature || '');
                    $container.attr('data-signature', signature);

                    if ($meta.length && response.lastcheck) {
                        $meta.text(getString('admin_lastcheck_updated', response.lastcheck));
                    }
                    return;
                }

                if (response.signature) {
                    signature = String(response.signature);
                    $container.attr('data-signature', signature);
                }

                if ($meta.length && response.lastcheck) {
                    $meta.text(getString('admin_lastcheck_nochange', response.lastcheck));
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
