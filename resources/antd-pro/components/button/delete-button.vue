<template>
  <a-tooltip>
    <template slot="title">
      Delete
    </template>
    <a-button icon="delete" size="small" @click="handleClick" />
  </a-tooltip>
</template>

<script>
export default {
  name: "delete-button",
  methods: {
    handleClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style scoped>

</style>
