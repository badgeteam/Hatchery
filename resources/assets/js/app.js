/* global require */
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('../../../node_modules/vanilla-picker');
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
	if (document.getElementById('pixels')) {
		let icon;
		let readOnly = true;
		if (document.getElementById('content')) {
			icon = document.getElementById('content');
			readOnly = false;
		} else {
			icon = document.getElementById('content-readonly');
		}
		let data = icon.innerHTML.trim();
		if (data.startsWith('icon = ')) {
			data = data.replace('icon = (', '');
			data = data.replace(')','');
			let numFrames = parseInt(data.match(/[0-9]+?$/)[0]);
			data = data.replace(', '+numFrames, '');
			if (numFrames > 0) {
				let frames = data.split('],');
				frames.forEach(function (frame, index) {
					frame = frame.trim();
					frame = frame.replace('[', '');
					frame = frame.replace(']', '');
					frame = frame.trim();
					frame = frame.split(',');
					frame.forEach(function (pixel, index) {
						frame[index] = pixel.trim();
					});
					frames[index] = frame;
				});
				console.log(frames);
				if (frames.length !== numFrames) {
					console.warn('Data corrupted!');
				} else {
					const framebuffer = [];
					for (let r = 0; r < 8; r++) {
						framebuffer[r] = [];
						for (let p = 0; p < 8; p++) {
							framebuffer[r][p] = document.getElementById('row'+r+'pixel'+p);
						}
					}
					let r = 0, p = 0;
					frames[0].forEach(function(pixel) {
						if (p > 7) {
							r++;
							p = 0;
						}
						if (r > 7) {
							console.warn('Image too big!');
						}
						framebuffer[r][p].style.backgroundColor = pixel.replace('0x', '#');
						p++;
					});
				}
				if (!readOnly) {
					const parentBasic = document.getElementById('colour'),
						popupBasic = new Picker(parentBasic);
					popupBasic.onChange = function(color) {
						parentBasic.style.backgroundColor = color.rgbaString;
					};
					//Open the popup manually:
					popupBasic.openHandler();
				}
			}
		}
	}
};