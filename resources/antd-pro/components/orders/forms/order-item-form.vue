<template>
  <a-space direction="vertical" :style="{ width: '100%' }" ref="items">
    <trawl-divider v-if="displayDestroy" orientation="right">
      <a-icon :component="MinusCircleIcon" :style="{ cursor: 'pointer' }" />
    </trawl-divider>
    <div>
      <h3 class="trawl-text-bolder">Daftar Barang</h3>
      <order-item-component :ref="`itemComponent`" :onChange="onItemChange" />
    </div>

    <div>
      <h3 class="trawl-text-bolder">Rekomendasi Packing</h3>
      <order-handlings-component
        :ref="`handlingComponent`"
        :onChange="onPackagingChange"
      />
    </div>
    <div>
      <h3 class="trawl-text-bolder">Asuransi</h3>
      <order-insurance-component :ref="`insuranceComponent`" />
    </div>
  </a-space>
</template>
<script>
import { MinusCircleIcon } from "../../icons";
export default {
  props: {
    displayDestroy: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      formRefs: ["itemComponent", "handlingComponent", "insuranceComponent"],
      MinusCircleIcon,
      form: {},
    };
  },
  computed: {
    getFormData() {
      let form = {};
      this.formRefs.forEach((ref) => {
        let currentForm = this.$refs[ref];
        form = { ...form, ...currentForm?.form };
      });
      return form;
    },
    valid() {
      let valid = true;
      this.formRefs.forEach((ref) => {
        let currentForm = this.$refs[ref];
        currentForm?.$refs?.formRules?.validate()?.catch(() => (valid = false));
      });
      return valid;
    },
  },
  methods: {
    destroyComponent() {
      this.$destroy();
    },

    onItemChange(itemForm) {
      // console.log(itemForm, this.form);
    },
    onPackagingChange(packagingForm) {
      // this.form.packaging = packagingForm.packaging;
      // this.form.packaging_type = packagingForm.packaging_type;
    },
  },
};
</script>
