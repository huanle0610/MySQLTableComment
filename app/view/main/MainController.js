/**
 * This class is the controller for the main view for the application. It is specified as
 * the "controller" of the Main view class.
 *
 * TODO - Replace this content of this view to suite the needs of your application.
 */
Ext.define('Mtc.view.main.MainController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.main',

    init: function () {
        this.GlobaStore = new Ext.util.LocalStorage({
            id: 'g'
        });
    },


    onItemSelected: function (sender, record) {
        // Ext.Msg.confirm('Confirm', 'Are you sure?', 'onConfirm', this);
    },

    getStoreUniqueKey: function (store) {
        return store.getProxy().reader.metaData.unique_key;
    },

    onTableGridRendered: function (grid) {
        var st = grid.getStore();
        st.on({
            metachange: function(store, meta) {
                grid.reconfigure(store, meta.columns);
                store.unique_key = meta.unique_key;
            }
        });
        grid.fireEvent('needLoadData', grid);
    },

    onRefreshBtnClick: function (btn) {
        var grid = btn.up('grid');
        grid.fireEvent('needLoadData', grid);
    },

    onNeedLoadData: function (grid) {
        var fav = grid.down('#fav');
        var fav_before_load = fav.checked;
        fav.setValue(false);
        grid.getStore().reload({
            callback: function () {
                Ext.defer(function () {
                    fav.setValue(fav_before_load);
                }, 500);
            }
        });
    },

    onFieldRefreshBtnClick: function (btn) {
        var me = this;

        var grid = btn.up('grid');

        var selected_tables = me.getViewModel().get('selected_tables');
        var st = grid.getStore();
        Ext.apply(st.getProxy().extraParams, {tables: Ext.encode(selected_tables)});
        grid.fireEvent('needLoadData', grid);
    },

    showTableDetail: function (view, rowIndex, colIndex) {
        var rec = view.getStore().getAt(rowIndex);
        alert("Edit " + rec.get('TABLE_SCHEMA')+ rec.get('TABLE_NAME'));
    },

    editTableComment: function (view, rowIndex, colIndex) {
        var me = this;
        var rec = view.getStore().getAt(rowIndex);
        var key = view.getStore().unique_key;
        var comment = 'TABLE_COMMENT';
        me.showEditWindow(key, comment, rec, view.up('grid').reference);

    },

    editCollection:  function (view, rowIndex, colIndex, item, evt, rec) {
        var me = this;
        var key = view.getStore().unique_key;

        var sKey = view.up('grid').reference + me.getKeyTitle(key, rec);
        if(me.GlobaStore.getItem(sKey)) {
            me.GlobaStore.removeItem(sKey);
        } else {
            me.GlobaStore.setItem(sKey, true);
        }

        rec.set('fav', me.GlobaStore.getItem(sKey));
    },

    getCollectionCls: function(v, meta, rec, rowIdx, colIdx, store, view) {
        var me = this;
        var key = store.unique_key;
        if(!key) {
            key = me.getStoreUniqueKey(store);
        }
        var sKey = view.up('grid').reference + me.getKeyTitle(key, rec);
        var stored = me.GlobaStore.getItem(sKey);
        rec.set('fav', stored);

        console.log(sKey, store.unique_key, stored);

        if (stored) {
            return 'x-fa fa-star';
        } else {
            return 'x-fa fa-star-o';
        }

    },

    showFavChange: function (checkbox, newV) {
        var st = checkbox.up('grid').getStore();
        if(checkbox.checked) {
            st.addFilter({
                property: 'fav',
                id: 'fav',
                value: true
            });
        } else {
            st.removeFilter('fav');
        }
    },

    editColumnComment: function (view, rowIndex, colIndex) {
        var me = this;
        var rec = view.getStore().getAt(rowIndex);
        var key = view.getStore().unique_key;

        var comment = 'COLUMN_COMMENT';
        me.showEditWindow(key, comment, rec, view.up('grid').reference);
    },


    getKeyTitle: function (key, rec) {
        var title = '';
        if(Ext.isArray(key)) {
            key.forEach(function (item) {
                title += '-' + rec.get(item);
            });
        } else {
            title = rec.get(key);
        }

        if(title) {
            title = title.replace(/^\-/, '');
        }

        return title;
    },

    getKeyFields: function (key, rec) {
        var fields = [];
        if(Ext.isArray(key)) {
            key.forEach(function (item) {
                fields.push({
                    xtype: 'hidden',
                    value: rec.get(item),
                    name: item
                });
            });
        } else {
            fields.push({
                xtype: 'hidden',
                value: rec.get(key),
                name: key
            });
        }

        return fields;
    },

    showEditWindow: function (key, comment, rec, type) {
        var me = this;
        var key_title = me.getKeyTitle(key, rec);
        var key_fields = me.getKeyFields(key, rec);
        var comment_text = me.getKeyTitle(comment, rec);

        var formItems = [
            {
                xtype: 'hidden',
                name: 'type',
                value: type
            },
            {
                xtype: 'textarea',
                fieldLabel: 'Comment',
                name: 'comment',
                width: '100%',
                value: comment_text
            }
        ];

        formItems.push.apply(formItems, key_fields);

        var win = me.getView().add({
            xtype: 'window',
            width: 650,
            height: 220,
            modal: true,
            title: "Edit " + type + " Comment For: " + key_title,
            items: {
                xtype: 'form',
                changeType: type,
                items: formItems,
                buttons: [
                    '->',
                    {
                        text: 'Save',
                        handler: 'saveComment'
                    }
                ]
            }
        }).show();
    },

    saveComment: function (btn) {
        var me = this,
            frm = btn.up('form');
        frm.submit({
            url: '/index.php/welcome/saveComment',
            success: function () {
                console.log(arguments);
                me.getReferences()[frm.changeType].getStore().reload();
                btn.up('window').close();
            },
            failure: function () {
                console.log(arguments);
                Ext.Msg.alert('Error', 'Some errors occured');
            }
        });
    },

    onConfirm: function (choice) {
        if (choice === 'yes') {
            //
        }
    }
});
