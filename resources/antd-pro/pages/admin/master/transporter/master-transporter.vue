<template>
  <div>
    <content-layout>
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
          :columns="transporterColumns"
          :data-source="items.data"
          :pagination="trawlbensPagination"
          @change="handleTableChanged"
          :loading="loading"
          :class="['trawl']"
        >
          <span slot="number" slot-scope="number">{{ number }}</span>

          <span slot="dimension" slot-scope="record">
            {{ record.type.length }} x {{ record.type.width }} x
            {{ record.type.height }}
          </span>

          <span slot="action" slot-scope="record">
            <a-space>
              <a-button
                type="link"
                :class="['text-success']"
                icon="check"
                @click="onApprove(record, true)"
              >
                Approve
              </a-button>
              <a-button
                type="link"
                :class="['text-danger']"
                icon="close"
                @click="onApprove(record, false)"
                >Reject</a-button
              >
            </a-space>
          </span>
        </a-table>
      </template>
    </content-layout>
  </div>
</template>

<script>
import DeleteButton from "../../../../components/button/delete-button.vue";
import transporterColumns from "../../../../config/table/transporter";
import ContentLayout from "../../../../layouts/content-layout.vue";

export default {
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
    transporterColumns
  }),
  methods: {
    onApprove(record, value) {
      let url = this.routeUri(this.getRoute()) + "/" + record.hash;

      this.$http
        .patch(url, { is_verified: value })
        .then(resp => this.onSuccessApproved(resp))
        .catch(err => this.onErrorResponse(err));
    },
    onSuccessApproved(response) {
      this.getItems();
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
