<template>
  <content-layout-footer>
    <template slot="content">
      <a-tabs v-model="current" :tabBarStyle="{ display: 'none' }">
        <a-tab-pane :key="1">
          <a-card :style="{ 'margin-bottom': '3rem' }">
            <order-receiver-form-step-1 ref="stepForm1" />
          </a-card>
        </a-tab-pane>
        <a-tab-pane :key="2">
          <a-card :style="{ 'margin-bottom': '3rem' }">
            <order-receiver-form-step-2 ref="stepForm2" />
          </a-card>
        </a-tab-pane>
      </a-tabs>
    </template>

    <template slot="footer">
      <trawl-step-circle
        ref="trawlStep"
        :number="2"
        :beforeChange="validateStep"
        :onChange="changeStep"
      />
    </template>
  </content-layout-footer>
</template>
<script>
import OrderReceiverFormStep1 from "../../../../components/orders/walkin/order-receiver-forms/form-step-1.vue";
import OrderReceiverFormStep2 from "../../../../components/orders/walkin/order-receiver-forms/form-step-2.vue";
import TrawlStepCircle from "../../../../components/trawl-step-circle.vue";
import ContentLayoutFooter from "../../../../layouts/content-layout-footer.vue";
export default {
  name: "customer-service-walkin",
  components: {
    OrderReceiverFormStep1,
    OrderReceiverFormStep2,
    ContentLayoutFooter,
    TrawlStepCircle,
  },
  data() {
    return {
      current: 1,
    };
  },
  computed: {
    stepForms() {
      return ["stepForm1", "stepForm2"];
    },
  },
  methods: {
    validateStep(toStep) {
      let currentForm = this.$refs[this.stepForms[this.current - 1]];
      let insideForm = currentForm?.formRefs;
      insideForm ? this.validateInsideForms(currentForm) : null;
      currentForm?.$refs?.formRules?.validate().then(() => {
        this.$refs.trawlStep.toStep(toStep);
      });
    },
    validateInsideForms(currentForm) {
      return currentForm.validate();
    },
    changeStep(value) {
      this.current = value;
    },
  },
  mounted() {
    console.log(this.$refs);
  },
};
</script>
