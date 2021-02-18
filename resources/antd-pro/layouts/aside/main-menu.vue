<script>
import { main as navigation, getNavigation } from "../../navigation";

export default {
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
      let router = this.route(route);
      window.location.href = router
        ? "/" + router.uri
        : window.location.pathname;
    },
    getNavigation
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
    <template v-for="(item, index) in navigation">
      <template v-if="item.children === null">
        <a-menu-item :key="item.route">
          <a
            :href="routeUri(item.route)"
            v-shortkey="item.shortKey"
            @shortkey="navigate(item.route)"
          >
            <a-icon :type="item.icon" />
            <span>{{ item.text }}</span>
          </a>
        </a-menu-item>
      </template>
      <template v-else>
        <a-sub-menu :key="item.route" @titleClick="navigate(item.route)">
          <span slot="title">
            <a-icon :type="item.icon" />
            <span>{{ item.text }}</span>
          </span>
          <a-menu-item
            v-for="(child, cIndex) in item.children"
            :key="child.route"
          >
            <a
              v-shortkey="child.shortKey"
              @shortkey="navigate(child.route)"
              :href="routeUri(child.route)"
            >
              <span>{{ child.text }}</span>
            </a>
          </a-menu-item>
        </a-sub-menu>
      </template>
    </template>
  </a-menu>
</template>
