define(function(localRequire, exports, module){

  var Config = module.config();
  Config.isDev = (Config.env === 'dev');

  require.config({
    urlArgs: Config.isDev ? "bust=" + (new Date()).getTime() : ''
    //"packages": [
    //  {
    //    name: 'PackageName',
    //    location: 'modules/PackageName'
    //  }
    //]
  });

  return Config;
});
