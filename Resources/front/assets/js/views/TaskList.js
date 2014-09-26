define(['marionette', 'views/TaskItem'], function(Marionette, TaskItemView){
  return Marionette.CollectionView.extend({
    tagName: "ul",
    childView: TaskItemView
  });
});