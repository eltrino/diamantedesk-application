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
require.config({
  paths : {
    "jquery" : "vendor/jquery/dist/jquery",
    "underscore" : "vendor/underscore/underscore",
    "backbone" : "vendor/backbone/backbone",
    "marionette" : "vendor/marionette/lib/backbone.marionette",
    "backbone.paginator" : "vendor/backbone.paginator/lib/backbone.paginator",
    "tpl" : "vendor/requirejs-tpl/tpl",
    "bootstrap" : "vendor/bootstrap/dist/js/bootstrap",
    "cryptojs.core" : "vendor/dfm-crypto-js/components/core",
    "cryptojs.sha1" : "vendor/dfm-crypto-js/components/sha1",
    "cryptojs.base64" : "vendor/dfm-crypto-js/components/enc-base64"
  },
  shim : {
    "jquery" : {
      exports : "jQuery"
    },
    "underscore" : {
      exports : "_"
    },
    "backbone" : {
      deps : ["jquery", "underscore"],
      exports : "Backbone"
    },
    "marionette" : {
      deps : ["backbone"],
      exports : "Marionette"
    },
    "backbone.paginator" : {
      deps : ["backbone"]
    },
    "bootstrap" : {
      deps : ["jquery"]
    },
    "cryptojs.core" : {
      exports: "CryptoJS"
    },
    "cryptojs.sha1": {
      deps: ["cryptojs.core"],
      exports: "CryptoJS.SHA1"
    },
    "cryptojs.base64": {
      deps: ["cryptojs.core"],
      exports: "CryptoJS.enc.Base64"
    }
  },


  "packages": [
    {
      name: 'Common',
      location: 'modules/Common'
    },
    {
      name: 'User',
      location: 'modules/User'
    },
    {
      name: 'SessionManager',
      location: 'modules/SessionManager'
    },
    {
      name: 'Header',
      location: 'modules/Header'
    },
    {
      name: 'Footer',
      location: 'modules/Footer'
    },
    {
      name: 'Ticket',
      location: 'modules/Ticket'
    },
    {
      name: 'Comment',
      location: 'modules/Comment'
    },
    {
      name: 'Attachment',
      location: 'modules/Attachment'
    }
  ],

  deps : ["app"]
});