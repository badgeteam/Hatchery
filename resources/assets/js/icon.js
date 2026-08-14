/**
 * Icon helpers.
 *
 * Badge icons are stored as python source in a file called icon.py, shaped
 * like `icon = ([0x00000000, ...], 1)` — a list of 64 pixels per frame,
 * followed by the number of frames. These functions convert between that
 * source form and plain arrays; everything that touches the DOM lives in
 * app.js.
 */

export const PIXELS_PER_FRAME = 64;
export const BLANK_PIXEL = '0x00000000';

/**
 * Convert a CSS rgb()/rgba() colour into the 0xRRGGBBAA form icons use.
 *
 * @param {string} rgba
 * @returns {string}
 */
export function pixelToHexA(rgba) {
	let remove = 5;
	if (rgba.indexOf('rgba') === -1) {
		remove = 4;
	}
	let sep = rgba.indexOf(',') > -1 ? ',' : ' ';
	let parts = rgba.substr(remove).split(')')[0].split(sep);
	if (parts.indexOf('/') > -1) {
		parts.splice(3, 1);
	}
	for (let index in parts) {
		let part = parts[index];
		if (part.indexOf('%') > -1) {
			let fraction = part.substr(0, part.length - 1) / 100;
			parts[index] = index < 3 ? Math.round(fraction * 255) : fraction;
		}
	}
	if (isNaN(parts[0])) {
		parts[0] = 0;
	}
	if (isNaN(parts[1])) {
		parts[1] = 0;
	}
	if (isNaN(parts[2])) {
		parts[2] = 0;
	}
	if (isNaN(parts[3])) {
		parts[3] = 1;
	}
	let r = (+parts[0]).toString(16),
		g = (+parts[1]).toString(16),
		b = (+parts[2]).toString(16),
		a = Math.round(+parts[3] * 255).toString(16);
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
}

/**
 * Render frames back into the python source stored in icon.py.
 *
 * @param {string[][]} frames
 * @returns {string}
 */
export function framesToContent(frames) {
	const rendered = frames.map(function (frame) {
		return '[' + frame.join(', ') + ']';
	});

	return 'icon = (' + rendered.join(', ') + ', ' + frames.length + ')';
}

/**
 * Build an empty frame.
 *
 * @returns {string[]}
 */
export function blankFrame() {
	return new Array(PIXELS_PER_FRAME).fill(BLANK_PIXEL);
}

/**
 * Parse the python source of an icon file into frames.
 *
 * Returns null when the content is not an icon at all, so callers can leave
 * the pixel editor alone. `corrupt` is true when the declared frame count does
 * not match what was actually parsed.
 *
 * @param {string} content
 * @returns {{frames: string[][], declared: number, corrupt: boolean}|null}
 */
export function parseIconContent(content) {
	let data = String(content).trim();
	if (!data.startsWith('icon = ')) {
		return null;
	}

	data = data.replace('icon = (', '');
	data = data.replace(')', '');

	const match = data.match(/[0-9]+?$/);
	if (match === null) {
		return null;
	}
	const declared = parseInt(match[0], 10);
	data = data.replace(', ' + declared, '');

	if (declared === 0) {
		return { frames: [blankFrame()], declared: declared, corrupt: false };
	}

	const frames = data.split('],').map(function (frame) {
		return frame
			.trim()
			.replace('[', '')
			.replace(']', '')
			.trim()
			.split(',')
			.map(function (pixel) {
				return pixel.trim();
			});
	});

	return { frames: frames, declared: declared, corrupt: frames.length !== declared };
}
