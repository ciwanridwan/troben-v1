<template>
  <a-table {...tableProps}> </a-table>
</template>

<script>
import { Table } from "ant-design-vue";

export default {
  props: {
    ...Table.props
  },
  computed: {
    tableProps() {
      return this.$props;
    }
  }
};
</script>

<style lang="scss">

</style>
