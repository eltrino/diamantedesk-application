define(function(){

  var baseurl = document.querySelector("script[data-main]").src.split('/assets/js')[0],
      apiurl = baseurl.replace(/\/front$/,'/web/app_dev.php') + '/api/rest/latest';

  require.config({
    //"packages": [
    //  {
    //    name: 'PackageName',
    //    location: 'modules/PackageName',
    //    main: 'module'
    //  }
    //]
  });

  var Config = {
    baseUrl : baseurl,
    apiUrl : apiurl
  };

  return Config;
});
