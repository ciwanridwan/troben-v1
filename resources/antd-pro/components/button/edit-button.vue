<template>
  <a-tooltip>
    <template slot="title">
      Edit
    </template>
    <a-button icon="edit" size="small" @click="handleClick" />
  </a-tooltip>
</template>

<script>
export default {
  name: "edit-button",
  methods: {
    handleClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style scoped>

</style>
