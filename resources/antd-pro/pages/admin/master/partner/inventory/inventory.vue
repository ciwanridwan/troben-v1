<template>
  <div>
    <content-layout title="Inventaris Mitra">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
          <a-col>
            <add-inventory :inventories="form"></add-inventory>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <a-table
          :columns="partnerInventoryColumns"
          :data-source="form"
          style="margin-top:24px"
        >
          <span slot="number" slot-scope="item, record, index">
            {{ index + 1 }}
          </span>
          <span slot="action" slot-scope="item, record, index">
            <delete-button @click="deleteItem(index)"></delete-button>
          </span>
        </a-table>
      </template>
    </content-layout>
  </div>
</template>
<script>
import ContentLayout from "../../../../../layouts/content-layout.vue";
import partnerInventoryColumns from "../../../../../config/table/partner-inventory";
import AddInventory from "./add-inventory.vue";

export default {
  data() {
    return {
      partnerInventoryColumns,
      form: []
    };
  },
  components: { ContentLayout, AddInventory },
  methods: {
    deleteItem(index) {
      this.form.splice(index, 1);
    }
  }
};
</script>
, DeleteButton
