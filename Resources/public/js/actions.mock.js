define(["underscore", "backbone"
], function (_, Backbone) {
    "use strict";

    return {
        actions: {
            "notifyByEmail": {
                name: "Send"
            }
        },

        properties: {
            "recipients": {
                name: "Email"
            }
        },

        values: {
            "admin@mail.com": {
                name: "Admin"
            },
            "kolomiets@mail.com": {
                name: "Alex Kolomiets"
            }
        }
    }

});
