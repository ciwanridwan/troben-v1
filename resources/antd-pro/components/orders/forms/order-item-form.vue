<template>
  <a-space direction="vertical" :style="{ width: '100%' }" ref="items">
    <div>
      <h3 class="trawl-text-bolder">Daftar Barang</h3>
      <order-item-component :ref="`itemComponent`" v-model="form.item" />
    </div>

    <div v-if="form.item.desc != 'bike'">
      <h3 class="trawl-text-bolder">Rekomendasi Packing</h3>
      <order-handlings-component
        :ref="`handlingComponent`"
        v-model="form.handling"
      />
    </div>
    <div>
      <h3 class="trawl-text-bolder">Asuransi</h3>
      <order-insurance-component
        :is-motor="form.item.desc"
        :ref="`insuranceComponent`"
        v-model="form.insurance"
      />
    </div>
    <div v-if="form.item.desc == 'bike'">
      <h3 class="trawl-text-bolder">Perlindungan Tambahan</h3>
      <order-protection-component
        :ref="`protectionComponent`"
        v-model="form.protection"
      />
    </div>
  </a-space>
</template>
<script>
import { MinusCircleIcon } from "../../icons";
import TrawlDivider from "../../trawl-divider.vue";
import OrderHandlingsComponent from "./order-handlings-component.vue";
import OrderInsuranceComponent from "./order-insurance-component.vue";
import OrderProtectionComponent from "./order-protection-component.vue";
import orderItemComponent from "./order-item-component.vue";
export default {
  components: {
    orderItemComponent,
    OrderHandlingsComponent,
    OrderInsuranceComponent,
    OrderProtectionComponent,
    TrawlDivider,
  },
  props: {
    visibleDestroy: {
      type: Boolean,
      default: false,
    },
    defaultValue: {
      type: Object,
      default: () => {},
    },
    value: {
      type: Object,
      default: () => {
        return {
          item: {
            name: null,
            desc: null,
            length: null,
            width: null,
            height: null,
            weight: null,
            qty: null,
            price: null,
            moto_cc: null,
            moto_type: null,
            moto_merk: null,
            moto_year: null,
            order_type: null
          },
          handling: {
            handling: false,
            handling_type: null,
          },
          insurance: {
            insurance: false,
          },
          protection: {
            protection: false,
          },
        };
      },
    },
  },
  data() {
    return {
      formRefs: [
        "itemComponent",
        "handlingComponent",
        "insuranceComponent",
        "protectionComponent",
      ],
      MinusCircleIcon,
      valid: true,
      form: {
        item: {
          name: null,
          desc: null,
          length: null,
          width: null,
          height: null,
          weight: null,
          qty: null,
          price: null,
          moto_cc: null,
          moto_type: null,
          moto_merk: null,
          moto_year: null,
          order_type:null
        },
        handling: {
          handling: false,
          handling_type: null,
        },
        insurance: {
          insurance: false,
        },
        protection: {
          protection: false,
        },
      },
    };
  },
  computed: {
    getFormData() {
      let form = {};
      Object.keys(this.form).forEach(
        (k) => (form = { ...form, ...this.form[k] })
      );
      return form;
    },
  },
  methods: {
    async validate() {
      let valid = true;
      for (const ref of this.formRefs) {
        let currentForm = this.$refs[ref];
        await currentForm?.$refs?.formRules
          ?.validate()
          ?.then((value) => {
            if (!value) {
              valid = false;
            }
          })
          ?.catch(() => (valid = false));
      }

      return valid;
    },
    onItemChange(itemForm) {
      // console.log(itemForm, this.form);
    },
    onPackagingChange(handlingForm) {
      // this.value.handling = handlingvalue.handling;
      // this.value.handling_type = handlingvalue.handling_type;
    },
  },
  watch: {
    value: {
      handler: function () {
        this.form = this.value;
      },
      deep: true,
    },
    form: {
      handler: function (value) {
        this.$emit("input", this.form);
        this.$emit("change", this.form);
      },
      deep: true,
    },
  },
};
</script>
