<template>
  <a-layout-header :class="['admin-header']">
    <a-row type="flex" justify="space-between">
      <a-col :span="3">
        <a-input-search></a-input-search>
      </a-col>

      <a-col :span="4">
        <span>
          <a-icon type="user"></a-icon>
          <span>{{ name }}</span>
        </span>
      </a-col>
    </a-row>
  </a-layout-header>
</template>
<script>
export default {
  computed: {
    name() {
      return this.$laravel.user.name;
    },
  },
};
</script>

<style lang="scss">
.admin-header-trawl {
  background-color: white;
}
</style>
