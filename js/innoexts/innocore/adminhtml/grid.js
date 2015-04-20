/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 *
 * @category    Innoexts
 * @package     Innoexts_InnoCore
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var editableGridForm = Class.create(varienForm, {

    initialize : function($super, grid, formId, elementIdPrefix, elementNames, defaults, validationUrl) {
        $super(formId, validationUrl);
        this.grid = grid;
        this.elementIdPrefix = elementIdPrefix;
        this.elementNames = elementNames;
        this.defaults = defaults;
        this.grid.rowClickCallback = this.rowClick.bind(this);
        this.grid.initRowCallback = this.rowInit.bind(this);
        this.grid.rows.each( function(row) { this.rowInit(this.grid, row); }.bind(this));
    }, 
    rowClick : function(grid, event) { return; }, 
    rowInit : function(grid, row) {
        var select = $(row).down('.action-select');
        if (select) {
            select.writeAttribute('onchange', null);
            select.observe('change', this.actionSelectChange.bind(this));
        }
    }, 
    actionSelectChange: function(event) {
        var select = Event.element(event);
        if (!select.value || !select.value.isJSON()) {
            return;
        }
        var config = select.value.evalJSON();
        if (config.confirm && !window.confirm(config.confirm)) {
            return;
        }     
        if (config.href) {
            if (config.name == 'edit') {
                this.doEdit(config.href);
            } else if (config.name == 'delete') {
                this.doDelete(config.href);
            }    
        }    
        select.options[0].selected = true;
        return;
    }, 
    doDelete : function(url) {
        if (url) {
            new Ajax.Request(url, {
                area : $(this.grid.containerId), 
                onComplete : this.doDeleteComplete.bind(this)
            });
        }
    }, 
    doDeleteComplete : function(transport) {
        var result = transport.responseText.evalJSON();
        if (result.messages) {
            $('messages').update(result.messages);
        }
        if (!result.error) {
            this.grid.reload();
        }
    }, 
    getElementNames : function() {
        return this.elementNames;
    }, 
    getDefaults : function() {
        return this.defaults;
    }, 
    getDefault : function(name) {
        var defaults = this.getDefaults();
        if (defaults[name]) {
            return defaults[name];
        } else {
            return null;
        }
    }, 
    getElementIdPrefix : function() {
        return this.elementIdPrefix;
    }, 
    getElement : function(name) {
        return $(this.getElementIdPrefix() + name);
    }, 
    hasElement : function(name) {
        return (this.getElement(name)) ? true : false;
    }, 
    setValue : function(name, value) {
        if (this.hasElement(name)) {
            this.getElement(name).setValue(value);
        }
    }, 
    setValues : function(values) {
        var self = this;
        this.getElementNames().each(function(elementName) {
            self.setValue(elementName, (
                (values[elementName] && values[elementName] !== null) ? values[elementName] : self.getDefault(elementName)
            ));
        });
    }, 
    doAdd : function() {
        this.setValues(this.getDefaults());
    }, 
    doEdit : function(url) {
        if (url) {
            new Ajax.Request(url, {
                area : $(this.grid.containerId), 
                onComplete : this.doEditComplete.bind(this)
            });
        }
    }, 
    doEditComplete : function(transport) {
        var result = transport.responseText.evalJSON();
        if (result.messages) $('messages').update(result.messages);
        if (result.data) this.setValues(result.data);
    }, 
    _submit : function() {
        if (this.submitUrl) {
            var params = Form.serializeElements($(this.formId).select('input', 'select', 'textarea'), true);
            params.form_key = FORM_KEY;
            $('messages').update();
            new Ajax.Request(this.submitUrl, {
                parameters : params, 
                method : 'post', 
                area : $(this.formId), 
                onComplete : this.doSubmitComplete.bind(this)
            });
        }
    }, 
    doSubmitComplete : function(transport) {
        var result = transport.responseText.evalJSON();
        if (result.messages) $('messages').update(result.messages);
        if (!result.error) this.grid.reload();
    }
});