/*! Datatables altEditor 2.0
 */
/**
 * @summary     altEditor
 * @description Lightweight editor for DataTables
 * @version     2.0
 * @file        dataTables.editor.lite.js
 * @author      kingkode (www.kingkode.com)
 * @contact     www.kingkode.com/contact
 * @copyright   Copyright 2016 Kingkode
 *
 * This source file is free software, available under the following license:
 *   MIT license - http://datatables.net/license/mit
 *
 * This source file is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.
 *
 * For details please refer to: http://www.kingkode.com
 */
(function(factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net'], function($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function(root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net')(root, $).$;
            }

            return factory($, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}(function($, window, document, undefined) {
    'use strict';

    var DataTable = $.fn.dataTable;


    var _instance = 0;

    /** 
     * altEditor provides modal editing of records for Datatables
     *
     * @class altEditor
     * @constructor
     * @param {object} oTD DataTables settings object
     * @param {object} oConfig Configuration object for altEditor
     */
    var altEditor = function(dt, opts) {
        if (!DataTable.versionCheck || !DataTable.versionCheck('1.10.8')) {
            throw ("Warning: altEditor requires DataTables 1.10.8 or greater");
        }

        // User and defaults configuration object
        this.c = $.extend(true, {},
            DataTable.defaults.altEditor,
            altEditor.defaults,
            opts
        );

        /**
         * @namespace Settings object which contains customisable information for altEditor instance
         */
        this.s = {
            /** @type {DataTable.Api} DataTables' API instance */
            dt: new DataTable.Api(dt),

            /** @type {String} Unique namespace for events attached to the document */
            namespace: '.altEditor' + (_instance++)
        };


        /**
         * @namespace Common and useful DOM elements for the class instance
         */
        this.dom = {
            /** @type {jQuery} altEditor handle */
            modal: $('<div class="dt-altEditor-handle"/>'),
        };


        /* Constructor logic */
        this._constructor();
    }



    $.extend(altEditor.prototype, {
        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Constructor
         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

        /**
         * Initialise the RowReorder instance
         *
         * @private
         */
        _constructor: function() {
            var that = this;
            var dt = this.s.dt;

            this._setup();

            dt.on('destroy.altEditor', function() {
                dt.off('.altEditor');
                $(dt.table().body()).off(that.s.namespace);
                $(document.body).off(that.s.namespace);
            });
        },

        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Private methods
         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

        /**
         * Setup dom and bind button actions
         *
         * @private
         */
        _setup: function() {

            var that = this;
            var dt = this.s.dt;

            // add modal
            $('body').append([
                '<div class="modal fade" id="altEditor-modal" tabindex="-1" role="dialog">',
                    '<div class="modal-dialog">',
                        '<div class="modal-content">',
                            '<div class="modal-header altEditor-header">',
                                '<button type="button" class="close" data-dismiss="modal" aria-label="Close">',
                                    '<span aria-hidden="true">&times;</span>',
                                '</button>',
                                
                                '<h4 class="modal-title"></h4>',
                            '</div>',

                            '<div class="modal-body altEditor-body">',
                                '<p></p>',
                            '</div>',

                            '<div class="modal-footer altEditor-footer">',
                                '<button type="button" class="btn btn-sm btn-raised btn-danger">Save changes</button>',
                                '<button type="button" class="btn btn-sm btn-raised btn-secondary" data-dismiss="modal">Close</button>',
                            '</div>',
                        '</div>',
                    '</div>',
                '</div>',
            ].join("\n"));


            // add Edit Button
            if (this.s.dt.button('edit:name')) {
                this.s.dt.button('edit:name').action(function(e, dt, node, config) {
                    var rows = dt.rows({
                        selected: true
                    }).count();

                    that._openEditModal();
                });

                $(document).on('click', '#editRowBtn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    that._editRowData();
                });

            }

            // add Delete Button
            if (this.s.dt.button('delete:name')) {
                this.s.dt.button('delete:name').action(function(e, dt, node, config) {
                    var rows = dt.rows({
                        selected: true
                    }).count();

                    that._openDeleteModal();
                });

                $(document).on('click', '#deleteRowBtn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    that._deleteRow();
                });
            }

            // add Add Button
            if (this.s.dt.button('add:name')) {
                this.s.dt.button('add:name').action(function(e, dt, node, config) {
                    var rows = dt.rows({
                        selected: true
                    }).count();

                    that._openAddModal();
                });

                $(document).on('click', '#addRowBtn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    that._addRowData();
                });
            }

        },

        /**
         * Emit an event on the DataTable for listeners
         *
         * @param  {string} name Event name
         * @param  {array} args Event arguments
         * @private
         */
        _emitEvent: function(name, args) {
            this.s.dt.iterator('table', function(ctx, i) {
                $(ctx.nTable).triggerHandler(name + '.dt', args);
            });
        },

        /**
         * Open Edit Modal for selected row
         * 
         * @private
         */
        _openEditModal: function() {

            var that = this;
            var dt = this.s.dt;


            // Get the only editable rows 
            // to create the form
            var columnDefs = [];

            for (var i = 0; i < dt.context[0].aoColumns.length; i++) {

                var config = {
                    title: dt.context[0].aoColumns[i].sTitle
                };

                if (typeof dt.context[0].aoColumns[i].className != 'undefined') {
                    config.editable = true;
                } else {
                    config.editable = false;
                }

                columnDefs[i] = config;
            }


            // Get selected row
            var adata = dt.rows({ selected: true });


            // Generate CRUD form
            var data = "";

            data += '<form name="altEditor-form" class="altEditor-form" role="form" autocomplete="off">';
            for (var j in columnDefs) {

                var is_hidden = columnDefs[j].editable ? '' : ' hidden',
                    label_title = columnDefs[j].title;

                var input_id = label_title.toLowerCase().replace(' ', '_');

                console.log(adata.data()[0][j]);

                data += '<div class="form-group altEditor-form-group' + is_hidden + '">';
                    data += '<div class="col-sm-3 col-md-3 col-lg-3" style="padding-top: 10px;">';
                        data += '<label for="' + input_id + '" class="altEditor-label">';
                            data += label_title + ': ';
                        data += '</label>';
                    data += '</div>';

                    data += '<div class="col-sm-9 col-md-9 col-lg-9">';
                        data += '<input type="text" id="' + input_id + '" name="' + label_title + '" placeholder="' + label_title + '" autocomplete="off" style="overflow: hidden;" class="form-control form-control-xs" value="' + adata.data()[0][j] + '">';
                    data += '</div>';

                    data += '<div style="clear:both;"></div>';
                data += '</div>';
            }
            data += "</form>";


            $('#altEditor-modal').on('show.bs.modal', function() {
                $('#altEditor-modal').find('.modal-title').html('Update Record');
                $('#altEditor-modal').find('.modal-body').html(data);
                $('#altEditor-modal').find('.modal-footer').html([
                    '<button type="button" data-content="remove" class="btn btn-sm btn-raised btn-danger" id="editRowBtn">Save Changes</button>',
                    '<button type="button" data-content="remove" class="btn btn-sm btn-raised btn-secondary" data-dismiss="modal">Close</button>'
                ].join("\n"));
            });

            $('#altEditor-modal').modal('show');
            $('#altEditor-modal input[0]').focus();

        },

        _editRowData: function() {

            var that = this;
            var dt = this.s.dt;


            // Fetch affected form data
            var data = [],
                save = {};
            $('form[name="altEditor-form"] input, select').each(function(i) {
                
                var dom = $(this),
                    dom_value = (dom.val() == 'null') ? '' : dom.val(),
                    dom_txtval = (dom.prop('type') === 'select-one') ? dom.find(':selected').text() : dom_value;

                var dom_id = dom.attr('id');

                if (typeof dom_id != 'undefined') {
                    data.push(dom_txtval);
                    save[dom.attr('id')] = dom_value;
                }
            });


            $(dt.context[0].nTable).trigger('savedata', [
                'edit', 
                save, 
                function (xhr_response) {

                    $('#altEditor-modal .modal-body .alert').remove();

                    var message = [
                      '<div id="datatable_success" class="alert alert-success" role="alert">',
                        '<strong>Success!</strong> This record has been updated.',
                      '</div>'
                    ].join('\n');

                    $('#altEditor-modal .modal-body').prepend(message);

                    $('#editRowBtn').remove();


                    // Check if the data has been successfully
                    // stored and use the given ID if provided
                    // to create a new row.
                    if (typeof xhr_response != 'undefined') {

                        // Supply the correct avatar url if provided
                        if (xhr_response.hasOwnProperty('avatar') && xhr_response.avatar) {
                            var idx = 0;
                            for (var col in save) {
                                if (col === 'avatar') { break; }
                                idx++;
                            }
                            data[idx] = xhr_response.avatar;
                        }
                    }

                    dt.row({
                        selected: true
                    }).data(data);
                },
                function (xhr_response) {

                    var message = [];

                    message.push('<div id="datatable_error" class="alert alert-danger" role="alert">');

                    if (typeof xhr_response == 'object' && xhr_response.hasOwnProperty('status') && xhr_response.hasOwnProperty('message')) {

                        message.push('<strong>Ooops!</strong> ' + xhr_response.message);

                    } else {

                        message.push('<strong>Ooops!</strong> Failed to proceed with the operation.');
                    }

                    message.push('</div>');


                    $('#altEditor-modal .modal-body .alert').remove();
                    $('#altEditor-modal .modal-body').prepend(message.join('\n'));
                }
            ]);
        },


        /**
         * Open Delete Modal for selected row
         *
         * @private
         */
        _openDeleteModal: function() {

            var that = this;
            var dt = this.s.dt;


            // Get the only editable rows 
            // to create the form
            var columnDefs = [];

            for (var i = 0; i < dt.context[0].aoColumns.length; i++) {

                var config = {
                    title: dt.context[0].aoColumns[i].sTitle
                };

                if (typeof dt.context[0].aoColumns[i].className != 'undefined') {
                    config.editable = true;
                } else {
                    config.editable = false;
                }

                columnDefs[i] = config;
            }

            // Get selected row
            var adata = dt.rows({ selected: true });


            // Generate CRUD form
            var data = "";

            data += '<form name="altEditor-form" class="altEditor-form" role="form" autocomplete="off">';
            for (var i in columnDefs) {

                var is_hidden = columnDefs[i].editable ? '' : ' hidden',
                    label_title = columnDefs[i].title;

                data += '<div class="form-group altEditor-form-group' + is_hidden + '">';
                    data += '<div class="col-sm-3 col-md-3 col-lg-3" style="padding-top: 7px;">';
                        data += '<label class="altEditor-label">';
                            data += label_title + ': ';
                        data += '</label>';
                    data += '</div>';

                    data += '<div class="col-sm-9 col-md-9 col-lg-9" style="padding-top: 7px;">';
                        data += '<p id="' + label_title + '" style="overflow: hidden;" class="altEditor-input-fd">' + adata.data()[0][i] + '</p>';
                    data += '</div>';

                    data += '<div style="clear:both;"></div>';
                data += '</div>';
            }
            data += "</form>";


            $('#altEditor-modal').on('show.bs.modal', function() {
                $('#altEditor-modal').find('.modal-title').html('Delete Record');
                $('#altEditor-modal').find('.modal-body').html(data);
                $('#altEditor-modal').find('.modal-footer').html([
                    '<button type="button" data-content="remove" class="btn btn-sm btn-raised btn-danger" id="deleteRowBtn">Delete</button>',
                    '<button type="button" data-content="remove" class="btn btn-sm btn-raised btn-secondary" data-dismiss="modal">Close</button>'
                ].join("\n"));
            });

            $('#altEditor-modal').modal('show');
            $('#altEditor-modal input[0]').focus();

        },

        _deleteRow: function() {

            var that = this;
            var dt = this.s.dt;


            // Fetch affected form data
            var data = {};
            $('form[name="altEditor-form"] .altEditor-input-fd').each(function(i) {
                var dom = $(this);
                data[dom.attr('id')] = dom.html();
            });


            $(dt.context[0].nTable).trigger('savedata', [
                'delete',
                data,
                function () {

                    $('#altEditor-modal .modal-body .alert').remove();

                    var message = [
                      '<div id="datatable_success" class="alert alert-success" role="alert">',
                        '<strong>Success!</strong> This record has been deleted.',
                      '</div>'
                    ].join('\n');

                    $('#altEditor-modal .modal-body').prepend(message);

                    $('#deleteRowBtn').remove();

                    dt.row({
                        selected: true
                    }).remove();

                    dt.draw();
                },
                function (xhr_response) {

                    var message = [];

                    message.push('<div id="datatable_error" class="alert alert-danger" role="alert">');

                    if (typeof xhr_response == 'object' && xhr_response.hasOwnProperty('status') && xhr_response.hasOwnProperty('message')) {

                        message.push('<strong>Ooops!</strong> ' + xhr_response.message);

                    } else {

                        message.push('<strong>Ooops!</strong> Failed to proceed with the operation.');
                    }

                    message.push('</div>');


                    $('#altEditor-modal .modal-body .alert').remove();
                    $('#altEditor-modal .modal-body').prepend(message.join('\n'));
                }
            ]);
        },


        /**
         * Open Add Modal for selected row
         * 
         * @private
         */
        _openAddModal: function() {

            var that = this;
            var dt = this.s.dt;


            // Get the only editable rows 
            // to create the form
            var columnDefs = [];

            for (var i = 0; i < dt.context[0].aoColumns.length; i++) {

                var config = {
                    title: dt.context[0].aoColumns[i].sTitle
                };

                if (typeof dt.context[0].aoColumns[i].className != 'undefined') {
                    config.editable = true;
                } else {
                    config.editable = false;
                }

                columnDefs[i] = config;
            }


            // Generate CRUD form
            var data = "";

            data += '<form name="altEditor-form" class="altEditor-form" role="form" autocomplete="off">';
            for (var j in columnDefs) {

                var is_hidden = columnDefs[j].editable ? '' : ' hidden',
                    label_title = columnDefs[j].title;

                var input_id = label_title.toLowerCase().replace(' ', '_');

                data += '<div class="form-group altEditor-form-group' + is_hidden + '">';
                    data += '<div class="col-sm-3 col-md-3 col-lg-3" style="padding-top: 10px;">';
                        data += '<label for="' + input_id + '" class="altEditor-label">';
                            data += label_title + ': ';
                        data += '</label>';
                    data += '</div>';

                    data += '<div class="col-sm-9 col-md-9 col-lg-9">';
                        data += '<input type="text" id="' + input_id + '" name="' + label_title + '" autocomplete="off" style="overflow: hidden;" class="form-control form-control-xs" value="">';
                    data += '</div>';

                    data += '<div style="clear:both;"></div>';
                data += '</div>';
            }
            data += "</form>";


            $('#altEditor-modal').on('show.bs.modal', function() {
                $('#altEditor-modal').find('.modal-title').html('New Record');
                $('#altEditor-modal').find('.modal-body').html(data);
                $('#altEditor-modal').find('.modal-footer').html([
                    '<button type="button" data-content="remove" class="btn btn-sm btn-raised btn-danger" id="addRowBtn">Add Record</button>',
                    '<button type="button" data-content="remove" class="btn btn-sm btn-raised btn-secondary" data-dismiss="modal">Close</button>'
                ].join("\n"));
            });

            $('#altEditor-modal').modal('show');
            $('#altEditor-modal input[0]').focus();
        },

        _addRowData: function() {
            
            var that = this;
            var dt = this.s.dt;


            // Fetch affected form data
            var data = [],
                save = {};
            $('form[name="altEditor-form"] input, select').each(function(i) {
                
                var dom = $(this),
                    dom_value = (dom.val() == 'null') ? '' : dom.val(),
                    dom_txtval = (dom.prop('type') === 'select-one') ? dom.find(':selected').text() : dom_value;
                
                var dom_id = dom.attr('id');

                if (typeof dom_id != 'undefined') {
                    data.push(dom_txtval);
                    save[dom.attr('id')] = dom_value;
                }
            });

            $(dt.context[0].nTable).trigger('savedata', [
                'add',
                save,
                function (xhr_response) {

                    $('#altEditor-modal .modal-body .alert').remove();

                    var message = [
                      '<div id="datatable_success" class="alert alert-success" role="alert">',
                        '<strong>Success!</strong> This record has been added.',
                      '</div>'
                    ].join('\n');

                    $('#altEditor-modal .modal-body').prepend(message);

                    $('#addRowBtn').remove();


                    // Check if the data has been successfully
                    // stored and use the given ID if provided
                    // to create a new row.
                    if (typeof xhr_response != 'undefined') {

                        // The first element of data array
                        // is expected to be the primary key
                        // of the datagrid row.
                        if (xhr_response.hasOwnProperty('id')) {
                            data[0] = xhr_response.id;
                        }

                        // Supply the correct avatar url if provided
                        if (xhr_response.hasOwnProperty('avatar') && xhr_response.avatar) {
                            var idx = 0;
                            for (var col in save) {
                                if (col === 'avatar') { break; }
                                idx++;
                            }
                            data[idx] = xhr_response.avatar;
                        }
                    }

                    dt.row.add(data).draw(false);
                },
                function (xhr_response) {

                    var message = [];

                    message.push('<div id="datatable_error" class="alert alert-danger" role="alert">');

                    if (typeof xhr_response == 'object' && xhr_response.hasOwnProperty('status') && xhr_response.hasOwnProperty('message')) {

                        message.push('<strong>Ooops!</strong> ' + xhr_response.message);

                    } else {

                        message.push('<strong>Ooops!</strong> Failed to proceed with the operation.');
                    }

                    message.push('</div>');


                    $('#altEditor-modal .modal-body .alert').remove();
                    $('#altEditor-modal .modal-body').prepend(message.join('\n'));
                }
            ]);
        },

        _getExecutionLocationFolder: function() {
            var fileName = "dataTables.altEditor.js";
            var scriptList = $("script[src]");
            var jsFileObject = $.grep(scriptList, function(el) {

                if (el.src.indexOf(fileName) !== -1) {
                    return el;
                }
            });
            var jsFilePath = jsFileObject[0].src;
            var jsFileDirectory = jsFilePath.substring(0, jsFilePath.lastIndexOf("/") + 1);
            return jsFileDirectory;
        }
    });



    /**
     * altEditor version
     * 
     * @static
     * @type      String
     */
    altEditor.version = '1.0';


    /**
     * altEditor defaults
     * 
     * @namespace
     */
    altEditor.defaults = {
        /** @type {Boolean} Ask user what they want to do, even for a single option */
        alwaysAsk: false,

        /** @type {string|null} What will trigger a focus */
        focus: null, // focus, click, hover

        /** @type {column-selector} Columns to provide auto fill for */
        columns: '', // all

        /** @type {boolean|null} Update the cells after a drag */
        update: null, // false is editor given, true otherwise

        /** @type {DataTable.Editor} Editor instance for automatic submission */
        editor: null
    };


    /**
     * Classes used by altEditor that are configurable
     * 
     * @namespace
     */
    altEditor.classes = {
        /** @type {String} Class used by the selection button */
        btn: 'btn'
    };


    // Attach a listener to the document which listens for DataTables initialisation
    // events so we can automatically initialise
    $(document).on('preInit.dt.altEditor', function(e, settings, json) {
        if (e.namespace !== 'dt') {
            return;
        }

        var init = settings.oInit.altEditor;
        var defaults = DataTable.defaults.altEditor;

        if (init || defaults) {
            var opts = $.extend({}, init, defaults);

            if (init !== false) {
                new altEditor(settings, opts);
            }
        }
    });


    // Alias for access
    DataTable.altEditor = altEditor;

    return altEditor;
}));