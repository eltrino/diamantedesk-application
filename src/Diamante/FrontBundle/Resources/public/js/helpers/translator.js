define(['polyglot','translations', 'underscore'], function(Polyglot, translations, _){

  var polyglot,
      messages = {};

  _.each(translations.messages, function (value, key) {
    if(key.indexOf('jsmessages:') !== -1){
      messages[key.replace('jsmessages:', '')] = value;
    }
  });

  console.log(translations.locale);
  console.log(messages);

  polyglot = new Polyglot( { phrases: messages });

  window.__ = function(key, options){
    return polyglot.t(key, options);
  };

});