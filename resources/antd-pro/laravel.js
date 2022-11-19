import config from "./config";
import moment from "moment";

const uriDataFinance = "admin.payment.data";
const laravel = {
  data() {
    return {
      socketBaseUrl: "wss://pulsar.trawlbens.com",
      chatBaseUrl: "https://chat.trawlbens.com",
      // socketBaseUrl: "wss://staging-ws.trawlbens.com",
      // chatBaseUrl: "https://staging-chat.trawlbens.com",
      filter: {
        q: null,
        per_page: 10,
      },
    };
  },
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
      return {
        showSizeChanger: true,
        current: this.items?.current_page ? this.items.current_page : 1,
        pageSize: parseInt(this.items?.per_page ? this.items.per_page : 10),
        total: this.items?.total ? this.items.total : 0,
      };
    },
  },
  methods: {
    moment,
    getItems() {
      this.loading = true;
      this.$http
        .get(this.routeUri(this.getRoute()), { params: this.filter })
        .then((res) => this.onSuccessResponse(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getFinanceDataDaily() {
      this.loading = true;
      this.$http
        .get(this.routeUri(uriDataFinance), { params: this.filterDaily })
        .then((res) => this.onSuccessResponseDaily(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getFinanceDataMonthly() {
      this.loading = true;
      this.$http
        .get(this.routeUri(uriDataFinance), { params: this.filterMonthly })
        .then((res) => this.onSuccessResponseMonthly(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getFinanceDataChart() {
      this.loading = true;
      this.$http
        .get(this.routeUri(uriDataFinance), { params: this.filterChart })
        .then((res) => this.onSuccessResponseChart(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getDataPartner() {
      this.loading = true;
      this.$http
        .get(this.routeUri(uriDataFinance), { params: this.filterPartner })
        .then((res) => this.onSuccessResponsePartner(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getRegency() {
      this.loading = true;
      this.$http
        .get(this.routeUri(uriDataFinance), { params: this.filterGeo })
        .then((res) => this.onSuccessResponse(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getProvince() {
      this.loading = true;
      this.$http
        .get(this.routeUri(uriDataFinance), { params: this.filterGeo })
        .then((res) => this.onSuccessResponse(res.data))
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    onErrorResponse(error) {
      this.$notification.error({
        message: error.response.data.message,
      });
    },
    onErrorValidation(err) {
      let messages = err.response.data.data;
      _.map(messages, (message) => {
        this.$notification.error({
          message,
        });
      });
    },
    deleteItem(record) {
      this.loading = true;
      let uri = this.routeUri(this.getRoute());
      let { hash } = record;
      uri = uri + "/" + hash;
      this.form.username = this.$laravel.user.username;
      this.$http
        .delete(uri)
        .then(() => {
          this.getItems();
        })
        .catch((err) => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    handleTableChanged(pagination) {
      this.filter.page = pagination.current;
      this.filter.per_page = pagination.pageSize;

      this.getItems();
    },
    numbering(index, pagination) {
      return pagination.per_page * (pagination.current_page - 1) + index + 1;
    },
    routeOriginUri(routeName) {
      return location.origin + this.routeUri(routeName);
    },
    redirectToPathName(routeName) {
      history.pushState(
        null,
        document.title,
        location.origin + this.routeUri(routeName)
      );
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
    roles() {
      let roles = [];
      this.$laravel.user?.partners?.forEach((o) =>
        roles.push(o?.pivot?.role ?? null)
      );
      return roles;
    },
    user() {
      return this.$laravel.user;
    },
    isAdmin() {
      return this.$laravel.user?.is_admin;
    },
    initial(v) {
      return v
        .match(/(^\S\S?|\b\S)?/g)
        .join("")
        .match(/(^\S|\S$)?/g)
        .join("")
        .toUpperCase();
    },
    transUri(navigation) {
      _.forIn(navigation, (o, k) => {
        o.uri = this.routeUri(o.route);
        if (o.children) {
          o.children = this.transUri(o.children);
        }
      });
      return navigation;
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
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(number);
    },
    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
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
        total: 0,
      };
    },
    handleXhrError(error) {
      this.$notification.error({
        message: error.response.data.code
          ? error.response.data.code
          : "UNKNOWN",
        description: error.response.data.message
          ? error.response.data.message
          : "",
      });
    },
    notify(notification) {
      this.$notification[notification.type]({
        key: notification.id,
        message: notification.title,
        description: notification.message,
      });
    },
  },
  watch: {
    config: {
      handler: function (value) {
        localStorage.setItem("antd_config", JSON.stringify(value.layout));
      },
      deep: true,
      immediate: true,
    },
  },
};

export default laravel;
