define(["underscore", "backbone"
], function (_, Backbone) {
    "use strict";

    return {
        data: {
            "id": 1,
            "condition": "eq",
            "property": "status",
            "value": "current",
            "weight": 0,
            "expression": "AND",
            "children": [{
                "id": 2,
                "weight": 0,
                "condition": "eq",
                "property": "status",
                "value": "new",
                "expression": "OR",
                "children": [{
                    "id": 4,
                    "condition": "eq",
                    "property": "status",
                    "value": "new",
                    "weight": 0,
                    "children": [],
                    "target": "ticket",
                    "parent": 2,
                    "active": true
                }, {
                    "id": 5,
                    "condition": "contains",
                    "property": "subject",
                    "value": "Default",
                    "weight": 0,
                    "children": [],
                    "target": "ticket",
                    "parent": 2,
                    "active": true
                }],
                "target": "ticket",
                "parent": 1,
                "active": true
            }, {
                "id": 3,
                "condition": "neq",
                "property": "status",
                "value": "open",
                "weight": 0,
                "children": [],
                "target": "ticket",
                "parent": 1,
                "active": true
            }, {
                "id": 6,
                "condition": "hasComments",
                "weight": 0,
                "children": [],
                "target": "ticket",
                "actionObject": "entity",
                "parent": 1,
                "active": true
            }],
            "target": "ticket",
            "active": true
        },

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
