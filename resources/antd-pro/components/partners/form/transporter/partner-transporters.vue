<template>
  <div>
    <content-layout title="Armada">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
          <a-col>
            <partner-add-transporter-form
              :types="transporterTypes"
              @submit="addToTransporters"
            ></partner-add-transporter-form>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <a-table
          :columns="partnerTransporterColumns"
          :data-source="form"
          style="margin-top: 24px"
        >
          <span slot="number" slot-scope="item, record, index">
            {{ index + 1 }}
          </span>
          <span slot="detail" slot-scope="record">
            <a-row type="flex">
              <a-col :span="24">
                <span>Tahun Pembuatan {{ record.production_year }}</span>
              </a-col>
              <a-col :span="24">
                <span
                  >{{ record.length }}cm x {{ record.width }}cm x
                  {{ record.height }}cm</span
                >
              </a-col>
            </a-row>
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
import partnerTransporterColumns from "../../../../config/table/partner-transporter";
import ContentLayout from "../../../../layouts/content-layout.vue";
import PartnerAddTransporterForm from "./partner-add-transporter-form.vue";
export default {
  components: { ContentLayout, PartnerAddTransporterForm, ContentLayout },
  data() {
    return {
      form: [],
      partnerTransporterColumns
    };
  },
  methods: {
    deleteItem(index) {
      this.form.splice(index, 1);
    },
    addToTransporters(value) {
      this.form.push(value);
    }
  },
  props: {
    transporterTypes: {
      type: Array,
      default: []
    }
  },
  watch: {
    form: {
      handler: function(value) {
        this.$emit("input", value);
      }
    }
  }
};
</script>
