// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

/**
 * CodeMirror 6 wrapper.
 *
 * The blade templates render plain <textarea> elements so the forms keep
 * working without JavaScript. Each editor hides its textarea, renders a
 * CodeMirror view next to it, and writes every change straight back into the
 * textarea, so ordinary form posts and window.lintFile() need to know nothing
 * about CodeMirror.
 */
import { EditorView, basicSetup } from 'codemirror';
import { EditorState } from '@codemirror/state';
import { keymap } from '@codemirror/view';
import { indentWithTab } from '@codemirror/commands';
import { StreamLanguage, indentUnit } from '@codemirror/language';
import { python } from '@codemirror/lang-python';
import { javascript } from '@codemirror/lang-javascript';
import { markdown } from '@codemirror/lang-markdown';
import { verilog } from '@codemirror/legacy-modes/mode/verilog';
import { shell } from '@codemirror/legacy-modes/mode/shell';
import { oneDark } from '@codemirror/theme-one-dark';
import { vim } from '@replit/codemirror-vim';
import { emacs } from '@replit/codemirror-emacs';

/**
 * Pick a language for a file extension. Anything unknown is treated as
 * micropython, which is what most eggs are.
 */
export function languageFor(extension) {
	switch (extension) {
	case 'json':
		return javascript();
	case 'v':
		return StreamLanguage.define(verilog);
	case 'md':
	case 'txt':
		return markdown();
	case 'sh':
		return StreamLanguage.define(shell);
	default:
		return python();
	}
}

/**
 * Map the user's editor preference onto a CodeMirror 6 keymap.
 *
 * CodeMirror 6 has no Sublime keymap, and its default bindings already follow
 * the same conventions, so 'sublime' falls through to the default.
 */
function keymapFor(name) {
	switch (name) {
	case 'vim':
		return vim();
	case 'emacs':
		return emacs();
	default:
		return [];
	}
}

function prefersDark() {
	return Boolean(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
}

/**
 * Replace a textarea with a CodeMirror view.
 *
 * @param {HTMLTextAreaElement} textarea
 * @param {{readOnly?: boolean, language: object, keyMap?: string}} options
 * @returns {EditorView}
 */
export function createEditor(textarea, options) {
	const readOnly = Boolean(options.readOnly);
	const extensions = [];

	// vim and emacs must come first so they can override the default bindings.
	if (!readOnly) {
		extensions.push(keymapFor(options.keyMap));
	}

	extensions.push(
		basicSetup,
		options.language,
		indentUnit.of('\t'),
		keymap.of([indentWithTab]),
		EditorView.lineWrapping
	);

	if (prefersDark()) {
		extensions.push(oneDark);
	}

	if (readOnly) {
		extensions.push(EditorState.readOnly.of(true), EditorView.editable.of(false));
	} else {
		// Keep the textarea authoritative so plain form posts still work.
		extensions.push(EditorView.updateListener.of(function (update) {
			if (update.docChanged) {
				textarea.value = update.state.doc.toString();
			}
		}));
	}

	const view = new EditorView({
		state: EditorState.create({ doc: textarea.value, extensions: extensions }),
		parent: textarea.parentNode
	});

	textarea.style.display = 'none';

	return view;
}

/**
 * Read the current contents of an editor.
 */
export function getValue(view) {
	return view.state.doc.toString();
}

/**
 * Replace the whole document of an editor.
 */
export function setValue(view, value) {
	view.dispatch({
		changes: { from: 0, to: view.state.doc.length, insert: value }
	});
}
