import Vue from "vue";
import Clipboard from "v-clipboard";
import qs from "qs";
import Laravel from "../antd-pro/laravel";

require("./bootstrap");

Vue.use(require("ant-design-vue"));
Vue.use(require("vue-shortkey"), { prevent: ["input", "textarea"] });
Vue.use(Clipboard);

// register proto object

Vue.prototype.$http = window.axios.create();
Vue.prototype.$laravel = window.Laravel;
Vue.prototype.$qs = qs;

// automatic component registration.
const files = require.context("../antd-pro", true, /\.vue$/i);
files.keys().map(key =>
  Vue.component(
    key
      .split("/")
      .pop()
      .split(".")[0],
    files(key).default
  )
);

const components = require.context("./components", true, /\.vue$/i);
components.keys().map(key =>
  Vue.component(
    key
      .split("/")
      .pop()
      .split(".")[0],
    components(key).default
  )
);

Vue.mixin(Laravel);

new Vue({}).$mount("#app");
