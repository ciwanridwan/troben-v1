<template>
  <a-tooltip>
    <template slot="title">
      Detail
    </template>
    <a-button icon="eye" size="small" @click="handleClick" />
  </a-tooltip>
</template>

<script>
export default {
  name: "detail-button",
  methods: {
    handleClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style scoped>

</style>
