import BootstrapVue from 'bootstrap-vue/dist/bootstrap-vue.esm';
import VueGoodTablePlugin from "vue-good-table";
import Meta from "vue-meta";
import DateRangePicker from 'vue-mj-daterangepicker';
import 'vue-mj-daterangepicker/dist/vue-mj-daterangepicker.css';
import "./../assets/styles/sass/themes/lite-purple.scss";
import "./sweetalert2.js";

export default {
  install(Vue) {
    Vue.use(BootstrapVue);
    Vue.component(
      "large-sidebar",
      // The `import` function returns a Promise.
      () => import(/* webpackChunkName: "largeSidebar" */ "../containers/layouts/largeSidebar")
    );
 
    Vue.component(
      "customizer",
      // The `import` function returns a Promise.
      () => import(/* webpackChunkName: "customizer" */ "../components/common/customizer.vue")
    );
    Vue.component("vue-perfect-scrollbar", () =>
      import(/* webpackChunkName: "vue-perfect-scrollbar" */ "vue-perfect-scrollbar")
    );
    Vue.use(DateRangePicker);
    Vue.use(Meta, {
      keyName: "metaInfo",
      attribute: "data-vue-meta",
      ssrAttribute: "data-vue-meta-server-rendered",
      tagIDKeyName: "vmid",
      refreshOnceOnNavigation: true
    });
    Vue.use(VueGoodTablePlugin);

  }
};
