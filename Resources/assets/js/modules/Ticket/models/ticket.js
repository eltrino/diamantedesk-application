define([
  'app',
  'config',
  'backbone.paginator'], function(App, Config){

  return App.module("Ticket", function(Ticket, App, Backbone, Marionette, $, _){

    var PARAM_TRIM_RE = /[\s'"]/g,
        URL_TRIM_RE = /[<>\s'"]/g;

    Ticket.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/tickets',
      defaults: {
        subject : '',
        description: '',
        priority : 'medium',
        status: 'open',
        source: 'web',
        branch: Config.branchId
      },
      validate: function(attrs, options){
        var errors = {};
        if(!attrs.subject) {
          errors.subject = "can't be blank";
        }
        if(!attrs.description) {
          errors.description = "can't be blank";
        }
        if(!_.isEmpty(errors)){
          return errors;
        }
      }
    });

    Ticket.Collection = Backbone.PageableCollection.extend({
      url: Config.apiUrl + '/desk/tickets',
      model: Ticket.Model,

      state: {
        pageSize: 5,
        sortKey: 'createdAt',
        order: -1
      },

      queryParams: {
        pageSize: 'limit',
        sortKey: 'sort',
        order: 'order'
      },

      setFilter: function(query) {
        this.params = { subject : query };
      },

      parseState: function(resp, queryParams, state, options){
        return { totalRecords: parseInt(options.xhr.getResponseHeader("X-Total"), 10) };
      },

      parseLinks: function (resp, options){
        var links = {},
            linkHeader = options.xhr.getResponseHeader("Link"),
            relations = ["first", "prev", "next", "last"];
        if (linkHeader) {
          _.each(linkHeader.split(","), function(linkValue){
            var linkParts = linkValue.split(";"),
                url = linkParts[0].replace(URL_TRIM_RE, ''),
                params = linkParts.slice(1);
            _.each(params, function(param){
              var paramParts = param.split("="),
                  key = paramParts[0].replace(PARAM_TRIM_RE, ''),
                  value = paramParts[1].replace(PARAM_TRIM_RE, '');
              if (key == "rel" && _.contains(relations, value)){
                links[value] = url;
              }
            });
          });
        }

        return links;
      }

    });

    var API = {
      getTicketCollection: function(query) {
        var tickets = new Ticket.Collection(),
            defer = $.Deferred();
        if(query){
          tickets.setFilter(query);
        }
        tickets.fetch({
          data : tickets.params,
          success : function(data){
            defer.resolve(data);
          }
        });
        return defer.promise();
      },
      getTicketModel: function(id) {
        var ticket = new Ticket.Model({id:id}),
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

    App.reqres.setHandler("ticket:collection:search", function(query){
      return API.getTicketCollection(query);
    });

    App.reqres.setHandler("ticket:model", function(id){
      return API.getTicketModel(id);
    });

  });

});

