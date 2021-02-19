<script>
import { main as navigation, getNavigation } from "../../navigation";
import subMenu from "./sub-menu.vue";

export default {
  components: { subMenu },
  props: {
    theme: {
      type: String,
      required: true
    },
    collapsed: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    openedKeys() {
      if (this.collapsed) return [];

      return this.activeKeys;
    },
    activeKeys() {
      let opened = [];
      let route = this.getRoute();

      opened.push(this.getNavigation(route).route);

      return opened;
    }
  },
  data: () => ({
    navigation
  }),
  methods: {
    navigate(route) {
      window.location.href = this.routeUri(route);
    },
    getNavigation,
    transUri(navigation) {
      _.forIn(navigation, (o, k) => {
        o.uri = this.routeUri(o.route);
        if (o.children) {
          o.children = this.transUri(o.children);
        }
      });
      return navigation;
    }
  },
  created() {
    this.navigation = this.transUri(this.navigation);
  }
};
</script>
<template>
  <a-menu
    :theme="theme"
    mode="inline"
    class="menu"
    :defaultSelectedKeys="activeKeys"
    :defaultOpenKeys="openedKeys"
  >
    <sub-menu
      v-for="item in navigation"
      :key="item.route"
      :menuInfo="item"
      v-shortkey="item.shortKey"
      @shortkey="navigate(item.route)"
    ></sub-menu>
  </a-menu>
</template>
