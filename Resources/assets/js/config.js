define(function(localRequire, exports, module){

  var Config = module.config();
  Config.isDev = (Config.env === 'dev');

  require.config({
    urlArgs: Config.isDev ? "bust=" + (new Date()).getTime() : ''
  });

  return Config;
});
