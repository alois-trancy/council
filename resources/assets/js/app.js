
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import InstantSearch from 'vue-instantsearch';
import algoliasearch from 'algoliasearch/lite';
import { history as historyRouter } from 'instantsearch.js/es/lib/routers';
import { simple as simpleMapping } from 'instantsearch.js/es/lib/stateMappings';

window.Vue = require('vue');

Vue.use(InstantSearch);

// Vue.prototype.authorize = function (handler) {
// 	let user = window.App.user;
// 	return user ? handler(user) : false;
// };

let authorizations = require('./authorizations');

Vue.prototype.authorize = function (...params) {
	if (! window.App.signedIn) return false;

	if (typeof params[0] === 'string') {
		return authorizations[params[0]](params[1]);
	}

	return params[0](window.App.user)
};

Vue.prototype.signedIn = window.App.signedIn;

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

window.events = new Vue();
window.flash = function (message, level = 'success') {
	window.events.$emit('flash', {message, level});
};

Vue.component('flash', require('./components/Flash.vue'));
Vue.component('paginator', require('./components/Paginator.vue'));
Vue.component('user-notifications', require('./components/UserNotifications.vue'));
// Vue.component('reply', require('./components/Reply.vue'));
Vue.component('thread-view', require('./pages/Threads.vue'));
Vue.component('avatar-form', require('./components/AvatarForm.vue'));
Vue.component('wysiwyg', require('./components/Wysiwyg.vue'));

Vue.config.ignoredElements = ['trix-editor'];

const app = new Vue({
    el: '#app',
    data: {
        searchClient: algoliasearch(
            process.env.MIX_ALGOLIA_APP_ID,
            process.env.MIX_ALGOLIA_SEARCH
        ),
        routing: {
          router: historyRouter(),
          stateMapping: simpleMapping(),
        },
    },
});