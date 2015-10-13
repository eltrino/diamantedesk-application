define(['underscore', 'backbone'
    ], function(_, Backbone) {
    'use strict';

    return {
        'conditions': {
            'eq': {
                'name': 'Equal',
                'template': 'general',
                'view': '',
                'support': ['ticket', 'comment']
            },
            'contains': {
                'name': 'Contains',
                'template': 'general',
                'view': '',
                'support': ['ticket', 'comment']
            },
            'has_comments': {
                'name': 'Ticket has Comments',
                'template': 'general',
                'view': '',
                'support': ['ticket']
            }
        }
        ,
        'supportTypes': {

        },
        'targets': {
            'ticket': [
                {
                    'option': 'subject',
                    'name': 'Subject'
                },
                {
                    'option': 'source',
                    'name': 'Source'
                }
            ]
            ,
            'comment': [
                {
                    'property': 'text',
                    'name': 'Text'
                },
                {
                    'property': 'private',
                    'name': 'Private'
                }
            ]
        }

    }

});
