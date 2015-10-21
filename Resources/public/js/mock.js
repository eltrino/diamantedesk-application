define(['underscore', 'backbone'
], function (_, Backbone) {
    'use strict';

    return {
        data: {
            "id": 1,
            "condition": {condition: 'eq', property: 'status', value: 'new'},
            "action": "notifyByEmail[recipients:{admin@mail.com}]",
            "weight": 0,
            "expression": "AND",
            "children": [{
                "id": 2,
                "weight": 0,
                "expression": "OR",
                "children": [{
                    "id": 4,
                    "condition": {name: 'eq', property: 'status', value: 'new'},
                    "weight": 0,
                    "children": [],
                    "target": "Ticket",
                    "parent": 2,
                    "active": true
                }, {
                    "id": 5,
                    "condition": {name: 'contains', property: 'subject', value: 'Default'},
                    "weight": 0,
                    "children": [],
                    "target": "Ticket",
                    "parent": 2,
                    "active": true
                }],
                "target": "Ticket",
                "parent": 1,
                "active": true
            }, {
                "id": 3,
                "condition": {name: 'neq', property: 'status', value: 'open'},
                "weight": 0,
                "children": [],
                "target": "Ticket",
                "parent": 1,
                "active": true
            }],
            "target": "Ticket",
            "active": true
        },
        conditions: {
            'eq': {name: 'Equal'},
            'neq': {name: 'Not equal'},
            'contains': {name: 'Contains'}
        },
        targets: {
            'ticket': {name: 'Ticket'},
            'comment': {name: 'Comment'}
        },
    }

});
