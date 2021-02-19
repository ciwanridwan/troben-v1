import config from "./config";
import moment from "moment";

const laravel = {
  computed: {
    config() {
      if (localStorage.getItem("antd_config") !== null)
        Object.assign(
          config.layout,
          JSON.parse(localStorage.getItem("antd_config"))
        );

      return config;
    },
    trawlbensPagination() {
      if (this.items) {
        return {
          showSizeChanger: true,
          current: this.items.current_page,
          pageSize: parseInt(this.items.per_page),
          total: this.items.total
        }
      }

      return null
    }
  },
  methods: {
    moment,
    numbering(index, pagination) {
      return pagination.per_page * (pagination.current_page - 1) + index + 1;
    },
    pushState(state) {
      history.pushState(
        state,
        document.title,
        location.origin +
          location.pathname +
          "?" +
          this.$qs.stringify(state, { encode: false })
      );
    },
    isAuthenticated() {
      return this.$laravel.is_authenticated;
    },
    user() {
      return this.$laravel.user;
    },
    initial(v) {
      return v
        .match(/(^\S\S?|\b\S)?/g)
        .join("")
        .match(/(^\S|\S$)?/g)
        .join("")
        .toUpperCase();
    },
    routes() {
      return this.$laravel.routes;
    },
    getRoute() {
      return this.$laravel.current_route;
    },
    route(name) {
      return _.find(this.routes(), (o, k) => k === name);
    },
    routeUri(name, replacer = {}, isPath = false) {
      let path = isPath
        ? name
        : this.route(name)
        ? "/" + this.route(name).uri
        : window.location.origin + window.location.pathname;
      return path.replace(/{([a-z0-9_]+)}/gi, (match, key) => replacer[key]);
    },
    copy(value, message = null) {
      this.$clipboard(value);

      this.$message.success(
        message === null
          ? '"' + value + '" has been copied to your clipboard.'
          : message
      );
    },
    currency(number) {
      return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR"
      }).format(number);
    },
    getDefaultPagination() {
      return {
        current_page: 1,
        data: [],
        first_page_url: "",
        from: 1,
        last_page: 1,
        last_page_url: "",
        next_page_url: null,
        path: "",
        per_page: 15,
        prev_page_url: null,
        to: 1,
        total: 0
      };
    },
    handleXhrError(error) {
      this.$notification.error({
        message: error.response.data.code
          ? error.response.data.code
          : "UNKNOWN",
        description: error.response.data.message
          ? error.response.data.message
          : ""
      });
    },
    notify(notification) {
      this.$notification[notification.type]({
        key: notification.id,
        message: notification.title,
        description: notification.message
      });
    }
  },
  watch: {
    config: {
      handler: function(value) {
        localStorage.setItem("antd_config", JSON.stringify(value.layout));
      },
      deep: true,
      immediate: true
    }
  }
};

export default laravel;
