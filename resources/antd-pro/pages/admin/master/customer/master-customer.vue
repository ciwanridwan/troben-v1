<template>
  <div>
    <content-layout title="Data Customer" :pagination="trawlbensPagination">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
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
          :columns="customerColumns"
          :data-source="items.data"
          :pagination="trawlbensPagination"
          @change="handleTableChanged"
          :loading="loading"
          :class="['trawl']"
        >
          <span slot="number" slot-scope="number">{{ number }}</span>
          <span slot="action" slot-scope="record">
            <a-space>
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
import customerColumns from "../../../../config/table/customer";
import ContentLayout from "../../../../layouts/content-layout.vue";

export default {
  name: "customer-list",
  components: {
    DeleteButton,
    ContentLayout
  },
  created() {
    this.items = this.getDefaultPagination();
    this.getItems();
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    customerColumns
  }),
  methods: {
    deleteConfirm(record) {
      this.$confirm({
        content:
          "Apakah kamu yaking ingin menghapus data customer " +
          record.name +
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
    }
  }
};
</script>

<style scoped></style>
