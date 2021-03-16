<template>
  <div>
    <!-- table header -->
    <a-card>
      <a-row>
        <a-col
          v-for="(column, index) in columns"
          :key="index"
          :span="column.size ? column.size : defaultColumnSize"
        >
          {{ column.title }}
        </a-col>
      </a-row>
    </a-card>

    <a-card v-for="(record, index) in dataSource" :key="index">
      <a-row>
        <a-col
          v-for="(column, index) in columns"
          :key="index"
          :span="column.size ? column.size : defaultColumnSize"
        >
          <slot
            :name="column.customSlot"
            v-bind="{ [`${column.customSlot}`]: record }"
            v-if="hasSlot(column.customSlot)"
          ></slot>
          <span v-else>
            {{ record[column.dataIndex] }}
          </span>
        </a-col>
      </a-row>
    </a-card>
  </div>
</template>
<script>
import dynamicComponent from "./dynamic-component.vue";
export default {
  components: { dynamicComponent },
  props: ["columns", "data-source"],
  computed: {
    defaultColumnSize() {
      return Math.floor(24 / this.columns.length);
    },
    allSlots() {
      return Object.keys(this.$slots);
    }
  },
  methods: {
    hasSlot(key) {
      console.log(this.$slots);
      return !!this.$slots[key];
    }
  }
};
</script>
