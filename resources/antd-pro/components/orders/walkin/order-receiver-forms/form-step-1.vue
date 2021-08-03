<template>
  <a-form-model
    ref="formRules"
    :model="form"
    :rules="rules"
    :hideRequiredMark="true"
    layout="vertical"
  >
    <a-space direction="vertical" :style="{ width: '100%' }">
      <div>
        <h3 class="trawl-text-bolder">Pengirim</h3>
        <a-form-model-item prop="customer_hash"></a-form-model-item>

        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="8">
            <a-form-model-item
              label="Apakah Customer Memiliki Akun TrawlBens?"
              prop="android"
            >
              <a-radio-group v-model="value" @change="onChange">
                <a-radio :value="true">
                  Ya, memiliki
                </a-radio>
                <a-radio :value="false">
                  Tidak
                </a-radio>
              </a-radio-group>
            </a-form-model-item>
          </a-col>
        </a-row>

        <div v-if="!value">
          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="6">
              <a-form-model-item label="Nomor HP Mitra" prop="mitra_phone">
                <a-input-search
                  size="large"
                  v-model="form.sender_phone_mitra"
                  placeholder="Nomor HP Mitra"
                  @search="getCustomerByPhoneMitra"
                ></a-input-search>
              </a-form-model-item>
            </a-col>
            <a-col :span="6">
              <a-form-model-item label="Nama Mitra" prop="mitra_name">
                <a-input
                  size="large"
                  v-model="form.sender_name_mitra"
                  placeholder="Nama Mitra"
                ></a-input>
              </a-form-model-item>
            </a-col>
          </a-row>
          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="6">
              <a-form-model-item
                label="Nomor Hp Pengirim"
                prop="sender_phone_2"
              >
                <a-input
                  size="large"
                  v-model="form.sender_phone"
                  placeholder="Nomor Hp Pengirim"
                ></a-input>
              </a-form-model-item>
            </a-col>
            <a-col :span="6">
              <a-form-model-item label="Nama Pengirim" prop="sender_name_2">
                <a-input
                  size="large"
                  v-model="form.sender_name"
                  placeholder="Nama Pengirim"
                ></a-input>
              </a-form-model-item>
            </a-col>
          </a-row>
        </div>

        <div v-if="value">
          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="6">
              <a-form-model-item label="Nomor Hp Pengirim" prop="sender_phone">
                <a-input-search
                  size="large"
                  v-model="form.sender_phone"
                  placeholder="Nomor Hp Pengirim"
                  @search="getCustomerByPhone"
                ></a-input-search>
              </a-form-model-item>
            </a-col>
            <a-col :span="6">
              <a-form-model-item label="Nama Pengirim" prop="sender_name">
                <a-input
                  size="large"
                  v-model="form.sender_name"
                  placeholder="Nama Pengirim"
                ></a-input>
              </a-form-model-item>
            </a-col>
          </a-row>
        </div>
      </div>

      <div>
        <h3 class="trawl-text-bolder">Penerima</h3>
        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="6">
            <a-form-model-item label="Nomor Hp Penerima" prop="receiver_phone">
              <a-input
                size="large"
                v-model="form.receiver_phone"
                placeholder="Nomor Hp Penerima"
              ></a-input>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item label="Nama Penerima" prop="receiver_name">
              <a-input
                size="large"
                v-model="form.receiver_name"
                placeholder="Nama Penerima"
              ></a-input>
            </a-form-model-item>
          </a-col>
        </a-row>
      </div>

      <div>
        <h3 class="trawl-text-bolder">Lokasi Penerima</h3>
        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="6">
            <a-form-model-item label="Provinsi" prop="destination_province_id">
              <a-select
                show-search
                @focus="getGeo('province')"
                v-model="form.destination_province_id"
                size="large"
                placeholder="- pilih provinsi -"
                :filter-option="filterOptionMethod"
                :loading="loading"
              >
                <a-select-option
                  v-for="(province, index) in provinces"
                  :key="index"
                  :value="province.id"
                >
                  {{ province.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item
              label="Kota / Kabupaten"
              prop="destination_regency_id"
            >
              <a-select
                show-search
                v-model="form.destination_regency_id"
                size="large"
                placeholder="- pilih Kota / Kabupaten -"
                :filter-option="filterOptionMethod"
                @focus="
                  getGeo('regency', {
                    province_id: form.destination_province_id
                  })
                "
                :loading="loading"
              >
                <a-select-option
                  v-for="(regency, index) in regencies"
                  :key="index"
                  :value="regency.id"
                >
                  {{ regency.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item label="Kecamatan" prop="destination_district_id">
              <a-select
                show-search
                v-model="form.destination_district_id"
                size="large"
                placeholder="- pilih Kecamatan -"
                :filter-option="filterOptionMethod"
                @focus="
                  getGeo('district', {
                    regency_id: form.destination_regency_id
                  })
                "
                :loading="loading"
              >
                <a-select-option
                  v-for="(district, index) in districts"
                  :key="index"
                  :value="district.id"
                >
                  {{ district.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
        </a-row>
        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="6">
            <a-form-model-item
              label="Kelurahan"
              prop="destination_sub_district_id"
            >
              <a-select
                show-search
                v-model="form.destination_sub_district_id"
                size="large"
                placeholder="- pilih Kelurahan -"
                :filter-option="filterOptionMethod"
                @focus="
                  getGeo('sub_district', {
                    district_id: form.destination_district_id
                  })
                "
                @change="setZipCode"
                :loading="loading"
              >
                <a-select-option
                  v-for="(subDistrict, index) in subDistricts"
                  :key="index"
                  :value="subDistrict.id"
                >
                  {{ subDistrict.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="6">
            <a-form-model-item label="Kode Pos" prop="destination_zip_code">
              <a-input
                size="large"
                v-model="form.destination_zip_code"
                disabled
              >
              </a-input>
            </a-form-model-item>
          </a-col>
        </a-row>

        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="12">
            <a-form-model-item label="Alamat Lengkap" prop="receiver_address">
              <a-textarea
                rows="5"
                type="textarea"
                size="large"
                v-model="form.receiver_address"
              >
              </a-textarea>
            </a-form-model-item>
          </a-col>
        </a-row>
      </div>
      <div>
        <h3 class="trawl-text-bolder">Metode Pengiriman</h3>
        <a-form-model-item prop="service_code">
          <service-radio-group
            v-model="form.service_code"
            :available-services="services"
          />
        </a-form-model-item>
      </div>
    </a-space>
  </a-form-model>
</template>
<script>
import {
  RC_OUT_OF_RANGE,
  RC_INVALID_DATA,
  RC_INVALID_PHONE_NUMBER
} from "../../../../data/response";
import { getMessageByCode } from "../../../../functions/response";
import trawlRadioButton from "../../../radio-buttons/trawl-radio-button";

import ServiceRadioGroup from "../../../radio-buttons/service-radio-group.vue";
export default {
  components: { trawlRadioButton, ServiceRadioGroup },
  data() {
    return {
      value: true,
      services: [],
      listOfService: [],

      provinces: [],
      regencies: [],
      districts: [],
      subDistricts: [],

      loading: false,

      form: {
        customer_hash: null,
        sender_phone: null,
        sender_phone_mitra: null,
        sender_address: null,
        sender_name: null,
        sender_name_mitra: null,
        receiver_phone: null,
        receiver_address: null,
        receiver_name: null,
        destination_province_id: null,
        destination_regency_id: null,
        destination_district_id: null,
        destination_sub_district_id: null,
        destination_zip_code: null,
        service_code: null
      },
      rules: {
        customer_hash: [{ required: true, message: "sender phone required" }],
        sender_phone: [{ required: true }],
        sender_phone_mitra: [{ required: true }],
        sender_name: [{ required: true }],
        sender_name_mitra: [{ required: true }],
        sender_address: [{ required: true }],
        receiver_phone: [{ required: true }],
        receiver_name: [{ required: true }],
        receiver_address: [{ required: true }],
        destination_province_id: [{ required: false }],
        destination_regency_id: [{ required: false }],
        destination_district_id: [{ required: false }],
        destination_sub_district_id: [{ required: false }],
        destination_zip_code: null,
        service_code: [{ required: false }]
      },
      valid: false
    };
  },
  methods: {
    onChange(e) {
      console.log("radio checked", e.target.value);
    },
    async validate() {
      this.valid = true;
      await this.$refs.formRules
        .validate()
        ?.then(value => {
          if (!value) {
            this.valid = false;
          }
        })
        .catch(error => {
          this.valid = false;
        });
      await this.checkAvailableShipping().then(value => {
        if (!value) {
          this.valid = false;
        }
      });
      return this.valid;
    },
    async getGeo(status = "province", params = {}) {
      this.loading = true;
      params = { per_page: "-1", ...params };
      this.$http
        .get(this.routeUri("partner.customer_service.home.order.walkin.geo"), {
          params: {
            type: status,
            ...params
          }
        })
        .then(({ data }) => {
          let datas = data.data;
          this.putGeoToData(status, datas);
        })
        .finally(() => (this.loading = false));
    },
    async getService() {
      this.loading = true;
      this.$http
        .get(
          this.routeUri("partner.customer_service.home.order.walkin.service")
        )
        .then(({ data }) => {
          let datas = data.data;
          this.services = datas;
        })
        .finally(() => (this.loading = false));
    },
    putGeoToData(status, data) {
      switch (status) {
        case "province":
          this.provinces = data;
          break;
        case "regency":
          this.regencies = data;
          break;
        case "district":
          this.districts = data;
          break;
        case "sub_district":
          this.subDistricts = data;
          break;
      }
    },
    async checkAvailableShipping() {
      let available = true;
      await this.$http
        .get(this.routeUri(this.getRoute()), {
          params: {
            check: true,
            destination_id: this.form.destination_sub_district_id
          }
        })
        .catch(error => {
          const { data } = error.response;

          let responseMessage = getMessageByCode(data.code);
          if ((data.code = RC_OUT_OF_RANGE)) {
            this.$notification.error({
              message: responseMessage?.message
            });
          } else {
            this.onErrorResponse(error);
          }
          available = false;
        });
      return available;
    },
    setZipCode() {
      let subDistrict = this.subDistricts.find(
        o => o.id === this.form.destination_sub_district_id
      );
      this.form.destination_zip_code = subDistrict.zip_code;
      this.checkAvailableShipping();
    },
    getCustomerByPhone() {
      this.$http
        .get(
          this.routeUri("partner.customer_service.home.order.walkin.customer"),
          {
            params: {
              phone: this.form.sender_phone
            }
          }
        )
        .then(({ data }) => {
          let customer = data.data;

          this.form.customer_hash = customer.hash;
          this.form.sender_name = customer.name;
        })
        .catch(error => {
          let code = error.response.data.code;
          let responseMessage = getMessageByCode(code);
          this.$notification.error({
            message: responseMessage?.message
          });
        });
    },
    getCustomerByPhoneMitra() {
      this.$http
        .get(
          this.routeUri("partner.customer_service.home.order.walkin.customer"),
          {
            params: {
              phone: this.form.sender_phone_mitra
            }
          }
        )
        .then(({ data }) => {
          let customer = data.data;

          this.form.customer_hash = customer.hash;
          this.form.sender_name_mitra = customer.name;
        })
        .catch(error => {
          let code = error.response.data.code;
          let responseMessage = getMessageByCode(code);
          this.$notification.error({
            message: responseMessage?.message
          });
        });
    }
  },

  watch: {
    form: {
      handler: function(value) {
        this.$emit("change", { ...value, valid: this.valid });
        this.$emit("input", { ...value, valid: this.valid });
      },
      deep: true
    }
  },
  mounted() {
    this.getService();
  }
};
</script>
