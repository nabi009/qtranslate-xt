/**
 * middleware handler for Gutenberg editor
 *
 * $author herrvigg
 */

(function () {
    console.log('setup apiFetch');

    wp.apiFetch.use((options, next) => {
        if (options.path) {
            const post_id = wp.data.select('core/editor').getCurrentPostId();
            if ((options.path.startsWith('/wp/v2/posts/' + post_id) && options.method == 'PUT') ||
                (options.path.startsWith('/wp/v2/posts/' + post_id + '/autosaves') && options.method == 'POST')) {
                const newOptions = {
                    ...options,
                    data: {
                        ...options.data,
                        'qtx_lang': 'fr'
                    }
                };
                const result = next(newOptions);
                return result;
            }
        }
        return next(options);
    });
})();
