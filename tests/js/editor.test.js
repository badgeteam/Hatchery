/**
 * @vitest-environment jsdom
 */
import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { createEditor, getValue, languageFor, setValue } from '../../resources/assets/js/editor.js';

function textareaWith(value) {
	document.body.innerHTML = '<form><textarea name="file_content">' + value + '</textarea></form>';

	return document.querySelector('textarea');
}

const views = [];

function mount(textarea, options) {
	const view = createEditor(textarea, options);
	views.push(view);

	return view;
}

beforeEach(() => {
	document.body.innerHTML = '';
});

afterEach(() => {
	// jsdom has no layout, so a surviving view keeps throwing from its
	// measure loop after the test that made it has finished.
	while (views.length) {
		views.pop().destroy();
	}
});

describe('languageFor', () => {
	it('maps known extensions onto a language', () => {
		for (const extension of ['py', 'json', 'md', 'txt', 'v', 'sh']) {
			expect(languageFor(extension), extension).toBeTruthy();
		}
	});

	it('falls back to python for unknown and missing extensions', () => {
		expect(languageFor('wat')).toBeTruthy();
		expect(languageFor(null)).toBeTruthy();
	});
});

describe('createEditor', () => {
	it('seeds the document from the textarea', () => {
		const textarea = textareaWith('import time');
		const view = mount(textarea, { language: languageFor('py') });

		expect(getValue(view)).toBe('import time');
	});

	it('hides the textarea but leaves it in the form', () => {
		const textarea = textareaWith('import time');
		const view = mount(textarea, { language: languageFor('py') });

		expect(textarea.style.display).toBe('none');
		expect(textarea.isConnected).toBe(true);
		expect(textarea.form).not.toBeNull();
		expect(view.dom.isConnected).toBe(true);
	});

	it('writes edits back into the textarea so plain form posts still work', () => {
		const textarea = textareaWith('one');
		const view = mount(textarea, { language: languageFor('py') });

		view.dispatch({ changes: { from: 3, insert: '\ntwo' } });

		expect(getValue(view)).toBe('one\ntwo');
		expect(textarea.value).toBe('one\ntwo');
	});

	it('keeps the original content readable through the textarea markup', () => {
		// The icon editor reads icon.innerHTML rather than .value, so the
		// initial markup has to survive mounting.
		const textarea = textareaWith('icon = ([0x00000000], 1)');
		mount(textarea, { language: languageFor('py') });

		expect(textarea.innerHTML).toBe('icon = ([0x00000000], 1)');
	});

	it('is not editable when read only', () => {
		const textarea = textareaWith('read me');
		const view = mount(textarea, { language: languageFor('md'), readOnly: true });

		// readOnly stops the editing commands, editable:false stops the
		// browser from letting anyone type into the DOM at all.
		expect(view.state.readOnly).toBe(true);
		expect(view.contentDOM.getAttribute('contenteditable')).toBe('false');
		expect(view.contentDOM.getAttribute('aria-readonly')).toBe('true');
	});

	it('does not sync a read only editor back into the textarea', () => {
		const textarea = textareaWith('read me');
		const view = mount(textarea, { language: languageFor('md'), readOnly: true });

		view.dispatch({ changes: { from: 0, insert: 'nope' } });

		expect(textarea.value).toBe('read me');
	});
});

describe('setValue', () => {
	it('replaces the whole document and syncs the textarea', () => {
		const textarea = textareaWith('old');
		const view = mount(textarea, { language: languageFor('py') });

		setValue(view, 'icon = ([0x00000000], 1)');

		expect(getValue(view)).toBe('icon = ([0x00000000], 1)');
		expect(textarea.value).toBe('icon = ([0x00000000], 1)');
	});
});

describe('keymaps', () => {
	it('builds an editor for every keymap the user can pick', () => {
		for (const keyMap of ['default', 'vim', 'emacs', 'sublime']) {
			const textarea = textareaWith('import time');
			const view = mount(textarea, { language: languageFor('py'), keyMap });

			expect(getValue(view), keyMap).toBe('import time');
		}
	});
});
