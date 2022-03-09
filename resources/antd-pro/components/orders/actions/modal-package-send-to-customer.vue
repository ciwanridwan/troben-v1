<template>
  <order-modal
    :package="package"
    @change="onChange"
    :modifiable="modifiable"
    v-model="visible"
  >
    <template slot="trigger">
      <a-button type="success" class="trawl-button-success">
        {{ triggerText }}
      </a-button>
    </template>
    <template slot="footer">
      <a-row type="flex" justify="end">
        <a-col :span="12">
          <a-button
            type="success"
            class="trawl-button-success"
            block
            @click="onSubmit"
          >
            Kirim ke Pelanggan
          </a-button>
        </a-col>
      </a-row>
    </template>
  </order-modal>
</template>
<script>
import orderModal from "../modal/order-modal.vue";
import { STATUS_PACKED, STATUS_REVAMP } from "../../../data/packageStatus";

export default {
  components: { orderModal },
  props: ["package", "modifiable"],
  data() {
    return {
      visible: false,
    };
  },
  computed: {
    triggerText() {
      let status = this.package?.status;
      switch (status) {
        case STATUS_PACKED:
          return "Cek";

        case STATUS_REVAMP:
          return "Lihat";

        default:
          return "Cek";
      }
    },
  },
  methods: {
    onChange() {
      this.$emit("change");
    },
    onSubmit() {
      let url = this.routeUri("partner.cashier.home.packageChecked", {
        package_hash: this.package?.hash,
      });

      if (localStorage.getItem("getDiscount") > 0) {
        url += `?discount=${localStorage.getItem("getDiscount")}`;
      }
      this.$http
        .patch(url)
        .then(() => {
          this.$notification.success({
            message: "Berhasil kirim ke customer",
          });
          this.visible = false;
          this.$emit("submit");
          localStorage.removeItem("getDiscount");
        })
        .catch((error) => {
          this.onErrorResponse(error);
          localStorage.removeItem("getDiscount");
        });
    },
  },
};
</script>
