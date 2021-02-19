<template>
  <a-table :rowKey="rowKey" :columns="columns" :data-source="dataSource">
  </a-table>
</template>

<script>
export default {
  props: {
    rowKey: {
      type: String,
      default: ""
    },
    columns: {
      type: Array,
      default: []
    },
    dataSource: {
      type: Array,
      default: []
    }
  }
};
</script>

<style lang="scss">

</style>
