<template>
  <a-menu
    :default-selected-keys="openKeys"
    :default-open-keys="openKeys"
    :open-keys="openKeys"
  >
    <a-menu-item v-for="roleRoute in roleRoutes" :key="roleRoute.role">
      <a :href="routeUri(roleRoute.baseRouteName)">
        <a-space>
          <a-icon :component="PeopleIcon" />
          <span>
            {{ roleRoute.title }}
          </span>
        </a-space>
      </a>
    </a-menu-item>
  </a-menu>
</template>
<script>
import { PeopleIcon } from "../../components/icons";
import { getAvailableFusionRoleRoutes } from "../../functions/roles";
export default {
  data() {
    return {
      openKeys: [],
      roleRoutes: [],
      PeopleIcon
    };
  },
  computed: {
    role() {
      return this.$laravel?.role;
    }
  },
  methods: {
    setAvailableRoleRoutes() {
      let availableRoutes = getAvailableFusionRoleRoutes(this.role);
      this.roleRoutes = availableRoutes?.filter(o => o.role != this.role) ?? [];
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.setAvailableRoleRoutes();
    });
  }
};
</script>
