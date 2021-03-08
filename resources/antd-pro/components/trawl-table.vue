<template>
  <table :style="{ width: tableWidth }">
    <thead>
      <tr>
        <th v-for="head in columns" :key="head">
          {{ head.title }}
        </th>
      </tr>
    </thead>
    <tbody>
      <template v-for="(item, index) in dataSource">
        <tr :key="index">
          <template v-for="(column, colIndex) in columns">
            <td :key="colIndex" :rowspan="column.counter ? 2 : 1">
              <slot v-bind="item"></slot>
              <slot :name="column.key" v-if="checkSlot(column.key)"></slot>
              <span v-else>{{
                column.dataIndex ? item[column.dataIndex] : item
              }}</span>
            </td>
          </template>
        </tr>
        <tr :key="index" v-if="doubleRow">
          <td :colspan="colspanLength">
            <slot name="custom-row"></slot>
          </td>
        </tr>
      </template>
    </tbody>
  </table>
</template>
<script>
export default {
  props: ["columns", "data-source", "fullWidth", "doubleRow"],
  computed: {
    tableWidth() {
      return this.fullWidth ? "100%" : "auto";
    },
    columnWithoutCounter() {
      return _.filter(this.columns, o => {
        return !o.counter;
      });
    },
    colspanLength() {
      return this.columnWithoutCounter.length;
    }
  },
  methods: {
    checkSlot(slotName) {
      console.log(!!this.$slots[slotName], slotName, this.$slots);
      return !!this.$slots[slotName];
    }
  }
};
</script>
