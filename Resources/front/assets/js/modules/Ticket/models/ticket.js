define([
  'app',
  'config',
  './priority',
  './status'], function(App, Config, Attr){

  return App.module("Ticket", function(Ticket, App, Backbone, Marionette, $, _){

    Ticket.TicketModel = Backbone.Model.extend({
      baseUrl : Config.apiUrl + '/desk/tickets',
      url: function(){
        if(this.isNew()){
          return this.baseUrl + '.json';
        } else {
          return this.baseUrl + '/' + this.id + '.json';
        }

      },
      defaults: {
        subject : '',
        description: '',
        priority : 'medium'
      },
      nestedModels : {
        priority : Attr.PriorityModel,
        status : Attr.StatusModel
      },

      parse: function(response){
        var EmbeddedClass, embeddedData, key;
        for(key in this.nestedModels) {
          if(this.nestedModels.hasOwnProperty(key)){
            EmbeddedClass = this.nestedModels[key];
            embeddedData = response[key];
            response[key] = new EmbeddedClass(embeddedData, {parse:true}).get(key);
          }
        }
        return response;
      },

      toJSON: function(){
        var data = _.clone(this.attributes);
        data.branch_key = data.branch && data.branch.key;
        return data;
      }

    });

    Ticket.TicketCollection = Backbone.Collection.extend({
      url: Config.apiUrl+ '/desk/tickets.json',
      model: Ticket.TicketModel
    });

    var API = {
      getTicketCollection: function() {
        var tickets = new Ticket.TicketCollection(),
            defer = $.Deferred();
        tickets.fetch({
          success: function(data){
            defer.resolve(data);
          }
        });
        return defer.promise();
      },
      getTicketModel: function(id) {
        var ticket = new Ticket.TicketModel({id:id}),
            defer = $.Deferred();
        ticket.fetch({
          success: function(data){
            defer.resolve(data);
          },
          error: function(){
            defer.reject();
          }
        });
        return defer.promise();
      }
    };

    App.reqres.setHandler("ticket:collection", function(){
      return API.getTicketCollection();
    });

    App.reqres.setHandler("ticket:model", function(id){
      return API.getTicketModel(id);
    });

  });

});

