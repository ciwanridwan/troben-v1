<template>
  <div>
    <order-modal-row-layout :alignItem="{ 'align-items': 'center' }">
      <template slot="icon">
        <trawl-icon size="3" />
      </template>
      <template slot="content">
        <a-space direction="vertical">
          <span class="trawl-text-bolder">Mitra Businnes</span>
          <span class="trawl-text-bolder"> {{ partner_code }} </span>
          <span>{{ dateSimpleFormat(created_at) }}</span>
        </a-space>
      </template>
      <template slot="addon">
        <div
          style="
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 1rem;
            font-style: normal;
            font-weight: 500;
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            align-items: center;
          "
        >
          {{ code }}
          <div
            style="
              background: rgba(61, 136, 36, 0.25);
              width: 113px;
              height: 30px;
              padding: 4px 23px;
            "
          >
            {{ service_code }}
          </div>
        </div>
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <send-icon />
      </template>
      <template slot="content">
        <package-address :package="package" type="sender" />
      </template>
    </order-modal-row-layout>

    <order-modal-row-layout :afterLine="false">
      <template slot="icon">
        <receive-icon />
      </template>
      <template slot="content">
        <a-space direction="vertical" :size="1">
          <package-address :package="package" type="receiver" />
        </a-space>
      </template>
      <!-- <template slot="addon">
        <a-empty v-if="package.attachments[0] == null" />
        <div
          v-else
          style="
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 1rem;
          "
        >
          <enlargeable-image
            style="width: 50px !important"
            v-for="(data, index) in URIImage"
            :key="index"
            :src="data.uri"
          />
        </div>
      </template> -->
    </order-modal-row-layout>
    <div class="estimation">
      <order-estimation v-if="this.price" :price="this.price" />
    </div>
  </div>
</template>
<script>
import { TrawlIcon, SendIcon, ReceiveIcon } from "../../icons";
import packageAddress from "../../packages/package-address.vue";
import EnlargeableImage from "@diracleo/vue-enlargeable-image";
export default {
  components: {
    EnlargeableImage,
    packageAddress,
    TrawlIcon,
    SendIcon,
    ReceiveIcon,
  },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
    price: {
      type: Object,
      default: () => null,
    },
  },
  data() {
    return {
      EnlargeableImage,
      URIImage,
    };
  },
  computed: {
    partner_code() {
      return this.package?.deliveries[0]?.partner.code;
    },
    service_code() {
      if (this.package?.service_code == "tpx") {
        return "Express";
      }
      if (this.package?.service_code == "tps") {
        return "REG";
      }
    },
    code() {
      return this.package?.code?.content;
    },
    created_at() {
      if (this.package?.attachments[0] == null) {
        this.URIImage = null;
      } else {
        this.URIImage = this.package?.attachments;
      }
      return this.package?.created_at;
    },
    imagePacking() {
      return this.package?.attachments[0];
    },
  },
};
</script>
<style scoped>
.estimation {
  background: rgba(61, 136, 36, 0.25);
  border: 1px solid #3d8824;
  border-radius: 10px;
  width: -webkit-fill-available;
  height: 81px;
  margin-left: 25px;
  padding: 18px 0px 0px 25px;
  margin-top: 35px;
  margin-bottom: 20px;
  margin-right: 10px;
}
</style>
