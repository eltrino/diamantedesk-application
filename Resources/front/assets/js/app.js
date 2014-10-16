/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
define(['marionette','backbone'], function(Marionette, Backbone) {

  var App = new Marionette.Application({
    regions : {
      HeaderRegion: '#header',
      MainRegion: '#content',
      FooterRegion: '#footer',
      DialogRegion: '#dialog'
    }
  });


  App.navigate = function(route, options){
    Backbone.history.navigate(route, options || {})
  };

  App.getCurrentRoute = function(){
    return Backbone.history.fragment;
  };



  App.on('before:start', function(){});


  App.on('start', function(){

    require(['modules/Task/routers/task'], function(){

      Backbone.history.start();
      if(App.getCurrentRoute() == ""){
        App.trigger('task:list');
      }

    });

  });


  App.start();

  return App;

});