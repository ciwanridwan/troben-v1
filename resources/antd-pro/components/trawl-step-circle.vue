<template>
  <div class="trawl-step-circle--wrapper">
    <a-space size="large">
      <span
        v-for="index in number"
        :key="index"
        @click="clickItem(index)"
        :class="[
          'trawl-step-circle--item',
          current == index ? 'trawl-step-circle--item-selected' : null,
        ]"
      >
      </span>
    </a-space>
  </div>
</template>
<script>
export default {
  data() {
    return {
      current: 1,
    };
  },
  props: {
    value: {
      type: Number,
      default: 1,
    },
    number: {
      type: Number,
      default: 1,
    },
    beforeChange: {
      type: Function,
      default: () => {
        return true;
      },
    },
    onChange: {
      type: Function,
      default: () => {},
    },
  },
  methods: {
    async clickItem(value) {
      let valid = await this.beforeChange(value);
      if (!valid) {
        return false;
      }

      this.current = value;
      this.onChange(this.current);
    },
    async next() {
      let nextStep = this.current + 1;
      this.toStep(nextStep);
    },
    async prev() {
      let prevStep = this.current - 1;
      this.toStep(prevStep);
    },
    async toStep(value) {
      let valid = await this.beforeChange(value);
      if (!valid) {
        return false;
      }
      this.current = value;
      this.onChange(this.current);
    },
  },
  watch: {
    value: {
      handler: function (value) {
        this.current = value;
      },
    },
    current: {
      handler: function (value) {
        this.$emit("input", value);
        this.$emit("change", value);
      },
    },
  },
};
</script>
