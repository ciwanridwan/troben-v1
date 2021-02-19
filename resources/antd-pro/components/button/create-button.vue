<template>
  <a-tooltip>
    <template slot="title">
      Create
    </template>
    <a-button icon="plus" size="small" @click="handleClick" />
  </a-tooltip>
</template>

<script>
export default {
  name: "create-button",
  methods: {
    handleClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style scoped>

</style>
