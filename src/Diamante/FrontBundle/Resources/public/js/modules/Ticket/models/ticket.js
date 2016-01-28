define([
  'app',
  'config',
  'backbone.paginator'], function(App, Config){

  return App.module("Ticket", function(Ticket, App, Backbone, Marionette, $, _){

    var trim = $.trim,
        PARAM_TRIM_RE = /[\s'"]/g,
        URL_TRIM_RE = /[<>\s'"]/g;

    Ticket.Model = Backbone.Model.extend({
      urlRoot: Config.apiUrl + '/desk/tickets',
      defaults: {
        subject : '',
        description: '',
        priority : 'medium',
        status: 'open',
        source: 'web'
      },
      idAttribute: 'key',

      validate: function(attrs, options){
        var errors = {};
        if(!trim(attrs.subject)) {
          errors.subject = "Can't be blank";
        }
        if(!trim(attrs.description) ||
            !trim(tinymce.get('description').getContent({format:'text'}))) {
          errors.description = "Can't be blank";
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
        pageSize: 10,
        sortKey: 'createdAt',
        order: 1,
        pagePerSet: 5
      },

      queryParams: {
        pageSize: 'limit',
        sortKey: 'sort',
        order: 'order'
      },

      setFilter: function(query) {
        this.params = { subject : query };
      },

      setParams: function(params) {
        if(params.search){
          this.params = { subject : params.search };
        }
        if(params.page){
          this.state.currentPage = params.page;
        }
        if(params.sort){
          this.state.sortKey = params.sort;
        }
        if(params.order){
          this.state.order = (params.order === 'asc') ? -1 : 1;
        }
      },

      getParams: function(){
        var str = [];
        if(this.params && this.params.subject){
          str.push('/search/' + this.params.subject);
        }
        if(this.state.currentPage && this.state.currentPage !== 1){
          str.push('/page/' + this.state.currentPage);
        }
        if(this.state.sortKey){
          str.push('/sort/' + this.state.sortKey);
        }
        if(this.state.order){
          if(this.state.order === 1){
            str.push('/order/desc');
          } else {
            str.push('/order/asc');
          }
        }
        return str.join('');
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
      getTicketCollection: function(params) {
        var tickets = new Ticket.Collection(),
            defer = $.Deferred();
        tickets.setParams(params);
        tickets.fetch({
          data : tickets.params,
          success : function(data){
            defer.resolve(data);
          }
        });
        return defer.promise();
      },
      getTicketModel: function(key) {
        var ticket = new Ticket.Model({ key:key }),
            defer = $.Deferred();
        ticket.fetch({
          success: function(data){
            defer.resolve(data);
          },
          error: function(model, xhr, options){
            defer.reject(model, xhr, options);
          }
        });
        return defer.promise();
      }
    };

    App.reqres.setHandler("ticket:collection", function(params){
      return API.getTicketCollection(params);
    });

    App.reqres.setHandler("ticket:model", function(id){
      return API.getTicketModel(id);
    });

  });

});
