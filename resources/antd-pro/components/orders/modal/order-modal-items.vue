<template>
  <order-modal-row-layout>
    <template slot="icon"> &nbsp; </template>
    <template slot="content">
      <a-row type="flex" :gutter="[12, 12]">
        <a-col v-for="(item, index) in value" :key="index" :span="12">
          <order-item-card
            v-model="value[index]"
            @change="onChange"
            :modifiable="modifiable"
            :editable="editable"
            :deletable="deletable"
            :package="package"
          />
        </a-col>
      </a-row>
    </template>
  </order-modal-row-layout>
</template>
<script>
import OrderItemCard from "../order-item-card.vue";
import orderModalRowLayout from "../order-modal-row-layout.vue";
export default {
  props: {
    value: {
      type: Array,
      default: () => {
        return [];
      },
    },
    modifiable: {
      type: Boolean,
      default: true,
    },
    editable: {
      type: Boolean,
      default: true,
    },
    deletable: {
      type: Boolean,
      default: true,
    },
    package: {
      type: Object,
      default: () => {
        return {};
      }
    }
  },
  methods: {
    onChange() {
      this.$emit("change");
    },
  },
  components: { orderModalRowLayout, OrderItemCard },
};
</script>
