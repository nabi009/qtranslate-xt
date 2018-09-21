/*var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };*/


var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType;

registerBlockType('qtx/qtx-admin2', {
    title: 'qTranslate-XT admin',
    category: 'embed',
    attributes: {
        lang: {
            type: 'string',
            source: 'meta',
            meta: 'qtx_admin2'
        }
    },
    edit: function ( props ) {
        function onChange( event ) {
            props.setAttributes( { lang: event.target.value } );
        }

        return el( 'input', {
            value: props.attributes.lang,
            onChange: onChange,
        } );
    },
    save () {
        return null;
    }
});

/*
function addBlockClassName( props, blockType ) {
    return Object.assign( props, { qtxt: 'WTFTESTT' } );
}

wp.hooks.addFilter(
    'blocks.getSaveContent.extraProps',
    'gdt-guten-plugin/add-block-class-name',
    addBlockClassName
);*/

//wp.data.select('core/editor').getCurrentPost()['meta']['qtx_admin'] = 'new wtf!!';

/*const { subscribe } = wp.data;
subscribe( () => {
   console.log('change');
});*/

function patch_submit(state, action) {
    console.log('reducing action', action.type);
    switch (action.type) {
        case 'UPDATE_POST':
            return Object.assign({}, state, {
                qtx_my_lang: 'fr'
            });
        default:
            return state;
    }
}

// wp.coreData.default.replaceReducer(patch_submit)

 wp.data.registerReducer('qtx_reducer', patch_submit)

/*
wp.apiFetch( { path: '/wp/v2/posts/' } ).then( posts => {
    console.log( posts );
} )
*/