define(["underscore", "backbone"
], function (_, Backbone) {
    "use strict";

    return {
        actions: {
            "send": {
                name: "Send"
            },
            "change": {
                name: "Change"
            }
        },

        properties: {
            "email": {
                name: "Email"
            },
            "status": {
                name: "Status"
            },
            "twitter": {
                name: "Twitter"
            }
        },

        values: {
            "open": {
                name: "Open"
            },
            "reporter": {
                name: "Reporter"
            },
            "assignee": {
                name: "Assignee"
            }
        }
    }

});
