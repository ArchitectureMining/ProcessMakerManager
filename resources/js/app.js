import 'bootstrap';

import Vue from 'vue';
import Vuelidate from 'vuelidate';
import * as validators from "vuelidate/lib/validators";

import VuelidateErrorExtractor, { templates } from 'vuelidate-error-extractor'

Vue.use(Vuelidate);
Vue.use(VuelidateErrorExtractor, {
  template: templates.singleErrorExtractor.bootstrap4
})

window.Vue = Vue;
window.validators = validators;

import {$,jQuery} from 'jquery';

window.$ = $;
window.jQuery = jQuery;
