<template>
  <a-form-model
    ref="formModel"
    layout="vertical"
    :model="form"
    :rules="rules"
    :hideRequiredMark="true"
  >
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="12">
        <a-form-model-item label="Tipe Status" prop="statusType">
          <a-select v-model="form.statusType" placeholder="Tipe Status">
            <a-select-option
              v-for="(statusType, index) in trackingStatusTypes"
              :key="index"
              :value="statusType.type"
              >{{ statusType.title }}</a-select-option
            >
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="12">
        <a-form-model-item label="Tipe Tracking" prop="type">
          <a-select v-model="form.type" placeholder="Tipe Tracking">
            <a-select-option
              v-for="(trackingType, index) in trackingTypes"
              :key="index"
              :value="trackingType.type"
              >{{ trackingType.title }}</a-select-option
            >
          </a-select>
        </a-form-model-item>
      </a-col>

      <a-col v-if="types" :span="12">
        <a-form-model-item label="Tipe Tracking Manifest" prop="deliveryType">
          <a-select placeholder="Tipe Tracking Manifest" v-model="form.deliveryType">
            <a-select-option
              v-for="(type, index) in types"
              :key="index"
              :value="type.type"
              >{{ type.title }}</a-select-option
            >
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="12">
        <a-form-model-item label="Tracking Status" prop="status">
          <a-select placeholder="Tracking Status" v-model="form.status">
            <a-select-option
              v-for="(trackingStatus, index) in statuses"
              :key="index"
              :value="trackingStatus.status"
              >{{ trackingStatus.title }}</a-select-option
            >
          </a-select>
        </a-form-model-item>
      </a-col>

      <a-col :span="24">
        <a-form-model-item label="Oleh" prop="partner">
          <a-select
            show-search
            placeholder="Oleh"
            v-model="form.partner"
            :show-arrow="false"
            :filter-option="false"
            :not-found-content="null"
            @search="getPartners"
          >
            <a-select-option
              v-for="(partner, index) in partners.data"
              :key="index"
              :value="partner.hash"
              >{{ partner.code }} - {{ partner.name }}</a-select-option
            >
          </a-select>
        </a-form-model-item>
      </a-col>
      <a-col :span="24">
        <a-form-model-item label="Deskripsi Tracking" prop="description">
          <a-textarea
            type="textarea"
            v-model="form.description"
            placeholder="Deskripsi Tracking"
          />
        </a-form-model-item>
      </a-col>
    </a-row>
  </a-form-model>
</template>
<script>
import {
  trackingTypes,
  trackingStatusTypes,
  trackingStatuses,
} from "../../data/manualTracking";
export default {
  data() {
    return {
      form: {
        type: null,
        deliveryType: null,
        statusType: null,
        status: null,
        partner: null,
        description: null,
        desc: null,
      },
      rules: {
        type: [{ required: true }],
        status: [{ required: true }],
        partner: [{ required: true }],
        deliveryType: [{ required: true }],
        description: [{ required: true }],
      },
      partners: [],
      trackingTypes,
      trackingStatuses,
      trackingStatusTypes,
    };
  },
  methods: {
    getPartners: _.debounce(async function (value) {
      this.loading = true;
      const { data } = await this.$http.get(this.routeUri(this.getRoute()), {
        params: {
          partner: true,
          per_page: 10,
          q: value,
        },
      });
      this.partners = data;
      this.loading = false;
    }),
  },
  computed: {
    statuses() {
      return this.form.statusType
        ? this.trackingStatuses[this.form.statusType].statuses
        : null;
    },
    types() {
      return this.form.statusType
        ? this.trackingStatuses[this.form.statusType].types
        : null;
    },
    description() {
      let desc = "";
      let selectedType = this.types?.find((o) => o.type === this.form.deliveryType);
      desc += selectedType?.title ? selectedType.title : "";
      let selectedStatus = this.statuses?.find((o) => o.status === this.form.status);
      desc += selectedStatus ? ` ${selectedStatus?.title}` : "";
      return desc;
    },
  },
  watch: {
    "form.type": function () {
      this.form.description = this.description;
    },
    "form.deliveryType": function () {
      this.form.description = this.description;
    },
    "form.statusType": function () {
      this.form.description = this.description;
    },
    "form.status": function () {
      this.form.description = this.description;
    },
    "form.partner": function () {
      this.form.description = this.description;
    },
  },
};
</script>
