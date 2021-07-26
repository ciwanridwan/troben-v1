<template>
  <div>
    <content-layout>
      <template slot="head-tools">
        <a-row type="flex" justify="end" :gutter="10">
          <a-col>
            <pricing-form :data_extra="data_extra"></pricing-form>
          </a-col>
          <a-col>
            <input-file></input-file>
          </a-col>
          <a-col>
            <a-input-search
              v-model="filter.q"
              @search="getItems"
            ></a-input-search>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <!-- table -->
        <a-table
          :columns="pricingColumns"
          :data-source="items.data"
          :pagination="trawlbensPagination"
          @change="handleTableChanged"
          :loading="loading"
          :class="['trawl']"
        >
          <span slot="number" slot-scope="number">{{ number }}</span>
          <span slot="from_to" slot-scope="record">
            <a-row type="flex" align="middle" :class="['trawl-timeline']">
              <a-col>
                <a-timeline>
                  <a-timeline-item color="green">
                    <span v-if="record.origin_district">{{
                      record.origin_district.name
                    }}</span>
                    <span v-if="record.origin_regency">{{
                      record.origin_regency.name
                    }}</span>
                    <span v-if="record.origin_province">{{
                      record.origin_province.name
                    }}</span>
                  </a-timeline-item>
                  <a-timeline-item color="green">
                    <span v-if="record.destination">{{
                      record.destination.name
                    }}</span>
                    <span v-if="record.destination.notes"
                      >, {{ record.destination.notes }}</span
                    >
                  </a-timeline-item>
                </a-timeline>
              </a-col>
            </a-row>
          </span>
          <span slot="action" slot-scope="record">
            <a-space>
              <pricing-form
                :data="record"
                :data_extra="data_extra"
                method="PATCH"
              >
                <template slot="trigger">
                  <edit-button></edit-button>
                </template>
              </pricing-form>
              <delete-button @click="deleteConfirm(record)"></delete-button>
            </a-space>
          </span>
        </a-table>
      </template>
    </content-layout>
  </div>
</template>

<script>
import DeleteButton from "../../../../components/button/delete-button.vue";
import EditButton from "../../../../components/button/edit-button.vue";
import pricingColumns from "../../../../config/table/pricing";
import ContentLayout from "../../../../layouts/content-layout.vue";
import InputFile from "./input-file";
import PricingForm from "./pricing-form.vue";

export default {
  components: {
    DeleteButton,
    ContentLayout,
    InputFile,
    PricingForm,
    EditButton
  },
  InputFilereated() {
    this.items = this.getDefaultPagination();
    this.getItems();
  },
  data: () => ({
    data_extra: {},
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    pricingColumns
  }),
  methods: {
    deleteConfirm(record) {
      this.$confirm({
        content:
          "Apakah kamu yaking ingin menghapus data Ongkir " +
          record.origin_province.name +
          " - " +
          record.destination.name +
          "?",
        okText: "Ya",
        cancelText: "Batal",
        onOk: () => {
          this.deleteItem(record);
        }
      });
    },
    onSuccessResponse(response) {
      this.items = response;
      this.data_extra = { ...response.data_extra };

      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
    },
    onErrorResponse(error) {
      this.$notification.error({
        message: error.response.data.message
      });
    },
    handleTableChanged(pagination) {
      this.filter.page = pagination.current;
      this.filter.per_page = pagination.pageSize;

      this.getItems();
    },
    popupModal() {
      this.uploadModal = !this.uploadModal;
    }
  },
  created() {
    this.getItems();
  }
};
</script>
