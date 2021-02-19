<template>
  <a-card :title="title">
    <create-button></create-button>
    <detail-button></detail-button>
    <edit-button></edit-button>
    <delete-button></delete-button>
  </a-card>
</template>

<script>
export default {
  name: "customer-list",
  props: {
    cardTitle: {
      type: String,
      nullable: true
    }
  },
  computed: {
    title() {
      return this.cardTitle || "Customer List"
    }
  }
}
</script>

<style scoped>

</style>
