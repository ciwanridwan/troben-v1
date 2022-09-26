<template>
  <div>
    <content-layout title="Inventaris Mitra">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
          <a-col>
            <partner-add-inventory
              @submit="addToInventories"
            ></partner-add-inventory>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <a-table
          :columns="partnerInventoryColumns"
          :data-source="form"
          style="margin-top: 24px"
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
import partnerInventoryColumns from "../../../../config/table/partner-inventory";
import ContentLayout from "../../../../layouts/content-layout.vue";
import PartnerAddInventory from "./partner-add-inventory.vue";

export default {
  data() {
    return {
      partnerInventoryColumns,
      form: [],
    };
  },
  components: { ContentLayout, PartnerAddInventory, ContentLayout },
  methods: {
    deleteItem(index) {
      this.form.splice(index, 1);
    },
    addToInventories(value) {
      // console.log(this.form);
      this.form.push(value);
    },
  },
  watch: {
    form: {
      handler: function (value) {
        this.$emit("input", value);
      },
    },
  },
};
</script>
, DeleteButton
