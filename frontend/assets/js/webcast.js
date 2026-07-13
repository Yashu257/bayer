(function () {
    'use strict';

    // ── Config ─────────────────────────────────────────────────────────────────
    var W = window.WEBCAST || {};
    var stream        = W.stream        || { type: 'placeholder' };
    var sidebar       = W.sidebar       || {};
    var heartbeatUrl  = W.heartbeatUrl  || '';
    var heartbeatEvery = W.heartbeatEvery || 60;
    var eventSlug     = (W.event || {}).slug || '';
    var csrfToken     = '';

    // ── Init ───────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        csrfToken = (document.querySelector('input[name="_csrf_token"]') || {}).value || '';

        loadPlayer();
        renderQuestions(sidebar.questions || []);
        renderPoll(sidebar.poll || null);
        renderQuiz(sidebar.quiz || null);
        renderAnnouncements(sidebar.announcements || []);
        startHeartbeat();
        bindQaForm();
        bindCharCount();
    });

    // ══════════════════════════════════════════════════════════════════════════
    // PLAYER LOADER
    // Reads window.WEBCAST.stream.type and builds the correct embed.
    // To add a new provider: add a case here + a new Provider class in PHP.
    // ══════════════════════════════════════════════════════════════════════════

    function loadPlayer() {
        var wrap        = document.getElementById('stream-player');
        var placeholder = document.getElementById('stream-placeholder');

        if (!wrap) return;

        switch (stream.type) {

            case 'vimeo':
                if (!stream.videoId) break;
                placeholder.style.display = 'none';
                var vParams = buildQueryString(Object.assign({}, stream.params || {}));
                var vIframe = makeIframe(
                    'https://player.vimeo.com/video/' + stream.videoId + (vParams ? '?' + vParams : ''),
                    'Vimeo player'
                );
                vIframe.allow = 'autoplay; fullscreen; picture-in-picture';
                wrap.appendChild(vIframe);
                break;

            case 'youtube':
                if (!stream.videoId) break;
                placeholder.style.display = 'none';
                var ytParams = buildQueryString(Object.assign({}, stream.params || {}));
                var ytIframe = makeIframe(
                    'https://www.youtube.com/embed/' + stream.videoId + (ytParams ? '?' + ytParams : ''),
                    'YouTube player'
                );
                ytIframe.allow = 'autoplay; fullscreen; picture-in-picture';
                wrap.appendChild(ytIframe);
                break;

            case 'wowza':
                if (!stream.streamUrl) break;
                placeholder.style.display = 'none';
                loadWowza(wrap, stream.streamUrl, (stream.params || {}).hlsjs_version || '1.5.7');
                break;

            case 'placeholder':
            default:
                // Leave placeholder visible
                break;
        }
    }

    function makeIframe(src, title) {
        var f = document.createElement('iframe');
        f.src       = src;
        f.title     = title;
        f.allowFullscreen = true;
        f.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:0;';
        return f;
    }

    function loadWowza(wrap, hlsUrl, hlsVersion) {
        // Dynamically load HLS.js then start the stream
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/hls.js@' + hlsVersion + '/dist/hls.min.js';
        script.onload = function () {
            var video = document.createElement('video');
            video.controls  = true;
            video.autoplay  = stream.autoplay;
            video.muted     = true;   // required for autoplay in most browsers
            video.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;background:#000;';

            wrap.appendChild(video);

            if (window.Hls && Hls.isSupported()) {
                var hls = new Hls();
                hls.loadSource(hlsUrl);
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED, function () {
                    video.play();
                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                // Safari native HLS
                video.src = hlsUrl;
                video.addEventListener('loadedmetadata', function () { video.play(); });
            }
        };
        document.head.appendChild(script);
    }

    function buildQueryString(params) {
        return Object.keys(params)
            .filter(function (k) { return params[k] !== '' && params[k] !== null && params[k] !== undefined; })
            .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
            .join('&');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Q&A
    // ══════════════════════════════════════════════════════════════════════════

    function renderQuestions(questions) {
        var list    = document.getElementById('qa-list');
        var empty   = document.getElementById('qa-empty');
        var counter = document.querySelector('.wc-qa-count');

        if (!list) return;

        // Remove previous cards (keep empty state node)
        list.querySelectorAll('.wc-question').forEach(function (el) { el.remove(); });

        if (!questions.length) {
            if (empty) empty.style.display = '';
            if (counter) counter.classList.add('d-none');
            return;
        }

        if (empty) empty.style.display = 'none';
        if (counter) {
            counter.textContent = questions.length;
            counter.classList.remove('d-none');
        }

        questions.forEach(function (q) {
            var card = document.createElement('div');
            card.className = 'wc-question' + (q.is_answered ? ' answered' : '');
            card.dataset.id = q.id;

            card.innerHTML =
                '<p class="wc-question-text mb-1">' + escHtml(q.question_text) + '</p>' +
                '<div class="wc-question-meta">' +
                    '<span>' + escHtml(q.asked_by_name || 'Attendee') + '</span>' +
                    '<span class="d-flex align-items-center gap-2">' +
                        (q.is_answered
                            ? '<span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i>Answered</span>'
                            : '') +
                        '<button class="wc-upvote" data-id="' + q.id + '" aria-label="Upvote">' +
                            '<i class="bi bi-chevron-up"></i>' +
                            '<span class="upvote-count">' + (q.upvote_count || 0) + '</span>' +
                        '</button>' +
                    '</span>' +
                '</div>';

            list.appendChild(card);
        });

        // Delegate upvote clicks
        list.addEventListener('click', handleUpvoteClick);
    }

    function handleUpvoteClick(e) {
        var btn = e.target.closest('.wc-upvote');
        if (!btn || btn.classList.contains('voted')) return;

        var id  = btn.dataset.id;
        var cnt = btn.querySelector('.upvote-count');

        btn.classList.add('voted');
        if (cnt) cnt.textContent = parseInt(cnt.textContent || '0', 10) + 1;

        jsonPost('/e/' + eventSlug + '/questions/' + id + '/upvote', {});
    }

    function bindQaForm() {
        var form   = document.getElementById('qa-form');
        var errBox = document.getElementById('qa-error');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = document.getElementById('qa-input');
            var text  = (input.value || '').trim();

            if (!text) {
                showQaError('Please enter a question.');
                return;
            }

            var btn = document.getElementById('qa-submit');
            btn.disabled = true;

            jsonPost('/e/' + eventSlug + '/questions', { question_text: text })
                .then(function (data) {
                    input.value = '';
                    updateCharCount(400);
                    if (errBox) errBox.style.display = 'none';
                    if (data && data.question) {
                        prependQuestion(data.question);
                    }
                })
                .catch(function () {
                    showQaError('Could not submit. Please try again.');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    }

    function prependQuestion(q) {
        var list  = document.getElementById('qa-list');
        var empty = document.getElementById('qa-empty');
        if (!list) return;
        if (empty) empty.style.display = 'none';

        var card = document.createElement('div');
        card.className = 'wc-question';
        card.innerHTML =
            '<p class="wc-question-text mb-1">' + escHtml(q.question_text) + '</p>' +
            '<div class="wc-question-meta">' +
                '<span>You</span>' +
                '<span><button class="wc-upvote voted" data-id="' + q.id + '">' +
                    '<i class="bi bi-chevron-up"></i><span class="upvote-count">0</span>' +
                '</button></span>' +
            '</div>';

        list.insertBefore(card, list.firstChild);
    }

    function showQaError(msg) {
        var el = document.getElementById('qa-error');
        if (el) { el.textContent = msg; el.style.display = ''; }
    }

    function bindCharCount() {
        var input   = document.getElementById('qa-input');
        var counter = document.getElementById('qa-char-count');
        if (!input || !counter) return;

        input.addEventListener('input', function () {
            updateCharCount(400 - input.value.length);
        });
    }

    function updateCharCount(remaining) {
        var el = document.getElementById('qa-char-count');
        if (el) el.textContent = Math.max(0, remaining);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // POLL
    // ══════════════════════════════════════════════════════════════════════════

    function renderPoll(poll) {
        var empty = document.getElementById('poll-empty');
        var card  = document.getElementById('poll-card');

        if (!poll || !poll.id) {
            if (empty) empty.style.display = '';
            if (card)  card.style.display  = 'none';
            return;
        }

        if (empty) empty.style.display = 'none';
        if (card)  card.style.display  = '';

        var qEl = document.getElementById('poll-question');
        if (qEl) qEl.textContent = poll.question;

        var optContainer = document.getElementById('poll-options');
        if (optContainer) {
            optContainer.innerHTML = '';
            (poll.options || []).forEach(function (opt) {
                var div   = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML =
                    '<input class="form-check-input" type="radio" name="poll_option" ' +
                    '       id="opt-' + opt.id + '" value="' + opt.id + '">' +
                    '<label class="form-check-label" for="opt-' + opt.id + '">' +
                        escHtml(opt.option_text) +
                    '</label>';
                optContainer.appendChild(div);
            });
        }

        var form = document.getElementById('poll-form');
        if (form) {
            form.onsubmit = function (e) {
                e.preventDefault();
                var chosen = form.querySelector('input[name="poll_option"]:checked');
                if (!chosen) return;

                jsonPost('/e/' + eventSlug + '/polls/' + poll.id + '/vote', { option_id: chosen.value })
                    .then(function (data) { showPollResults(poll, data); })
                    .catch(function () {});
            };
        }
    }

    function showPollResults(poll, data) {
        var form    = document.getElementById('poll-form');
        var results = document.getElementById('poll-results');
        var bars    = document.getElementById('poll-results-bars');

        if (form)    form.style.display    = 'none';
        if (results) results.style.display = '';

        if (!bars) return;
        bars.innerHTML = '';

        var options = (data && data.options) ? data.options : (poll.options || []);
        var total   = options.reduce(function (s, o) { return s + (o.vote_count || 0); }, 0);

        options.forEach(function (opt) {
            var pct = total > 0 ? Math.round((opt.vote_count / total) * 100) : 0;
            var row = document.createElement('div');
            row.className = 'wc-poll-bar';
            row.innerHTML =
                '<div class="wc-poll-bar-label">' +
                    '<span>' + escHtml(opt.option_text) + '</span>' +
                    '<span>' + pct + '%</span>' +
                '</div>' +
                '<div class="wc-poll-bar-track">' +
                    '<div class="wc-poll-bar-fill" style="width:' + pct + '%"></div>' +
                '</div>';
            bars.appendChild(row);
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // QUIZ
    // ══════════════════════════════════════════════════════════════════════════

    function renderQuiz(quiz) {
        var empty = document.getElementById('quiz-empty');
        var card  = document.getElementById('quiz-card');

        if (!quiz || !quiz.id) {
            if (empty) empty.style.display = '';
            if (card)  card.style.display  = 'none';
            return;
        }

        if (empty) empty.style.display = 'none';
        if (card)  card.style.display  = '';

        var title = document.getElementById('quiz-title');
        var desc  = document.getElementById('quiz-description');
        var meta  = document.getElementById('quiz-time-limit');
        var link  = document.getElementById('quiz-start-link');

        if (title) title.textContent = quiz.title;
        if (desc)  desc.textContent  = quiz.description || '';
        if (meta && quiz.time_limit_seconds) {
            meta.textContent = Math.floor(quiz.time_limit_seconds / 60) + ' min time limit';
        }
        if (link) link.href = '/e/' + eventSlug + '/quiz/' + quiz.id;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ANNOUNCEMENTS
    // ══════════════════════════════════════════════════════════════════════════

    function renderAnnouncements(items) {
        var list  = document.getElementById('announce-list');
        var empty = document.getElementById('announce-empty');
        var badge = document.querySelector('.wc-announce-badge');

        if (!list) return;

        list.querySelectorAll('.wc-announcement').forEach(function (el) { el.remove(); });

        if (!items.length) {
            if (empty) empty.style.display = '';
            if (badge) badge.classList.add('d-none');
            return;
        }

        if (empty) empty.style.display = 'none';
        if (badge) badge.classList.remove('d-none');

        items.forEach(function (item) {
            var div = document.createElement('div');
            div.className = 'wc-announcement';
            div.innerHTML =
                escHtml(item.message) +
                '<div class="wc-announcement-time">' + formatTime(item.created_at) + '</div>';
            list.appendChild(div);
        });
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HEARTBEAT — POST every N seconds to record watch time
    // ══════════════════════════════════════════════════════════════════════════

    function startHeartbeat() {
        if (!heartbeatUrl) return;
        setInterval(function () {
            jsonPost(heartbeatUrl, {});
        }, heartbeatEvery * 1000);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UTILITIES
    // ══════════════════════════════════════════════════════════════════════════

    function jsonPost(url, body) {
        var payload = Object.assign({ _csrf_token: csrfToken }, body);
        return fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify(payload),
        }).then(function (r) { return r.json(); });
    }

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatTime(ts) {
        if (!ts) return '';
        try { return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }
        catch (e) { return ''; }
    }

})();
