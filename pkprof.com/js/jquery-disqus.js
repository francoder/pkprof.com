/**
 * https://github.com/timothyshaw/jquery-disqus
 */
(function ($, undefined) {
    var disqusContainers = '.disqus-container';
    var disqusShortName = $('#disqus').data('dsq');

    function init() {
        addListener();
    }

    function addListener() {
        $(disqusContainers).on('loadDisqus', loadDisqus);
    }

    function loadDisqus() {
        if (window.DISQUS) {
            $container = $(this);
            $thread = $('#disqus_thread').length ? $('#disqus_thread') : $('<div id="disqus_thread"></div>');

            $thread.appendTo($container);

            DISQUS.reset({
                reload: true,
                config: function () {
                    this.page.url = $container.data('disqus-url');
                    this.page.identifier = $container.data('disqus-id');
                    this.page.title = $container.data('disqus-title');
                }
            });
        } else {
            initDisqus(this);
        }
    }

    function initDisqus(container) {
        var $container = $(container);

        $('<div id="disqus_thread"></div>').appendTo($container);

        disqus_config = function () {
            this.page.url = $container.data('disqus-url');
            this.page.identifier = $container.data('disqus-id');
            this.page.title = $container.data('disqus-title');
        };

        (function () {
            var d = document, s = d.createElement('script');

            s.src = 'https://' + disqusShortName + '.disqus.com/embed.js';

            s.setAttribute('data-timestamp', +new Date());
            (d.head || d.body).appendChild(s);
        })();
    }

    $(init);
})(jQuery);
