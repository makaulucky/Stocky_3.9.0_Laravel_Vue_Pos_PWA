import Vue from "vue";
import VueCookies from "vue-cookies";
Vue.use(VueCookies);

export default (to, from, next) => {
  let accessToken = VueCookies.isKey("Stocky_token");
  if (!accessToken) {
    next("/app/sessions/signIn");
  } else {
    return next();
  }
};
