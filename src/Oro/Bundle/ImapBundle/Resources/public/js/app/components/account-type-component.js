define(function(require) {
    'use strict';

    var AccountTypeComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var accountTypeView = require('oroimap/js/app/views/account-type-view');
    var BaseComponent = require('oroui/js/app/components/base/component');

    AccountTypeComponent = BaseComponent.extend({
        ViewType: accountTypeView,

        /**
         * @constructor
         * @param {Object} options
         */
        initialize: function(options) {
            var config = options.configs || {};
            this.url = _.result(options, 'url') || '';

            var viewConfig = this.prepareViewOptions(options, config);
            this.view = new this.ViewType(viewConfig);
            this.listenTo(this.view, 'imapConnectionChangeType', this.onChangeType);
            this.listenTo(mediator, 'imapGmailConnectionSetToken', this.onIMapGotToken);
        },

        /**
         * Prepares options for the related view
         *
         * @param {Object} options - component's options
         * @param {Object} config - select2's options
         * @return {Object}
         */
        prepareViewOptions: function(options, config) {
            return {
                el: options._sourceElement,
                url: options.url
            };
        },

        onChangeType: function(value) {
            $.ajax({
                url : this.url,
                method: "GET",
                data: {
                    'type': value
                },
                success: _.bind(this.templateLoaded, this)
            });
        },

        onIMapGotToken: function(value) {
            $.ajax({
                url : this.url,
                method: "GET",
                data: {
                    'type': value.type,
                    'token': value.token
                },
                success: _.bind(this.templateLoaded, this)
            });
        },

        templateLoaded: function(response) {
            //debugger;
            this.view.setHtml(response.html).render();
        }
    });

    return AccountTypeComponent;
});
