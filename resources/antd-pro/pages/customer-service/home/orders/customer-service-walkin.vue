<template>
  <content-layout-footer>
    <template slot="content">
      <a-form-model ref="formRules" :model="form" :rules="rules">
        <a-tabs v-model="current" :tabBarStyle="{ display: 'none' }">
          <a-tab-pane :key="1">
            <a-card :style="{ 'margin-bottom': '3rem' }">
              <a-form-model-item prop="steps.0.valid">
                <order-receiver-form-step-1
                  ref="stepForm1"
                  v-model="form.steps[0]"
                />
              </a-form-model-item>
            </a-card>
          </a-tab-pane>
          <a-tab-pane :key="2">
            <a-card :style="{ 'margin-bottom': '3rem' }">
              <a-form-model-item prop="eula">
                <order-receiver-form-step-2
                  ref="stepForm2"
                  v-model="form.steps[1]"
                />
                <a-checkbox v-model="form.eula">
                  <a-space size="large">
                    <span>Saya menyetujui semua ketentuan yang berlaku </span>
                    <a-icon :component="InformationCircleIcon" />
                  </a-space>
                </a-checkbox>
              </a-form-model-item>
            </a-card>
          </a-tab-pane>
          <a-tab-pane :key="3">
            <a-card :style="{ 'margin-bottom': '3rem' }">
              <order-receiver-form-step-3
                ref="stepForm3"
                :receiverEdit="receiverEdit"
                :itemEdit="itemEdit"
                :data="calculateData"
                :loading="loading"
              />
            </a-card>
          </a-tab-pane>
        </a-tabs>
      </a-form-model>
    </template>

    <template slot="footer">
      <a-row type="flex">
        <a-col :span="10">
          <trawl-step-circle
            ref="trawlStep"
            :number="3"
            v-model="current"
            :beforeChange="validateStep"
          />
        </a-col>
        <a-col :span="10" class="trawl-text-right">
          <a-space>
            <a-button
              ghost
              class="trawl-button trawl-button-success--ghost"
              @click="prev"
              >Sebelumnya</a-button
            >
            <a-button
              v-if="current != 3"
              class="trawl-button-success"
              @click="next"
              >Selanjutnya</a-button
            >

            <a-button
              v-else
              class="trawl-button-success"
              @click="submit"
              :disabled="isActive"
              :loading="loading"
            >
              <trawl-modal-confirm
                v-model="submitModal"
                :cancelButton="false"
                :ok="redirectHome"
              >
                <template slot="text"
                  >Telah berhasil terkirim ke Customer</template
                >
              </trawl-modal-confirm>
              <trawl-modal-confirm
                v-model="rejectModal"
                :cancelButton="false"
                :ok="handleOk"
              >
                <template slot="text"
                  >Data tidak dapat terkirim, harap hubungi Customer
                  Service!!!</template
                >
              </trawl-modal-confirm>
              <!-- <a-modal v-model="rejectModal" @ok="handleOk">
                <template slot="text"
                  >Data tidak dapat terkirim, harap hubungi Customer
                  Service!!!</template
                >
              </a-modal> -->
              <a-space>
                <span>Kirim Ke cus</span>
                <a-icon :component="PackageIconSpark" />
              </a-space>
            </a-button>
          </a-space>
        </a-col>
      </a-row>
    </template>
  </content-layout-footer>
</template>
<script>
import OrderReceiverFormStep1 from "../../../../components/orders/walkin/order-receiver-forms/form-step-1.vue";
import OrderReceiverFormStep2 from "../../../../components/orders/walkin/order-receiver-forms/form-step-2.vue";
import OrderReceiverFormStep3 from "../../../../components/orders/walkin/order-receiver-forms/form-step-3.vue";
import TrawlStepCircle from "../../../../components/trawl-step-circle.vue";
import ContentLayoutFooter from "../../../../layouts/content-layout-footer.vue";
import {
  InformationCircleIcon,
  PackageIconSpark,
} from "../../../../components/icons";
import TrawlModalConfirm from "../../../../components/trawl-modal-confirm.vue";

export default {
  name: "customer-service-walkin",
  components: {
    OrderReceiverFormStep1,
    OrderReceiverFormStep2,
    OrderReceiverFormStep3,
    ContentLayoutFooter,
    TrawlStepCircle,
    TrawlModalConfirm,
  },
  data() {
    return {
      form: {
        steps: [{}, {}],
        eula: null,
      },
      rules: {
        "steps.0.valid": [{ required: true }],
        "steps.1.valid": [{ required: true }],
        eula: [{ required: true }],
      },
      calculateData: {},
      current: 1,
      InformationCircleIcon,
      PackageIconSpark,
      loading: false,
      submitModal: false,
      rejectModal: false,
      isActive: false,
    };
  },
  computed: {
    stepForms() {
      return ["stepForm1", "stepForm2"];
    },

    preparedStore() {
      let formdata = new FormData();
      Object.keys(this.preparedData).forEach((k) => {
        if (k != "photos") {
          formdata.append(k, JSON.stringify(this.preparedData[k]));
        }
      });
      this.preparedData?.photos?.forEach((o) =>
        formdata.append("photos[]", o.originFileObj)
      );
      return formdata;
    },

    preparedItems() {
      let items = [];
      this.preparedData.items.forEach((item) => {
        item.handling = item.handling_type;
        delete item.handling_type;
        items.push(item);
      });
      return items;
    },
    preparedData() {
      let form = {};
      this.form.steps.forEach((step) => {
        form = { ...form, ...step };
      });

      return form;
    },
  },
  methods: {
    async validateStep(toStep) {
      for (const ref of this.stepForms.slice(0, toStep - 1)) {
        let formValid = await this.$refs[ref].validate();
        if (!formValid) {
          return false;
        }
        if (ref == "stepForm2" && !this.form.eula) {
          return false;
        }
      }
      return true;
    },

    submit() {
      const config = { headers: { "Content-Type": "multipart/form-data" } };
      this.isActive = true;
      this.loading = true;
      this.$http
        .post(this.routeUri(this.getRoute()), this.preparedStore, config)
        .then(({ data }) => {
          this.submitModal = true;
          this.loading = false;
        })
        .catch((err) => {
          this.rejectModal = true;
        });
    },

    handleOk() {
      this.rejectModal = false;
    },

    prev() {
      this.$refs.trawlStep.prev();
    },
    next() {
      this.$refs.trawlStep.next();
    },

    itemEdit() {
      this.$refs.trawlStep.toStep(2);
    },
    receiverEdit() {
      this.$refs.trawlStep.toStep(1);
    },

    redirectHome() {
      window.location.href = this.routeUri("partner.customer_service.home");
    },

    async getGeo(status = "province", params = {}) {
      let result = {};
      await this.$http
        .get(this.routeUri("partner.customer_service.home.order.walkin.geo"), {
          params: {
            type: status,
            ...params,
          },
        })
        .then(({ data }) => {
          let datas = data.data;
          result = datas;
        });

      return result;
    },

    async getAddress(sub_district_id) {
      let address = "";
      await this.getGeo("sub_district", {
        id: sub_district_id,
      }).then((data) => {
        let sub_district = data[0];
        let list_address = [
          sub_district?.name,
          sub_district?.district?.name,
          sub_district?.regency?.name,
        ];
        address += list_address.join(" ");
        address += ", ";
        address += sub_district?.province?.name;
      });

      return address;
    },

    async calculate(params) {
      this.loading = true;
      let result = {};
      await this.$http
        .post(
          this.routeUri("partner.customer_service.home.order.walkin.calculate"),
          params
        )
        .then(({ data }) => {
          result = data;
        })
        .finally(() => (this.loading = false));
      return result;
    },
  },
  watch: {
    form: {
      handler: function (value) {},
      deep: true,
    },
    current: function () {
      if (this.current === 3) {
        if (this.preparedData) {
          let params = {
            destination_district_id: this.preparedData.destination_district_id,
            destination_province_id: this.preparedData.destination_province_id,
            destination_regency_id: this.preparedData.destination_regency_id,
            destination_sub_district_id:
              this.preparedData.destination_sub_district_id,
            items: this.preparedItems,
            service_code: this.preparedData.service_code,
          };
          this.calculate(params)
            .then(({ data }) => {
              this.calculateData = { ...this.form.steps[0], ...data };
            })
            .finally(() => {
              // set receiver address
              this.getAddress(
                this.preparedData?.destination_sub_district_id
              ).then((address) => {
                this.calculateData = {
                  ...this.calculateData,
                  receiver_address: `${address} \n ${this.preparedData.receiver_address}`,
                };
              });

              let partner = this.$laravel.user.partners[0];
              this.calculateData = {
                ...this.calculateData,
                sender_address: partner.address,
              };
            });
        }
      }
    },
  },
};
</script>
