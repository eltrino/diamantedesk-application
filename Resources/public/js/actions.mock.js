define(["underscore", "backbone"
], function (_, Backbone) {
    "use strict";

    return {
        "types": {
            "NotifyByEmail": {
                "name": "Send"
            },
            "UpdateProperty": {
                "name": "Update"
            }
        },

        "addressees": {
            "admin@mail.com": {
                "name": "Admin"
            },
            "kolomiets@eltrino.com": {
                "name": "Alex Kolomiets"
            },
            "assignee": {
                "name": "Assignee"
            },
            "reporter": {
                "name": "Reporter"
            }
        },

        "notifications": {
            "email": {
                "name": "Email"
            },
            "twitter": {
                "name": "Twitter"
            },
            "sms": {
                "name": "Sms"
            }
        },

        "targets": {
            "ticket": {
                "name": "Ticket",
                "properties": {
                    "subject": "Subject",
                    "status": "Status"
                }
            },
            "comment": {
                "name": "Comment",
                "properties": {
                    "text": "comment text"
                }
            }
        },

        actionTemplates: [
            {
                "id": "action-general",
                "depends": {
                    "type": ["NotifyByEmail"]
                }
            },
            {
                "id": "action-update",
                "depends": {
                    "type": ["UpdateProperty"]
                }
            }
        ]
    }

});
