<template>
  <div>
    <a-button type="primary" class="trawl-button-success" @click="showModal"
      >Assign Mitra</a-button
    >
    <a-modal
      :visible="visible"
      @cancel="onCancel"
      :width="840"
      centered
      :closable="true"
      :mask-closable="true"
      :ok-button-props="{ props: { disabled: true } }"
    >
      <template slot="closeIcon"
        ><a-icon type="close" @click="onCancel"></a-icon
      ></template>
      <template slot="title">
        <h3><b>Assign Mitra</b></h3>
      </template>
      <a-row type="flex">
        <a-col :span="14" class="trawl-border-right" style="padding-right:24px">
          <a-space direction="vertical" :size="24">
            <!-- sender address -->
            <a-row type="flex">
              <a-col :span="iconSize">
                <send-icon></send-icon>
              </a-col>
              <a-col :span="leftSize">
                <address-component>
                  <b slot="people">{{ order.sender_name }}</b>
                  <h5 class="trawl-text-normal" slot="phone">
                    {{ order.sender_phone }}
                  </h5>
                  <p slot="address">
                    {{ order.sender_address }}
                  </p>
                </address-component>
              </a-col>
            </a-row>

            <!-- receiver address -->
            <a-row type="flex">
              <a-col :span="iconSize">
                <receive-icon></receive-icon>
              </a-col>
              <a-col :span="leftSize">
                <address-component :receiver="true">
                  <b slot="people">{{ order.receiver_name }}</b>
                  <h5 class="trawl-text-normal" slot="phone">
                    {{ order.receiver_phone }}
                  </h5>
                  <p slot="address">
                    {{ order.receiver_address }}
                  </p>
                </address-component>
              </a-col>
            </a-row>

            <a-row>
              <a-col :span="iconSize"> </a-col>
              <a-col :span="leftSize">
                <h5 class="trawl-text-normal">
                  Jumlah Kg: <b>{{ totalItemsWeight }} Kg</b>
                </h5>
              </a-col>
            </a-row>
          </a-space>
        </a-col>
        <a-col :span="10">
          <a-layout>
            <a-layout-content class="trawl-bg-white">
              <!-- description -->
              <a-space direction="vertical">
                <div style="padding:0px 24px">
                  <h3>Pilih Mitra</h3>
                  <a-input-search v-model="search"> </a-input-search>
                </div>

                <a-divider></a-divider>

                <a-radio-group style="padding:0px 24px" v-model="chosenPartner">
                  <a-space direction="vertical">
                    <a-card
                      v-for="(partner, index) in items.data"
                      :key="`partner-` + index"
                    >
                      <a-space direction="vertical">
                        <a-row type="flex">
                          <a-col :span="20">
                            <a-space direction="vertical">
                              <!-- PARTNER NAME -->
                              <h3 class="trawl-text-normal">
                                {{ partner.code }}
                              </h3>
                              <!-- OWNER NAME -->
                              <h3>
                                {{ partner.name }}
                              </h3>
                            </a-space>
                          </a-col>
                          <a-col :span="4" class="trawl-text-right">
                            <a-radio :value="partner.hash"> </a-radio>
                          </a-col>
                        </a-row>
                        <a-space align="start">
                          <home-icon></home-icon>
                          <p>
                            {{ partner.address }}
                          </p>
                        </a-space>
                      </a-space>
                    </a-card>
                  </a-space>
                </a-radio-group>

                <a-divider></a-divider>
              </a-space>
            </a-layout-content>
            <a-layout-footer class="trawl-bg-white" style="padding:24px">
              <a-button
                @click="onOk"
                class="trawl-button-success"
                block
                :disabled="!isReadyToSubmit"
                :loading="loading"
                >Tugaskan</a-button
              >
            </a-layout-footer>
          </a-layout>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
import editButton from "./button/edit-button.vue";
import { SendIcon, ReceiveIcon, HomeIcon } from "./icons";

import AddressComponent from "./orders/address-component.vue";
import OrderEstimation from "./orders/order-estimation.vue";
import _ from "lodash";

export default {
  name: "ModalAssignTransporter",
  components: {
    editButton,
    SendIcon,
    ReceiveIcon,
    AddressComponent,
    OrderEstimation,
    HomeIcon
  },
  props: {
    order: {
      type: Object,
      default: () => ({})
    },
    afterAssign: {
      type: Function,
      default: () => ({})
    }
  },
  data() {
    return {
      loading: false,
      search: null,
      iconSize: 4,
      chosenPartner: null,
      visible: false,
      items: this.getDefaultPagination()
    };
  },
  computed: {
    leftSize() {
      return 24 - this.iconSize;
    },
    totalItemsWeight() {
      return (
        this.order?.items?.reduce((borrow, item) => {
          return borrow + item.weight;
        }, 0) ?? 0
      );
    },
    isReadyToSubmit() {
      return this.order?.hash && this.chosenPartner;
    }
  },
  watch: {
    search: {
      immediate: false,
      handler() {
        this.getPartners();
      }
    }
  },
  methods: {
    showModal() {
      this.visible = true;
      this.chosenPartner = null;
      this.getPartners();
    },
    onOk() {
      this.save().then(() => {
        this.onCancel();
        this.afterAssign();
      });
    },
    onCancel() {
      this.visible = false;
    },
    getPartners: _.debounce(async function() {
      this.loading = true;
      const { data } = await this.$http.get(this.routeUri(this.getRoute()), {
        params: {
          partner: true,
          transporter_type: this.order.transporter_type,
          per_page: 2,
          q: this.search
        }
      });
      this.items = data;
      this.loading = false;
    }),
    async save() {
      this.loading = true;

      try {
        const response = await this.$http.patch(
          this.routeUri("admin.home.assign", {
            package_hash: this.order.hash,
            partner_hash: this.chosenPartner
          })
        );

        this.$notification.success({
          message: "Sukses menugaskan mitra!"
        });

        return response;
      } catch (e) {
        this.onErrorResponse(e);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
