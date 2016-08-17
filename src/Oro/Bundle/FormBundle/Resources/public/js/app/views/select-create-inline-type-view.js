define(function(require) {
    'use strict';

    var SelectCreateInlineTypeView;
    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var routing = require('routing');
    var DialogWidget = require('oro/dialog-widget');
    var BaseView = require('oroui/js/app/views/base/view');
    SelectCreateInlineTypeView = BaseView.extend({
        autoRender: true,
        urlParts: null,
        inputSelector: null,
        select2QueryAdditionalParams: null,
        entityLabel: '',
        existingEntityGridId: null,
        dialogWidget: null,
        keepDialogWidget: false,
        events: {
            'click .entity-select-btn': 'onSelect',
            'click .entity-create-btn': 'onCreate'
        },
        initialize: function(options) {
            SelectCreateInlineTypeView.__super__.initialize.apply(this, arguments);
            _.extend(this,
                _.pick(options, 'urlParts', 'entityLabel', 'existingEntityGridId', 'inputSelector'));
        },

        buildRouteParams: function(routeType) {
            var routeParams = this.urlParts[routeType].parameters;
            return _.extend({}, routeParams, this.$(this.inputSelector).data('select2_query_additional_params'));
        },

        onSelect: function(e) {
            e.preventDefault();
            var routeName = _.result(this.urlParts.grid, 'gridWidgetView') || this.urlParts.grid.route;
            var routeParams = this.buildRouteParams('grid');
            this.dialogWidget = new DialogWidget({
                title: __('Select {{ entity }}', {'entity': this.entityLabel}),
                url: routing.generate(routeName, routeParams),
                stateEnabled: false,
                incrementalPosition: true,
                dialogOptions: {
                    modal: true,
                    allowMaximize: true,
                    width: 1280,
                    height: 650,
                    close: _.bind(this.onDialogClose, this)
                }
            });

            this.dialogWidget.once('grid-row-select', _.bind(this.onGridRowSelect, this));
            this.dialogWidget.render();
        },
        onDialogClose: function() {
            if (!this.keepDialogWidget) {
                this.$(this.inputSelector).off('.' + this.dialogWidget._wid);
                this.dialogWidget.dispose();
                this.dialogWidget = null;
            }
        },
        onGridRowSelect: function(data) {
            var eventNamespace = this.dialogWidget._wid;
            var loadingStarted = false;
            this.keepDialogWidget = true;
            var $input = this.$(this.inputSelector);
            var onSelect = _.bind(function() {
                this.keepDialogWidget = false;
                this.dialogWidget.remove();
                this.$(this.inputSelector).inputWidget('focus');
            }, this);
            this.dialogWidget._showLoading();
            $input.one('select2-data-request.' + eventNamespace, function() {
                loadingStarted = true;
                $(this).one('select2-data-loaded.' + eventNamespace, onSelect);
            });
            $input.inputWidget('val', data.model.get(this.existingEntityGridId), true);
            // if there was no data request sent to server
            if (!loadingStarted) {
                onSelect();
            }
        },
        onCreate: function(e) {
            e.preventDefault();
            var routeName = this.urlParts.create.route;
            var routeParams = this.buildRouteParams('create');
            this.dialogWidget = new DialogWidget({
                title: __('Create {{ entity }}', {'entity': this.entityLabel}),
                url: routing.generate(routeName, routeParams),
                stateEnabled: false,
                incrementalPosition: true,
                dialogOptions: {
                    modal: true,
                    allowMaximize: true,
                    width: 1280,
                    height: 650,
                    close: _.bind(this.onDialogClose, this)
                }
            });

            this.dialogWidget.once('formSave', _.bind(function(id) {
                var $input = this.$(this.inputSelector);
                $input.inputWidget('val', id, true);
                this.keepDialogWidget = false;
                this.dialogWidget.remove();
                this.dialogWidget = null;
                $input.inputWidget('focus');
            }, this));

            this.dialogWidget.render();
        },
        getUrlParts: function() {
            return this.urlParts;
        },
        setUrlParts: function(newParts) {
            this.urlParts = newParts;
        },
        setSelection: function(value) {
            this.$(this.inputSelector).inputWidget('val', value);
        },
        getSelection: function() {
            return this.$(this.inputSelector).inputWidget('val');
        }
    });

    return SelectCreateInlineTypeView;
});
