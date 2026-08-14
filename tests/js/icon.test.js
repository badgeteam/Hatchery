// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

import { describe, expect, it } from 'vitest';
import {
	BLANK_PIXEL,
	PIXELS_PER_FRAME,
	blankFrame,
	framesToContent,
	parseIconContent,
	pixelToHexA
} from '../../resources/assets/js/icon.js';

describe('pixelToHexA', () => {
	it('converts rgb() to 0xRRGGBBAA with a full alpha channel', () => {
		expect(pixelToHexA('rgb(255, 0, 0)')).toBe('0xff0000ff');
		expect(pixelToHexA('rgb(0, 128, 0)')).toBe('0x008000ff');
	});

	it('converts rgba() and keeps the alpha channel', () => {
		expect(pixelToHexA('rgba(0, 0, 0, 0)')).toBe('0x00000000');
		expect(pixelToHexA('rgba(255, 255, 255, 1)')).toBe('0xffffffff');
		expect(pixelToHexA('rgba(17, 34, 51, 0.5)')).toBe('0x11223380');
	});

	it('pads single digit channels', () => {
		expect(pixelToHexA('rgb(1, 2, 3)')).toBe('0x010203ff');
	});

	it('accepts the space separated form browsers also produce', () => {
		expect(pixelToHexA('rgb(255 0 0)')).toBe('0xff0000ff');
	});

	it('converts percentage channels', () => {
		expect(pixelToHexA('rgb(100%, 0%, 0%)')).toBe('0xff0000ff');
	});

	it('falls back to black for unparseable channels', () => {
		expect(pixelToHexA('rgb(nope, nope, nope)')).toBe('0x000000ff');
	});
});

describe('blankFrame', () => {
	it('is a full frame of transparent pixels', () => {
		const frame = blankFrame();
		expect(frame).toHaveLength(PIXELS_PER_FRAME);
		expect(new Set(frame)).toEqual(new Set([BLANK_PIXEL]));
	});
});

describe('framesToContent', () => {
	it('renders a single frame as python source', () => {
		const frame = blankFrame();
		frame[0] = '0xff0000ff';

		expect(framesToContent([frame])).toBe(
			'icon = ([' + frame.join(', ') + '], 1)'
		);
	});

	it('renders the frame count for multiple frames', () => {
		const content = framesToContent([['0x00000000'], ['0xffffffff']]);

		expect(content).toBe('icon = ([0x00000000], [0xffffffff], 2)');
	});
});

describe('parseIconContent', () => {
	it('ignores content that is not an icon', () => {
		expect(parseIconContent('import time')).toBeNull();
		expect(parseIconContent('')).toBeNull();
	});

	it('parses a single frame', () => {
		const parsed = parseIconContent('icon = ([0x00000000, 0xff0000ff], 1)');

		expect(parsed.declared).toBe(1);
		expect(parsed.corrupt).toBe(false);
		expect(parsed.frames).toEqual([['0x00000000', '0xff0000ff']]);
	});

	it('parses multiple frames', () => {
		const parsed = parseIconContent('icon = ([0x00000000], [0xffffffff], 2)');

		expect(parsed.declared).toBe(2);
		expect(parsed.corrupt).toBe(false);
		expect(parsed.frames).toEqual([['0x00000000'], ['0xffffffff']]);
	});

	it('flags content whose frame count does not match the data', () => {
		const parsed = parseIconContent('icon = ([0x00000000], 2)');

		expect(parsed.corrupt).toBe(true);
	});

	it('returns a blank frame for an empty icon instead of throwing', () => {
		const parsed = parseIconContent('icon = (, 0)');

		expect(parsed.corrupt).toBe(false);
		expect(parsed.frames).toEqual([blankFrame()]);
	});

	it('round trips through framesToContent', () => {
		const frames = [blankFrame(), blankFrame()];
		frames[1][5] = '0x11223344';

		const parsed = parseIconContent(framesToContent(frames));

		expect(parsed.corrupt).toBe(false);
		expect(parsed.frames).toEqual(frames);
	});

	it('tolerates the leading and trailing whitespace textareas keep', () => {
		const parsed = parseIconContent('\n  icon = ([0x00000000], 1)\n  ');

		expect(parsed.frames).toEqual([['0x00000000']]);
	});
});
