/**
 * This view is an example list of people.
 */
Ext.define('Mtc.view.main.List', {
    extend: 'Ext.grid.Panel',
    xtype: 'mainlist',

    requires: [
        'Mtc.store.Personnel'
    ],

    title: 'Databases & Tables',
    border: true,

    tbar: [
        {
            boxLabel: 'Only Show Favorite',
            itemId: 'fav',
            //make checkbox stateful
            stateful: true,
            stateId: 'table-fav',
            getState: function() {
                return { "checked": this.getValue() };
            },
            applyState: function(state) {
                this.setValue( state.checked );
            },
            stateEvents: [
                'click',
                'change',
                'check'
            ],
            //make checkbox stateful end
            listeners: {
                change: 'showFavChange'
            },
            xtype: 'checkbox'
        },
        {
            xtype: 'tagfield',
            fieldLabel: 'Databases',
            labelWidth: 63,
            valueField: 'TABLE_SCHEMA',
            displayField: 'TABLE_SCHEMA',
            store: {
                proxy: {
                    url: '/index.php/welcome/db_list'
                },
                type: 'personnel'
            }
        },
        {
            iconCls: 'x-fa fa-refresh',
            handler: 'onRefreshBtnClick'
        }
    ],

    store: {
        type: 'personnel'
    },


    listeners: {
        render: 'onTableGridRendered',
        select: 'onItemSelected',
        needLoadData: 'onNeedLoadData'
    }
});
