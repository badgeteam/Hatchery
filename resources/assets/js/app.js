// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

/**
 * First we will load all of this project's JavaScript dependencies. It is a
 * great starting point when building robust, powerful web applications
 * using Laravel.
 */
import './bootstrap';
import { blankFrame, framesToContent, parseIconContent, pixelToHexA } from './icon';

// A blade may already have set this from the user's preference.
window.keymap = window.keymap || 'default';

const framebuffer = [];
let frames;
let currentFrame = 0;
let editor;
// Filled in once ./editor has been loaded, which only happens on pages that
// actually render one.
let editorSetValue = function () {};
let editorGetValue = function () {
	return '';
};

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
	frames[newFrame] = blankFrame();
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
	editorSetValue(editor, framesToContent(frames));
};

window.pixelToHexA = function (rgba) {
	return pixelToHexA(rgba);
};

window.lintFile = function () {
	const form = document.getElementById('content_form');
	const lintApi = form.getAttribute('action').replace('files', 'lint-content');
	window.$.post(lintApi, {
		file_content: editorGetValue(editor),
		_token: window.Laravel.csrfToken
	});
};

window.addEventListener('load', async function () {
	const ext = document.getElementById('extension');
	const extension = ext ? ext.getAttribute('value') : null;

	const fields = ['content', 'commands', 'constraints'];
	const needsEditor = fields.some(function (field) {
		return document.getElementById(field) || document.getElementById(field + '-readonly');
	});

	if (needsEditor) {
		// CodeMirror is a large dependency, so it is only fetched on the pages
		// that show an editor rather than on every page of the site.
		const cm = await import('./editor');
		editorSetValue = cm.setValue;
		editorGetValue = cm.getValue;

		fields.forEach(function (field) {
			const editable = document.getElementById(field);
			if (editable) {
				editor = cm.createEditor(editable, {
					language: cm.languageFor(extension),
					keyMap: window.keymap
				});
				// Warn about losing edits, but not when the edits are being
				// saved. Use the textarea's own form: not every page that has
				// an editor calls its form "content_form".
				window.onbeforeunload = function () {
					return true;
				};
				if (editable.form) {
					editable.form.addEventListener('submit', function () {
						window.onbeforeunload = null;
					});
				}
			}

			const readonly = document.getElementById(field + '-readonly');
			if (readonly) {
				cm.createEditor(readonly, {
					language: cm.languageFor(extension),
					readOnly: true
				});
			}
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
		const parsed = parseIconContent(icon.innerHTML);
		if (parsed !== null) {
			frames = parsed.frames;
			frames.forEach(function (frame, index) {
				window.addFrameButton(index);
			});
			currentFrame = 0;
			if (parsed.corrupt) {
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
					popupBasic = new window.Picker(parentBasic);
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
});
