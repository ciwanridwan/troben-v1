import Vue from "vue";
import Clipboard from "v-clipboard";
import qs from "qs";
import Laravel from "../antd-pro/laravel";
import Geo from "../antd-pro/data/geo";
import TrawlNotification from "../antd-pro/data/trawlNotification";
import VueQrcode from "vue-qrcode";
import moment from "moment";
import { isPromise } from "../antd-pro/functions/general";
import Firebase from "./firebase";

require("./bootstrap");
Vue.component("vue-qrcode", VueQrcode);
Vue.use(require("ant-design-vue"));
Vue.use(require("vue-shortkey"), { prevent: ["input", "textarea"] });
Vue.use(Clipboard);

// register proto object

Vue.prototype.$http = window.axios.create();
Vue.prototype.$laravel = window.Laravel;
Vue.prototype.$qs = qs;

// prepare global data for notification watcher.
const trawlNotificationData = Vue.observable({ data: {} });
Object.defineProperty(Vue.prototype, '$trawlNotificationData', {
  get() { return trawlNotificationData.data },
  set(value) { trawlNotificationData.data = value; }
})

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

Vue.mixin({
  computed: {
    currentDate() {
      let d = new Date();
      return d.toDateString();
    }
  },
  methods: {
    isPromise,
    dateSimpleFormat(date) {
      return moment(date).format("ddd, DD MMM YYYY");
    },
    getBase64(img, callback) {
      const reader = new FileReader();
      reader.addEventListener("load", () => callback(reader.result));
      reader.readAsDataURL(img);
    },
    getParent(name) {
      let p = this.$parent;
      while (typeof p !== "undefined") {
        if (p.$options.name == name) {
          return p;
        } else {
          p = p.$parent;
        }
      }
      return false;
    },
    filterOptionMethod(input, option) {
      return (
        option.componentOptions.children[0].text
          .toLowerCase()
          .indexOf(input.toLowerCase()) >= 0
      );
    }
  }
});

Vue.mixin(Laravel);
Vue.mixin(Geo);
Vue.mixin(TrawlNotification);
Vue.mixin(Firebase);

new Vue({}).$mount("#app");
