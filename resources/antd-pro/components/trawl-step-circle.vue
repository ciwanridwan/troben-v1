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
    clickItem(value) {
      if (!this.beforeChange(value)) {
        return false;
      }
      this.current = value;
      this.onChange(this.current);
    },
    toStep(value) {
      this.current = value;
      this.onChange(this.current);
    },
  },
};
</script>
