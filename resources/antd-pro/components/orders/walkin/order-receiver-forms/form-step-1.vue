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
        <h3 class="trawl-text-bolder">Penerima</h3>
        <a-row type="flex">
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
            <a-form-model-item label="Kota / Kabupaten" prop="destination_regency_id">
              <a-select
                show-search
                v-model="form.destination_regency_id"
                size="large"
                placeholder="- pilih Kota / Kabupaten -"
                :filter-option="filterOptionMethod"
                @focus="
                  getGeo('regency', {
                    province_id: form.destination_province_id,
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
                    regency_id: form.destination_regency_id,
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
            <a-form-model-item label="Kelurahan" prop="destination_sub_district_id">
              <a-select
                show-search
                v-model="form.destination_sub_district_id"
                size="large"
                placeholder="- pilih Kelurahan -"
                :filter-option="filterOptionMethod"
                @focus="
                  getGeo('sub_district', {
                    district_id: form.destination_district_id,
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
              <a-input size="large" v-model="form.destination_zip_code" disabled>
              </a-input>
            </a-form-model-item>
          </a-col>
        </a-row>

        <a-row type="flex" :gutter="[12, 12]">
          <a-col :span="12">
            <a-form-model-item label="Alamat Lengkap" prop="destination_address">
              <a-textarea
                rows="5"
                type="textarea"
                size="large"
                v-model="form.destination_address"
              >
              </a-textarea>
            </a-form-model-item>
          </a-col>
        </a-row>
      </div>
      <div>
        <h3 class="trawl-text-bolder">Metode Pengiriman</h3>
        <a-form-model-item prop="service_type">
          <a-radio-group v-model="form.service_type">
            <trawl-radio-button
              v-for="(service, index) in services"
              :key="index"
              :value="'test'"
            >
              <template slot="icon">
                <a-icon :component="service.icon"></a-icon>
              </template>
              {{ service.title }}
            </trawl-radio-button>
          </a-radio-group>
        </a-form-model-item>
      </div>
    </a-space>
  </a-form-model>
</template>
<script>
import { RC_OUT_OF_RANGE } from "../../../../data/response";
import { getMessageByCode } from "../../../../functions/response";
import trawlRadioButton from "../../../trawl-radio-button.vue";
import { services } from "../../../../data/services";
export default {
  components: { trawlRadioButton },
  data() {
    return {
      services,

      provinces: [],
      regencies: [],
      districts: [],
      subDistricts: [],

      loading: false,

      form: {
        receiver_name: null,
        destination_province_id: null,
        destination_regency_id: null,
        destination_district_id: null,
        destination_sub_district_id: null,
        destination_zip_code: null,
        destination_address: null,
        service_type: null,
      },
      rules: {
        receiver_name: [{ required: true }],
        destination_province_id: [{ required: true }],
        destination_regency_id: [{ required: true }],
        destination_district_id: [{ required: true }],
        destination_sub_district_id: [{ required: true }],
        destination_zip_code: null,
        destination_address: [{ required: true }],
        service_type: [{ required: true }],
      },
      valid: false,
    };
  },
  methods: {
    async validate() {
      this.valid = true;
      await this.$refs.formRules
        .validate()
        ?.then((value) => {
          if (!value) {
            this.valid = false;
          }
        })
        .catch((error) => {
          this.valid = false;
        });
      await this.checkAvailableShipping().then((value) => {
        if (!value) {
          this.valid = false;
        }
      });
      return this.valid;
    },
    async getGeo(status = "province", params = {}) {
      this.loading = true;
      this.$http
        .get(this.routeUri("partner.customer_service.order.walkin.geo"), {
          params: {
            type: status,
            ...params,
          },
        })
        .then(({ data }) => {
          let datas = data.data;
          this.putGeoToData(status, datas);
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
            destination_id: this.form.destination_sub_district_id,
          },
        })
        .catch((error) => {
          const { data } = error.response;

          let responseMessage = getMessageByCode(data.code);
          if ((data.code = RC_OUT_OF_RANGE)) {
            this.$notification.error({
              message: responseMessage.message,
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
        (o) => o.id === this.form.destination_sub_district_id
      );
      this.form.destination_zip_code = subDistrict.zip_code;
      this.checkAvailableShipping();
    },
  },
  watch: {
    form: {
      handler: function (value) {
        this.$emit("change", { ...value, valid: this.valid });
        this.$emit("input", { ...value, valid: this.valid });
      },
      deep: true,
    },
  },
};
</script>
