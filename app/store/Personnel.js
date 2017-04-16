Ext.define('Mtc.store.Personnel', {
    extend: 'Ext.data.Store',

    alias: 'store.personnel',

    autoLoad: true,

    proxy: {
        type: 'ajax',
        actionMethods: { read: 'POST'},
        url: '/index.php/welcome/table_list',
        reader: {
            type: 'json',
            rootProperty: 'items'
        }
    }
});
