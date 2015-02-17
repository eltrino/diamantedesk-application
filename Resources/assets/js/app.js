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
define([
  'marionette',
  'backbone',
  'config',
  'bootstrap'], function(Marionette, Backbone, Config) {

  var App = new Marionette.Application({

    regions : {
      headerRegion: '#header',
      mainRegion:   '#content',
      footerRegion: '#footer',
      dialogRegion: '#dialog'
    }

  });

  var _navigate = function(route, options){
    Backbone.history.navigate(route, options);
    if(!options.nohistory){
      App.history.push(route);
    }
  };

  App.debug = function(type){
    if(Config.isDev) {
      if(arguments.length > 1){
        console[type].apply(console, [].slice.call(arguments, 1));
      } else {
        console.log.apply(console, arguments);
      }
    }
  };

  App.alert = function(options){
    require(['Common/views/alert'], function(Alert){
      App.dialogRegion.show(new Alert.View(options));
    });
  };

  App.navigate = function(route, options){
    if(Backbone.History.started){
      _navigate(route, options || {});
    } else {
      App.on('history:start', function(){
        _navigate(route, options || {});
      });
    }
  };

  App.on('history:start', function(){
    App.history = [];
    Backbone.history.on("route", function(router, route, param){
      App.history.push(App.getCurrentRoute());
    });
  });

  App.back = function(options){
    var opt = options || {};
    if(!opt.force){
      App.history.pop();
    }
    App.navigate(App.history[App.history.length - 1], { nohistory: true, trigger: true, replace: true });
  };

  App.getCurrentRoute = function(){
    return Backbone.history.fragment;
  };

  App.on('before:start', function(){ });

  App.on('start', function(){
    Backbone.history.start();
    this.trigger('history:start');
  });

  require(['SessionManager','Header', 'Footer', 'Ticket'], function(){
    App.start();
  });

  window.App = App;

  return App;

});