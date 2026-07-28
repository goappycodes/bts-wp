// ===============
// Category SEO "read more" toggle for the top content block.
// Shows ~5 lines with a fade, then expands/collapses on click.
// If the content already fits within the collapsed height, the toggle
// is hidden and the block is shown in full.
(function () {
    'use strict';

    function initSeoToggle() {
        var blocks = document.querySelectorAll('.bts-cat-seo--top');
        if (!blocks.length) {
            return;
        }

        Array.prototype.forEach.call(blocks, function (block) {
            var body = block.querySelector('.bts-cat-seo__body');
            var toggle = block.querySelector('.bts-cat-seo__toggle');
            if (!body || !toggle) {
                return;
            }

            // While collapsed, clientHeight is the clamped height and
            // scrollHeight is the full content height.
            var collapsedHeight = body.clientHeight;
            var fullHeight = body.scrollHeight;

            if (fullHeight <= collapsedHeight + 8) {
                block.classList.remove('is-collapsed');
                toggle.classList.add('is-hidden');
                return;
            }

            toggle.addEventListener('click', function () {
                var stillCollapsed = block.classList.toggle('is-collapsed');
                toggle.setAttribute('aria-expanded', stillCollapsed ? 'false' : 'true');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSeoToggle);
    } else {
        initSeoToggle();
    }
})();
