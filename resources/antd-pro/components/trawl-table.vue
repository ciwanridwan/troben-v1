<template>
  <a-space direction="vertical" size="large" class="trawl-table-component">
    <a-card class="trawl-table-component-header--container">
      <a-row type="flex" justify="space-between">
        <a-col
          v-for="(column, index) in columns"
          :key="index"
          :span="column.colspan ? column.colspan : defaultColumnSize"
          :class="[column.classes ? column.classes : 'trawl-text-center']"
        >
          <span class="trawl-table-component-header--title">{{
            column.title
          }}</span>
        </a-col>
      </a-row>
    </a-card>
    <a-card
      v-for="(item, itemIndex) in dataSource"
      :key="itemIndex"
      class="trawl-table-component-content--container"
    >
      <a-row
        type="flex"
        justify="space-between"
        class="trawl-table-component-content--row"
      >
        <a-col
          v-for="(column, index) in columns"
          :key="index"
          :span="column.colspan ? column.colspan : defaultColumnSize"
          :class="[column.classes, 'trawl-table-component-content--col']"
        >
          <span
            v-if="
              hasScopedSlot(
                column.scopedSlots
                  ? column.scopedSlots.customRender
                    ? column.scopedSlots.customRender
                    : null
                  : null
              )
            "
            class="trawl-table-component-content--field"
          >
            <slot :name="column.scopedSlots.customRender" :record="item"></slot>
          </span>
          <!-- <div v-if="hasSlot(column.scopedSlot)">
          </div> -->
          <span
            v-else-if="column.customRender"
            class="trawl-table-component-content--field"
          >
            {{ displayCustomRender(column, item, itemIndex) }}
          </span>
          <span
            v-else-if="column.dataIndex"
            class="trawl-table-component-content--field"
            >{{ resolve(column.dataIndex, item) }}</span
          >
          <span v-else class="trawl-table-component-content--field">
            {{ item }}
          </span>
        </a-col>
      </a-row>
      <a-divider />
      <a-row v-if="hasScopedSlot('expandedRowRender')" type="flex">
        <a-col :span="24">
          <slot name="expandedRowRender" :record="item"></slot>
        </a-col>
      </a-row>
    </a-card>
    <a-pagination
      v-model="current"
      show-size-changer
      @showSizeChange="changePerPage"
      :total="pageTotal"
      :defaultPageSize="pagePer_page"
      show-less-items
      @change="reloadItems"
      :pageSizeOptions="['10', '15', '25', '40']"
    />
  </a-space>
</template>
<script>
export default {
  data() {
    return {
      current: 1,
    };
  },
  props: {
    columns: {
      type: Array,
      default: () => {
        return [];
      },
    },
    dataSource: {
      type: Array,
      default: () => {
        return [];
      },
    },
    pagination: {
      type: Object,
    },
  },
  computed: {
    defaultColumnSize() {
      let spanLeft = 24;
      this.columns.forEach((column) => {
        if (column.colspan) {
          spanLeft -= column.colspan;
        }
      });
      return Math.floor(spanLeft / this.columns.length);
    },
    pageTotal() {
      return this.pagination
        ? this.pagination.total
        : this.getDefaultPagination().total;
    },
    pagePer_page() {
      return this.pagination
        ? this.pagination.pageSize
        : this.getDefaultPagination().total;
    },
  },
  mounted() {},
  methods: {
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    },
    hasScopedSlot(scopedSlotName) {
      return !!this.$scopedSlots[scopedSlotName];
    },
    resolve(path, obj) {
      return path.split(".").reduce(function (prev, curr) {
        return prev ? prev[curr] : null;
      }, obj || self);
    },
    displayCustomRender(column, row, index) {
      let text = column?.dataIndex ? this.resolve(column.dataIndex, row) : "";
      let result = column?.customRender(text, row, index);
      const { children } = result;
      return (children && children != "") || children != undefined
        ? children
        : result;
    },
    reloadItems() {
      this.$emit("changePage", this.current);
      window.scrollTo({ behavior: "smooth", top: "0px" });
    },
    changePerPage(current, size) {
      this.$emit("changeSizePage", size);
    },
  },
};
</script>
