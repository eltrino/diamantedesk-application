define(function(){
  var Config = {

    modules : {
      SessionManager : 'modules/SessionManager/module',
      Task : 'modules/Task/module',
      Header : 'modules/Header/module'
    },

    baseUrl : (function(){
      return document.querySelector("script[data-main]").src.split('/assets/js')[0];
    })(),

    getModulesUrl : function() {
      return _.values(this.modules);
    },

    loadModules : function(fn) {
      require(this.getModulesUrl(), fn);
    }

  };

  return Config;
});
