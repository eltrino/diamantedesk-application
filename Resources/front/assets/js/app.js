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
define(['marionette'], function(Marionette) {

  var App = new Marionette.Application({
    regions : {
      header: '#header',
      main: '#content',
      footer: '#footer'
    }
  });

  App.on('before:start', function(){});


  App.on('start', function(){

    require(['modules/Task/controller/list'], function(){
      App.Task.List.Controller.listTasks();
    });

  });


  return App;

});