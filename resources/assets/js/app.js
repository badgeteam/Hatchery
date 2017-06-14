
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Dropzone = require('../../../node_modules/dropzone/dist/dropzone');

window.onload = function() {
    if (document.getElementById('content')) {
        window.CodeMirror = require(['../../../node_modules/codemirror/lib/codemirror',
            '../../../node_modules/codemirror/mode/python/python'], function (CodeMirror) {
            CodeMirror.fromTextArea(document.getElementById('content'), {
                lineNumbers: true,
                mode: "python"
            });
        });
    }
}