define(['underscore', 'orotranslation/js/translator', 'xregexp'
    ], function(_, __, XRegExp) {
    'use strict';

    var defaultParam = {
        message: 'This value is not valid.',
        match: true
    };

    /**
     * @export oroform/js/validator/regex
     */
    return [
        'Regex',
        function(value, element, param) {
            var parts, pattern;
            if((/\\p/).test(param.pattern)){
                parts = param.pattern.match(/^\/([\w\W]*?)\/(g?i?m?y?u?)$/);
                pattern = new XRegExp(parts[1].replace(/\\/g,'\\'), parts[2].replace('u',''));
            } else {
                parts = param.pattern.match(/^\/([\w\W]*?)\/(g?i?m?y?)$/);
                pattern = new RegExp(parts[1], parts[2]);
            }
            param = _.extend({}, defaultParam, param);
            return this.optional(element) || Boolean(param.match) === pattern.test(value);
        },
        function(param, element) {
            var value = this.elementValue(element);
            var placeholders = {};
            param = _.extend({}, defaultParam, param);
            placeholders.value = value;
            return __(param.message, placeholders);
        }
    ];
});
