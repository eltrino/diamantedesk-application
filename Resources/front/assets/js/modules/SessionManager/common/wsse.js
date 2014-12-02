define(['cryptojs.core', 'cryptojs.sha1', 'cryptojs.base64'], function(CryptoJS){

  var Wsse = function() {};

  Wsse.prototype.getNonce = function() {
    var nonce = Math.random().toString(36).substring(2);
    return CryptoJS.enc.Utf8.parse(nonce).toString(CryptoJS.enc.Base64);
  };

  Wsse.prototype.getCreatedDate = function() {
    return new Date().toISOString();
  };


  Wsse.prototype.getPasswordDigest = function(nonce, created, password) {
    var nonce_64 = CryptoJS.enc.Base64.parse(nonce);
    var sha1 = CryptoJS.SHA1(nonce_64
        .concat(CryptoJS.enc.Utf8.parse(created)
        .concat(CryptoJS.enc.Utf8.parse(password))));
    return sha1.toString(CryptoJS.enc.Base64);
  };

  Wsse.prototype.getUsernameToken = function(username, password) {
    var nonce = this.getNonce();
    var created = this.getCreatedDate();
    var passwordDigest = this.getPasswordDigest(nonce, created, password);
    var usernameToken = '';
    usernameToken += 'Username="' + username + '", ';
    usernameToken += 'PasswordDigest="' + passwordDigest + '", ';
    usernameToken += 'Nonce="' + nonce + '", ';
    usernameToken += 'Created="' + created + '"';
    return usernameToken;
  };

  return new Wsse();
});