define(function(){

  var dev = window.location.search === '?dev',
      baseurl = document.querySelector("script[data-main]").src.split('/assets/js')[0],
      apiurl = dev ? baseurl.replace(/\/front$/,'/web/app_dev.php') : baseurl.replace(/\/front$/,'/web');

  baseurl += dev ? '?dev' : '';
  apiurl += '/api/rest/latest';

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
    apiUrl : apiurl
  };

  return Config;
});
