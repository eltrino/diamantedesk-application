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
define(['marionette','backbone','config','bootstrap'], function(Marionette, Backbone) {

  var App = new Marionette.Application({

    regions : {
      HeaderRegion: '#header',
      MainRegion:   '#content',
      FooterRegion: '#footer',
      DialogRegion: '#dialog'
    }

  });

  App.navigate = function(route, options){
    if(Backbone.History.started){
      Backbone.history.navigate(route, options || {});
    } else {
      this.on('history:start', function(){
        Backbone.history.navigate(route, options || {});
      });
    }
  };

  App.getCurrentRoute = function(){
    return Backbone.history.fragment;
  };

  App.on('before:start', function(){ });

  App.on('start', function(){
    Backbone.history.start();
    this.trigger('history:start');
  });

  require(['SessionManager','Header', 'Task'], function(){
    App.start();
  });

  window.App = App;

  return App;

});