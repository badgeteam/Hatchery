/* global require */
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Dropzone = require('../../../node_modules/dropzone/dist/dropzone');

window.keymap = 'default';

window.onload = function() {
	if (document.getElementById('content')) {
		window.CodeMirror = require([
			'../../../node_modules/codemirror/lib/codemirror',
			'../../../node_modules/codemirror/mode/python/python',
			'../../../node_modules/codemirror/addon/dialog/dialog.js',
			'../../../node_modules/codemirror/addon/search/searchcursor.js',
			'../../../node_modules/codemirror/keymap/vim.js',
			'../../../node_modules/codemirror/keymap/sublime.js',
			'../../../node_modules/codemirror/keymap/emacs.js'
		], function (CodeMirror) {
			CodeMirror.fromTextArea(document.getElementById('content'), {
				lineNumbers: true,
				mode: 'python',
				showCursorWhenSelecting: true,
				indentWithTabs: true,
				keyMap: window.keymap,
			});
		});
		// Enable navigation prompt
		window.onbeforeunload = function() {
			return true;
		};
		document.getElementById('content_form').addEventListener('submit', function() {
			window.onbeforeunload = null;
		});
	}
	if (document.getElementById('content-readonly')) {
		window.CodeMirror = require(['../../../node_modules/codemirror/lib/codemirror',
			'../../../node_modules/codemirror/mode/python/python'], function (CodeMirror) {
			CodeMirror.fromTextArea(document.getElementById('content-readonly'), {
				lineNumbers: true,
				mode: 'python',
				readOnly: true
			});
		});
	}
};