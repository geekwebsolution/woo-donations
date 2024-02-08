(function (blocks, element) {
    var el = element.createElement;
    const htmlToElem = ( html ) => element.RawHTML( { children: html } );

    blocks.registerBlockType('woo-donations-block/woo-donations', {
        title: 'Woo Donations',
        icon: 'money-alt',
        category: 'common',

        // Call the generateHTML function in both edit and save methods
        edit: function() {
            return [
                el( 'style', null, wdgkObject.buttonstyle ),
                htmlToElem( wdgkObject.blockhtml )
            ];
        },

        save: function() {
            return htmlToElem( wdgkObject.blockhtml );
        },
    });
})(wp.blocks, wp.element);