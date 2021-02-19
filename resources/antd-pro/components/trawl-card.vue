<template>
  <a-card :bordered="false">
    <slot></slot>
  </a-card>
</template>
<script>
export default {};
</script>
<style lang="scss">
.ant-card {
  border-radius: 10px;
}
</style>
