/* global require */

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');

window.keymap = 'default';

const framebuffer = [];
let frames;
let currentFrame = 0;
let editor;

window.drawIcon = function () {
	let r = 0, p = 0;
	frames[currentFrame].forEach(function (pixel) {
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
};

window.gotoFrame = function (num) {
	currentFrame = num;
	window.drawIcon();
	for (let child = document.getElementById('frames').firstChild; child !== null; child = child.nextSibling) {
		child.className = 'frames btn btn-default';
	}
	document.getElementById('frame' + num).className = 'frames btn btn-info';
};

window.addFrame = function () {
	const newFrame = frames.length;
	frames[newFrame] = [];
	for (let p = 0; p < 64; p++) {
		frames[newFrame][p] = '0x00000000';
	}
	window.addFrameButton(newFrame);
	window.framesToContent();
};

window.addFrameButton = function (index) {
	const framesDiv = document.getElementById('frames');
	if (index > 0) {
		if (index === 1) {
			let firstFrame = document.createElement('a');
			firstFrame.onclick = function () {
				window.gotoFrame(0); };
			firstFrame.innerText = '1';
			firstFrame.className = 'frames btn btn-info';
			firstFrame.id = 'frame0';
			framesDiv.appendChild(firstFrame);
		}
		let frameButton = document.createElement('a');
		frameButton.onclick = function () {
			window.gotoFrame(index); };
		frameButton.innerText = (index + 1).toString();
		frameButton.className = 'frames btn btn-default';
		frameButton.id = 'frame' + index;
		framesDiv.appendChild(frameButton);
	}
};

window.framesToContent = function () {
	let content = 'icon = (';
	let first = true;
	frames.forEach(function (frame) {
		if (!first) {
			content += ', ';
		} else {
			first = false;
		}
		content += '[';
		let firstPixel = true;
		frame.forEach(function (pixel) {
			if (!firstPixel) {
				content += ', ';
			} else {
				firstPixel = false;
			}
			content += pixel;
		});
		content += ']';
	});
	content += ', ' + frames.length + ')';
	editor.setValue(content);
};

window.pixelToHexA = function (rgba) {
	let remove = 5;
	if (rgba.indexOf('rgba') === -1) {
		remove = 4;
	}
	let sep = rgba.indexOf(',') > -1 ? ',' : ' ';
	rgba = rgba.substr(remove).split(')')[0].split(sep);
	if (rgba.indexOf('/') > -1) {
		rgba.splice(3,1);
	}
	for (let R in rgba) {
		let r = rgba[R];
		if (r.indexOf('%') > -1) {
			let p = r.substr(0,r.length - 1) / 100;
			if (R < 3) {
				rgba[R] = Math.round(p * 255);
			} else {
				rgba[R] = p;
			}
		}
	}
	if (isNaN(rgba[0])) {
		rgba[0] = 0;
	}
	if (isNaN(rgba[1])) {
		rgba[1] = 0;
	}
	if (isNaN(rgba[2])) {
		rgba[2] = 0;
	}
	if (isNaN(rgba[3])) {
		rgba[3] = 1;
	}
	let r = (+rgba[0]).toString(16),
		g = (+rgba[1]).toString(16),
		b = (+rgba[2]).toString(16),
		a = Math.round(+rgba[3] * 255).toString(16);
	if (r.length === 1) {
		r = '0' + r;
	}
	if (g.length === 1) {
		g = '0' + g;
	}
	if (b.length === 1) {
		b = '0' + b;
	}
	if (a.length === 1) {
		a = '0' + a;
	}
	return ('0x' + r + g + b + a);
};

window.lintFile = function () {
	const form = document.getElementById('content_form');
	const lintApi = form.getAttribute('action').replace('files', 'lint-content');
	window.$.post(lintApi, {
		file_content: editor.getValue(),
		_token: window.Laravel.csrfToken
	});
};

window.onload = function () {
	const ext = document.getElementById('extension');
	let langmode = 'python';
	if (ext) {
		if (ext.getAttribute('value') === 'json') {
			langmode = 'javascript';
		} else if (ext.getAttribute('value') === 'v') {
			langmode = 'verilog';
		} else if (ext.getAttribute('value') === 'md' || ext.getAttribute('value') === 'txt') {
			langmode = 'markdown';
		} else if (ext.getAttribute('value') === 'sh') {
			langmode = 'shell';
		}
	}

	[
		'content',
		'commands',
		'constraints'
	].forEach(function (field) {
		if (document.getElementById(field)) {
			window.CodeMirror = require([
				'../../../node_modules/codemirror/lib/codemirror',
				'../../../node_modules/codemirror/mode/python/python',
				'../../../node_modules/codemirror/mode/javascript/javascript',
				'../../../node_modules/codemirror/mode/markdown/markdown',
				'../../../node_modules/codemirror/mode/verilog/verilog',
				'../../../node_modules/codemirror/mode/shell/shell',
				'../../../node_modules/codemirror/addon/dialog/dialog',
				'../../../node_modules/codemirror/addon/search/searchcursor',
				'../../../node_modules/codemirror/keymap/vim',
				'../../../node_modules/codemirror/keymap/sublime',
				'../../../node_modules/codemirror/keymap/emacs'
			], function (CodeMirror) {
				editor = CodeMirror.fromTextArea(document.getElementById(field), {
					lineNumbers: true,
					mode: langmode,
					showCursorWhenSelecting: true,
					indentWithTabs: true,
					keyMap: window.keymap,
					json: true,
					theme: (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'ambiance' : ''
				});
			});
			// Enable navigation prompt
			window.onbeforeunload = function () {
				return true;
			};
			const form = document.getElementById('content_form');
			if (form) {
				form.addEventListener('submit', function () {
					window.onbeforeunload = null;
				});
			}
		}

		if (document.getElementById(field + '-readonly')) {
			window.CodeMirror = require([
				'../../../node_modules/codemirror/lib/codemirror',
				'../../../node_modules/codemirror/mode/python/python',
				'../../../node_modules/codemirror/mode/javascript/javascript',
				'../../../node_modules/codemirror/mode/markdown/markdown',
				'../../../node_modules/codemirror/mode/verilog/verilog',
				'../../../node_modules/codemirror/mode/shell/shell'
			], function (CodeMirror) {
				CodeMirror.fromTextArea(document.getElementById(field + '-readonly'), {
					lineNumbers: true,
					mode: langmode,
					readOnly: true,
					theme: (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'ambiance' : ''
				});
			});
		}
	});
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
			let numFrames = parseInt(data.match(/[0-9]+?$/)[0], 10);
			data = data.replace(', ' + numFrames, '');
			if (numFrames > 0) {
				frames = data.split('],');
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
					currentFrame = index;
					window.addFrameButton(index);
				});
			} else if (data.length === 0) {
				frames = [];
				for (let p = 0; p < 64; p++) {
					frames[currentFrame][p] = '0x00000000';
				}
			}
			currentFrame = 0;
			if (frames.length !== numFrames) {
				console.warn('Data corrupted!');
			} else {
				for (let r = 0; r < 8; r++) {
					framebuffer[r] = [];
					for (let p = 0; p < 8; p++) {
						framebuffer[r][p] = document.getElementById('row' + r + 'pixel' + p);
						if (!readOnly) {
							framebuffer[r][p].onclick = function () {
								this.style.backgroundColor = document.getElementById('colour').style.backgroundColor;
								let pos = this.id.match(/[0-9]+?/g);
								let r = parseInt(pos[0], 10);
								let p = parseInt(pos[1], 10);
								frames[currentFrame][(r * 8) + p] = window.pixelToHexA(this.style.backgroundColor);
								window.framesToContent();
							};
						}
					}
				}
				window.drawIcon();
			}
			if (!readOnly) {
				const parentBasic = document.getElementById('colour'),
					popupBasic = new window.Picker.default(parentBasic);
				popupBasic.onChange = function (color) {
					parentBasic.style.backgroundColor = color.rgbaString;
				};
			}
		}
	}

	const lintButtons = document.getElementsByClassName('lint-button');
	for (let z = 0; z < lintButtons.length; z++) {
		const elem = lintButtons[z];
		elem.onclick = function () {
			window.lintFile();
			return false;
		};
	}

	if (window.UserId) {
		window.Echo.private('App.User.' + window.UserId)
			.listen('ProjectUpdated', (data) => {
				const messages = document.getElementById('messages');
				messages.innerHTML += '<div class="alert alert-' + data.type + ' alert-dismissible">\n' +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>\n' +
                    '<p>' + data.message + '</p>\n' +
                    '</div>';
			});
	}
};
