define(["underscore", "backbone"
], function (_, Backbone) {
    "use strict";

    return {
        conditions: {
            "eq": {name: "Equal", keywords: ["ticket", "comment", "property"], fields: ['property']},
            "created": {name: "Created", keywords: ["ticket", "comment", "entity"], fields: []},
            "hasComments": {name: "Has comment", keywords: ["ticket", "entity"], fields: []},
            "neq": {name: "Not equal", keywords: ["ticket", "comment", "property"], fields: ['property']},
            "contains": {name: "Contains", keywords: ["ticket", "comment", "property"], fields: ['property']}
        },

        targets: {
            "ticket": {
                name: "Ticket",
                "properties": {
                    "subject": "Subject",
                    "status": "Status"
                }
            },
            "comment": {
                name: "Comment",
                "properties": {
                    "text": "comment text"
                }
            }
        },

        actionObject: ["entity", "property"],

        expressions: {
            "OR": {
                name: "ANY"
            },
            "AND": {
                name: "ALL"
            }
        },

        conditionTemplates: [
            {
                "id": "condition-general",
                "depends": {
                    "target": ["ticket", "comment"],
                    "actionObject": ["property"]
                    ,
                    "condition": ["eq", "neq", "contains"]
                }
            },
            {
                "id": "condition-entity",
                "depends": {
                    "target": ["ticket", "comment"],
                    "actionObject": ["entity"]
                    ,
                    "condition": ["created", "hasComments"]
                }
            }
        ]
    }

});
