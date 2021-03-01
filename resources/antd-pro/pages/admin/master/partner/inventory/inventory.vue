<template>
  <div>
    <content-layout title="Inventaris Mitra">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
          <a-col>
            <add-inventory></add-inventory>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <a-table :columns="partnerInventoryColumns" style="margin-top:24px">
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
      partnerInventoryColumns
    };
  },
  components: { ContentLayout, AddInventory }
};
</script>
