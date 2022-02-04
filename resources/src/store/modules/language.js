import Vue from 'vue';
import VueLocalStorage from 'vue-localstorage';
import Languages from '../../translations';

Vue.use(VueLocalStorage);
const supportedLanguages = Object.getOwnPropertyNames(Languages);

export default {
  namespaced: true,
  state: {
    language: Vue.localStorage.get('language'),
  },
  mutations: {
    SET_LANGUAGE(state, lang) {
      Vue.localStorage.set('language', lang);
      state.language = lang;
    },
  },
  actions: {
    setLanguage({ commit }, languages) {
      if (typeof languages === 'string') {
        commit('SET_LANGUAGE', languages);
      } else {
        const language = supportedLanguages.find(sl =>
          languages.find(l => (l.split(new RegExp(sl, 'gi')).length - 1 > 0 ? sl : null)));
          commit('SET_LANGUAGE', language);
      }
    },
  },
};