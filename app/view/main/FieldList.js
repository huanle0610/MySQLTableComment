/**
 * This view is an example list of people.
 */
Ext.define('Mtc.view.main.FieldList', {
    extend: 'Ext.grid.Panel',
    xtype: 'mainfieldlist',

    requires: [
        'Mtc.store.Personnel'
    ],

    title: 'Databases & Tables',
    border: true,
    columnLines: true,
    selType: 'checkboxmodel',

    tbar: [
        {
            xtype: 'container',
            layout: 'vbox',
            items: [
                {
                    xtype: 'container',
                    layout: 'hbox',
                    items: [
                        {
                            boxLabel: 'Only Show Favorite',
                            //make checkbox stateful
                            itemId: 'fav',
                            stateful: true,
                            stateId: 'column-fav',
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
                            text: 'Save Checked To Favorite',
                            xtype: 'button',
                            handler: 'saveMarkedFavorite'
                        },
                        {
                            xtype: 'combo',
                            fieldLabel: 'Show Mode',
                            labelWidth: 80,
                            bind: {
                                value: '{show_mode}'
                            },
                            store: [[1, 'By Favorite'], [2, 'By Tables']]
                        },
                        {
                            xtype: 'combo',
                            fieldLabel: 'Favorite group',
                            labelWidth: 94,
                            bind: {
                                disabled: '{show_mode != 1}'
                            },
                            store: ['a', 'b', 'c']
                        },
                        {
                            iconCls: 'x-fa fa-refresh',
                            xtype: 'button',
                            handler: 'onFieldRefreshBtnClick'
                        }
                    ]
                },
                {
                    xtype: 'tagfield',
                    fieldLabel: 'Tables',
                    labelWidth: 48,
                    margin: '5 0 0 0',
                    valueField: 'table',
                    displayField: 'table',
                    minChars: 3,
                    bind: {
                        value: '{selected_tables}',
                        disabled: '{show_mode != 2}'
                    },
                    store: {
                        type: 'personnel'
                    }
                }
            ]
        }
    ],

    store: {
        proxy: {
            url: '/index.php/welcome/field_list'
        },
        type: 'personnel'
    },

    listeners: {
        render: 'onTableGridRendered',
        select: 'onItemSelected',
        needLoadData: 'onNeedLoadData'
    }
});
