
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Dropzone = require('../../../node_modules/dropzone/dist/dropzone.js');

window.CodeMirror = require(['../../../node_modules/codemirror/lib/codemirror.js',
    '../../../node_modules/codemirror/mode/python/python.js']);
