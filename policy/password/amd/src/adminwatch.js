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
 * adminwatch.js
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    'use strict';

    let started = false;
    let summaryLoading = false;
    let adminLoading = false;
    let adminSignature = '';

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

    function isAdminPage() {
        return window.location.pathname.indexOf('/local/kopere_proctoring/policy/password/admin.php') !== -1;
    }

    function showPopover(count) {
        let $popover = $('#kopere-password-admin-popover');
        if ($popover.length === 0) {
            $popover = $(`
                <div id="kopere-password-admin-popover" role="status" aria-live="polite">
                    <div class="kopere-password-admin-popover-title"></div>
                    <div class="kopere-password-admin-popover-body"></div>
                    <div class="kopere-password-admin-popover-actions">
                        <a class="btn btn-primary btn-sm" href=""></a>
                    </div>
                </div>
            `);
            $('body').append($popover);
        }

        $popover.find('.kopere-password-admin-popover-title').text(
            M.util.get_string('admin_popover_title', 'proctoringpolicy_password')
        );
        $popover.find('.kopere-password-admin-popover-body').text(
            M.util.get_string('admin_popover_body', 'proctoringpolicy_password', count)
        );
        $popover.find('a')
            .attr('href', `${M.cfg.wwwroot}/local/kopere_proctoring/policy/password/admin.php`)
            .attr('target', '_blank')
            .text(M.util.get_string('admin_popover_open', 'proctoringpolicy_password'));
        $popover.show();
    }

    function hidePopover() {
        $('#kopere-password-admin-popover').hide();
    }

    function getQuizPendingRegion() {
        let $region = $('#kopere-password-quiz-pending');
        if ($region.length) {
            return $region;
        }

        let $target = $('.quizattemptcounts').first();
        if ($target.length === 0) {
            return $();
        }

        $region = $('<div id="kopere-password-quiz-pending" data-region="kopere-password-quiz-pending"></div>');
        $target.after($region);
        return $region;
    }

    function hideQuizSummary() {
        $('#kopere-password-quiz-pending').empty().hide();
    }

    function refreshQuizSummary() {
        var isquizview = window.location.href.includes('/mod/quiz/view.php');
        if (!isquizview || summaryLoading) {
            return;
        }

        let $region = getQuizPendingRegion();
        if ($region.length === 0) {
            return;
        }

        summaryLoading = true;
        $.ajax({
            url: `${M.cfg.wwwroot}/local/kopere_proctoring/policy/password/admin.php?ajax=1&summary=1&sesskey=${M.cfg.sesskey}`,
            method: 'GET',
            dataType: 'json',
            cache: false
        }).done(function(response) {
            if (!response || !response.html || Number(response.count || 0) <= 0) {
                hideQuizSummary();
                return;
            }

            $region.html(response.html).show();
        }).always(function() {
            summaryLoading = false;
        });
    }

    function addQueryParam(url, name, value) {
        let separator = url.indexOf('?') === -1 ? '?' : '&';
        return url + separator + encodeURIComponent(name) + '=' + encodeURIComponent(value);
    }

    function buildAdminUrl(config) {
        if (config && config.adminurl) {
            return String(config.adminurl);
        }

        let url = `${M.cfg.wwwroot}/local/kopere_proctoring/policy/password/admin.php`;
        let query = window.location.search.replace(/^\?/, '');
        let keep = [];

        if (query !== '') {
            query.split('&').forEach(function(part) {
                let key = decodeURIComponent(part.split('=')[0] || '');
                if (key !== 'ajax' && key !== 'sesskey' && key !== 'signature' && key !== '_') {
                    keep.push(part);
                }
            });
        }

        if (keep.length) {
            url += '?' + keep.join('&');
        }

        url = addQueryParam(url, 'ajax', '1');
        url = addQueryParam(url, 'sesskey', M.cfg.sesskey);
        return url;
    }

    function refreshAdminTable(config) {
        if (adminLoading) {
            return;
        }

        let $container = $('[data-region="password-admin-content"]');
        if ($container.length === 0) {
            return;
        }

        let $meta = $('[data-region="password-admin-meta"]');
        let url = buildAdminUrl(config);
        adminSignature = adminSignature || String($container.attr('data-signature') || '');

        adminLoading = true;
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: {
                signature: adminSignature,
                _: Date.now()
            }
        }).done(function(response) {
            if (!response) {
                return;
            }

            if (response.changed && typeof response.html === 'string') {
                $container.html(response.html);
                adminSignature = String(response.signature || '');
                $container.attr('data-signature', adminSignature);

                if ($meta.length && response.lastcheck) {
                    $meta.text(getString('admin_lastcheck_updated', response.lastcheck));
                }
                return;
            }

            if (response.signature) {
                adminSignature = String(response.signature);
                $container.attr('data-signature', adminSignature);
            }

            if ($meta.length && response.lastcheck) {
                $meta.text(getString('admin_lastcheck_nochange', response.lastcheck));
            }
        }).always(function() {
            adminLoading = false;
        });
    }

    function checkPending() {
        if (isAdminPage()) {
            return;
        }

        $.ajax({
            url: `${M.cfg.wwwroot}/local/kopere_proctoring/policy/password/pending.php`,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: {_: Date.now()}
        }).done(function(response) {
            let total = Number(response && response.total ? response.total : 0);
            if (total > 0) {
                showPopover(total);
                refreshQuizSummary();
                return;
            }

            hidePopover();
            hideQuizSummary();
        });
    }

    function init(config) {
        if (started) {
            return;
        }
        started = true;

        if (isAdminPage()) {
            refreshAdminTable(config || {});
            window.setInterval(function() {
                refreshAdminTable(config || {});
            }, Number((config && config.admininterval) || 10000));
            return;
        }

        checkPending();
        window.setInterval(function() {
            checkPending();
        }, Number((config && config.interval) || 5000));
    }

    return {
        init: init
    };
});
