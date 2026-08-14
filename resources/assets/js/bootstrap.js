import Picker from 'vanilla-picker';
import _ from 'lodash';
import $ from 'jquery';
import 'bootstrap';
import Echo from 'laravel-echo';
import { io } from 'socket.io-client';
import axios from 'axios';
import { Dropzone } from 'dropzone';

window.Picker = Picker;

window._ = _;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

window.$ = window.jQuery = $;

window.io = io;

window.Echo = new Echo({
	broadcaster: 'socket.io',
	host: window.location.hostname + ':6001'
});

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = axios;

window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Dropzone = Dropzone;
