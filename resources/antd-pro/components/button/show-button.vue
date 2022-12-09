<template>
  <a-tooltip>
    <template slot="title">
      Show To Apps
    </template>
    <a-button icon="check" size="small" @click="handleClick" />
  </a-tooltip>
</template>

<script>
export default {
  name: "show-button",
  methods: {
    handleClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style scoped>

</style>
