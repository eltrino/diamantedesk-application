define(['marionette', 'tpl!templates/task.ejs'], function(Marionette, taskTemplate) {
  return Marionette.ItemView.extend({
    tagName: "li",
    template: taskTemplate
  });
});