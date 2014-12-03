define(function(){

  var baseurl = document.querySelector("script[data-main]").src.split('/assets/js')[0],
      apiurl = baseurl.replace('front','web/app_dev.php') + '/api/rest/latest';

  require.config({
    "packages": [
      {
        name: 'SessionManager',
        location: 'modules/SessionManager',
        main: 'module'
      },
      {
        name: 'Header',
        location: 'modules/Header',
        main: 'module'
      },
      {
        name: 'Ticket',
        location: 'modules/Ticket',
        main: 'module'
      }
    ]
  });

  var Config = {
    baseUrl : baseurl,
    apiUrl : apiurl
  };

  return Config;
});
