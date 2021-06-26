<template>
  <a-layout-header :class="['admin-header']">
    <a-row type="flex" justify="space-between">
      <a-col :span="3">
        <a-input-search></a-input-search>
      </a-col>

      <a-col :span="12">
        <a-row type="flex" justify="end">
          <a-col :span="10">
            <a-dropdown :trigger="['click']">
              <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                <a-icon type="user"></a-icon>
                <span>{{ name }}</span>
              </a>
              <a-card slot="overlay">
                <trawl-change-roles />
                <a-divider class="trawl-text-normal" />
                <a-menu>
                  <a-menu-item key="logouts">
                    <a :href="routeUri('auth.logout')">Logout</a>
                  </a-menu-item>
                </a-menu>
              </a-card>
            </a-dropdown>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
  </a-layout-header>
</template>
<script>
export default {
  computed: {
    name() {
      return this.$laravel.user.name;
    }
  }
};
</script>

<style lang="scss">
.admin-header-trawl {
  background-color: white;
}
</style>
