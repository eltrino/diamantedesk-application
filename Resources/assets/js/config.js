define(function(){

  var dev = window.location.search === '?dev',
      baseurl = config.baseUrl,
      basepath = config.basePath,
      apiurl = config.apiUrl;

  require.config({
    urlArgs: dev ? "bust=" + (new Date()).getTime() : ''
    //"packages": [
    //  {
    //    name: 'PackageName',
    //    location: 'modules/PackageName'
    //  }
    //]
  });

  var Config = {
    isDev : dev,
    baseUrl : baseurl,
    basePath : basepath,
    apiUrl : apiurl
  };

  return Config;
});
