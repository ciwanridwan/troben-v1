<template>
  <div>
    <a-button class="trawl-button-success" @click="onVisible">Ambil</a-button>
    <a-modal
      :visible="visible"
      :width="840"
      centered
      :closable="true"
      :mask-closable="true"
      @cancel="onCancel"
      :footer="null"
    >
      <template slot="closeIcon">
        <a-icon type="close" @click="onCancel"></a-icon>
      </template>
      <template slot="title">
        <h3><b>Assign Transporter</b></h3>
      </template>
      <a-row type="flex">
        <a-col :span="12" class="trawl-border-right" style="padding-right:24px">
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
        </a-col>
        <a-col :span="12">
          <a-layout>
            <a-layout-content class="trawl-bg-white">
              <!-- description -->
              <a-space direction="vertical">
                <div style="padding:0px 24px">
                  <h3>Pilih Transporter</h3>
                  <a-input-search v-model="search"> </a-input-search>
                </div>

                <a-divider></a-divider>

                <a-radio-group
                  style="padding:0px 24px"
                  v-model="chosenTransporter"
                >
                  <a-row type="flex" :gutter="[0, 24]">
                    <template v-for="transporter in items.data">
                      <a-col
                        v-for="(driver, index) in transporter.users"
                        :span="24"
                        :key="`transporter-` + index"
                      >
                        <a-card>
                          <a-row type="flex" :gutter="12" align="middle">
                            <a-col
                              :span="4"
                              :class="['trawl-assign-transporter--image']"
                            >
                              <a-empty :description="null"></a-empty>
                            </a-col>
                            <a-col
                              :span="18"
                              class="trawl-assign-transporter--title"
                            >
                              <h3>
                                {{ driver.name }}
                              </h3>
                              <h4 class="trawl-text-normal">
                                {{ transporter.type }} -
                                {{ transporter.registration_number }}
                              </h4>
                            </a-col>
                            <a-col :span="2" class="trawl-text-right">
                              <a-radio :value="driver.pivot.hash"> </a-radio>
                            </a-col>
                          </a-row>
                        </a-card>
                      </a-col>
                    </template>
                  </a-row>
                </a-radio-group>

                <a-divider></a-divider>
              </a-space>
            </a-layout-content>

            <a-layout-footer
              :class="['trawl-bg-white', 'trawl-assign-transporter--footer']"
              style="padding:24px"
            >
              <a-button
                @click="onOk"
                :class="['trawl-button-success']"
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
import { SendIcon, ReceiveIcon } from "../../../components/icons";
import AddressComponent from "../../../components/orders/address-component.vue";

export default {
  name: "ModalAssignTransporter",
  components: {
    SendIcon,
    ReceiveIcon,
    AddressComponent
  },
  props: {
    order: {
      type: Object,
      default: () => ({})
    },
    items: {
      type: Object,
      default: () => {}
    },
    getTransporters: {
      type: Function,
      default: () => {}
    },
    save: {
      type: Function,
      default: () => {}
    }
  },
  data() {
    return {
      visible: false,
      loading: false,
      search: null,
      iconSize: 4,
      chosenTransporter: null
    };
  },
  computed: {
    package() {
      return this.order?.packages[0];
    },
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
      return this.order?.hash && this.chosenTransporter;
    }
  },
  watch: {
    search: {
      immediate: false,
      handler() {
        this.getTransporters(this.search, this.package.transporter_type);
      }
    }
  },
  methods: {
    onOk() {
      this.save({
        delivery_hash: this.order.hash,
        userable_hash: this.chosenTransporter
      });
      this.onCancel();
    },
    onCancel() {
      this.$emit("cancel");
      this.visible = false;
    },
    onVisible() {
      this.visible = true;
      this.search = null;
      this.getTransporters(null, this.package.transporter_type);
    }
  }
};
</script>
