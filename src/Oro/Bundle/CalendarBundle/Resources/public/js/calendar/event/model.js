define([
    'underscore',
    'backbone',
    'routing',
    'moment'
], function(_, Backbone, routing, moment) {
    'use strict';

    var EventModel;

    /**
     * @export  orocalendar/js/calendar/event/model
     * @class   orocalendar.calendar.event.Model
     * @extends Backbone.Model
     */
    EventModel = Backbone.Model.extend({
        route: 'oro_api_get_calendarevents',
        urlRoot: null,
        originalId: null, // original id received from a server

        defaults: {
            // original id is copied to originalId property and this attribute is replaced with calendarUid + originalId
            id: null,
            title: null,
            description: null,
            start: null,
            end: null,
            allDay: false,
            backgroundColor: null,
            reminders: {},
            parentEventId: null,
            invitationStatus: null,
            invitedUsers: null,
            editable: false,
            removable: false,
            calendarAlias: null,
            calendar: null, // calendarId
            calendarUid: null // calculated automatically, equals to calendarAlias + calendarId
        },

        initialize: function() {
            this.urlRoot = routing.generate(this.route);
            this._updateComputableAttributes();
            this.on('change:id change:calendarAlias change:calendar', this._updateComputableAttributes, this);
        },

        url: function() {
            var url;
            var id = this.id;

            this.id = this.originalId;
            url = Backbone.Model.prototype.url.call(this, arguments);
            this.id = id;

            return url;
        },

        save: function(key, val, options) {
            var attrs;
            var modelData;

            // Handle both `"key", value` and `{key: value}` -style arguments.
            if (key === null || key === undefined || typeof key === 'object') {
                attrs = key || {};
                options = val;
            } else {
                attrs = {};
                attrs[key] = val;
            }

            modelData = _.extend(
                {id: this.originalId},
                _.omit(
                    this.toJSON(),
                    ['id', 'editable', 'removable', 'calendarUid', 'parentEventId', 'invitationStatus']
                ),
                attrs || {}
            );
            modelData.invitedUsers = modelData.invitedUsers ? modelData.invitedUsers.join(',') : undefined;

            options.contentType = 'application/json';
            options.data = JSON.stringify(modelData);

            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        _updateComputableAttributes: function() {
            var calendarAlias = this.get('calendarAlias');
            var calendarId = this.get('calendar');
            var calendarUid = calendarAlias && calendarId ? calendarAlias + '_' + calendarId : null;

            this.set('calendarUid', calendarUid);

            if (!this.originalId && this.id && calendarUid) {
                this.originalId = this.id;
                this.set('id', calendarUid + '_' + this.originalId);
            }
        },

        validate: function(attrs) {
            var errors = [];

            if (moment(attrs.end).diff(attrs.start) < 0) {
                errors.push('oro.calendar.error_message.event_model.end_date_earlier_than_start');
            }

            return errors.length ? errors : null;
        },

        getInvitationStatus: function() {
            var invitationStatus = this.get('invitationStatus');
            var attendees = this.get('attendees');
            if (!invitationStatus && attendees && attendees.length) {
                invitationStatus = 'accepted';
            }
            return invitationStatus;
        }
    });

    return EventModel;
});
