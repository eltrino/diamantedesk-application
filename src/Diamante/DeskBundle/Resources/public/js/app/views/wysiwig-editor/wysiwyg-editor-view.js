define(function(require) {
    'use strict';

    var WysiwygEditorView = require('oroform/js/app/views/wysiwig-editor/wysiwyg-editor-view');
    var LoadingMask = require('oroui/js/app/views/loading-mask-view');

    var DiamanteWysiwygEditorView = WysiwygEditorView.extend({

        connectTinyMCE: function() {
            var self = this;
            var loadingMaskContainer = this.$el.parents('.ui-dialog');
            if (!loadingMaskContainer.length) {
                loadingMaskContainer = this.$el.parent();
            }
            this.subview('loadingMask', new LoadingMask({
                container: loadingMaskContainer
            }));
            this.subview('loadingMask').show();
            if (!this.firstRender) {
                if (this.htmlValue && this.$el.val() === this.strippedValue) {
                    // if content is not modified, return html representation back
                    this.$el.val(this.htmlValue);
                } else {
                    this.$el.val(txtHtmlTransformer.text2html(this.$el.val()));
                }
            }
            this._deferredRender();
            var options = this.options;
            if ($(this.$el).prop('disabled')) {
                options.readonly = true;
            }
            this.$el.tinymce(_.extend({
                'init_instance_callback': function(editor) {
                    /**
                     * fix of https://magecore.atlassian.net/browse/BAP-7130
                     * "WYSWING editor does not work with IE"
                     * Please check if it's still required after tinyMCE update
                     *
                     * REALLY???
                     */
                    setTimeout(function() {
                        var focusedElement = document.activeElement;
                        editor.focus();
                        focusedElement.focus();
                        focusedElement.scrollTop = 0;
                    }, 500);

                    self.removeSubview('loadingMask');
                    self.tinymceInstance = editor;
                    _.defer(function() {
                        /**
                         * fixes jumping dialog on refresh page
                         * (promise should be resolved in a separate process)
                         */
                        self._resolveDeferredRender();
                    });
                }
            }, options));
            this.tinymceConnected = true;
        }
    });

    return DiamanteWysiwygEditorView;
});
