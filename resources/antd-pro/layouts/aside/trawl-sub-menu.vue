<template functional>
  <a-menu-item v-if="!props.menuInfo.children" :key="props.menuInfo.route">
    <a :href="props.menuInfo.uri">
      <a-icon v-if="props.menuInfo.icon" :type="props.menuInfo.icon" />
      <p>{{ props.menuInfo.text }}</p>
    </a>
  </a-menu-item>
  <a-sub-menu v-else :key="props.menuInfo.route">
    <span slot="title">
      <a :href="props.menuInfo.uri">
        <a-icon v-if="props.menuInfo.icon" :type="props.menuInfo.icon" />
        <p>{{ props.menuInfo.text }}</p>
      </a>
    </span>
    <template v-for="item in props.menuInfo.children">
      <a-menu-item v-if="!item.children" :key="item.route">
        <a :href="item.uri">
          <a-icon v-if="item.icon" :type="item.icon" />
          <p>{{ item.text }}</p>
        </a>
      </a-menu-item>
      <sub-menu v-else :key="item.route" :menu-info="item" />
    </template>
  </a-sub-menu>
</template>
<script>
export default {
  props: ["menuInfo"]
};
</script>
