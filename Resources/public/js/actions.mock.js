define(["underscore", "backbone"
], function (_, Backbone) {
    "use strict";

    return {
        data: [
            {
                "action": "Send",
                "property": "email",
                "value": "reporter"
            },
            {
                "action": "Send",
                "property": "twitter",
                "value": "assignee"
            },
            {
                "action": "Change",
                "property": "status",
                "value": "Open"
            }
        ]
    }

});
