/**
 * middleware handler for Gutenberg editor
 *
 * $author herrvigg
 */

(function () {
    console.log('setup apiFetch');

    wp.apiFetch.use((options, next) => {
        if (options.path) {
            const post = wp.data.select('core/editor').getCurrentPost();
            if ((options.path.startsWith('/wp/v2/posts/' + post.id) && options.method == 'PUT') ||
                (options.path.startsWith('/wp/v2/posts/' + post.id + '/autosaves') && options.method == 'POST')) {
                console.log('Post', post);
                if (! post.hasOwnProperty('qtx_editor_lang')) {
                    console.log('Missing field: \'qtx_editor_lang\' in post id=' + post.id);
                    return next(options);
                }
                const newOptions = {
                    ...options,
                    data: {
                        ...options.data,
                        'qtx_editor_lang': post.qtx_editor_lang
                    }
                };
                const result = next(newOptions);
                return result;
            }
        }
        return next(options);
    });
})();
