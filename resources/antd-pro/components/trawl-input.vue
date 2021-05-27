<template>
  <div>
    <h3>
      <slot v-if="hasLabel" name="label"></slot>
      <span v-else>{{ label }}</span>
    </h3>
    <slot v-if="hasInput" name="input"></slot>
    <a-input v-else v-model="trawlValue"></a-input>
  </div>
</template>
<script>
export default {
  props: ["label", "trawlValue"],

  computed: {
    hasInput() {
      return !!this.$slots.input;
    },
    hasLabel() {
      return !!this.$slots.label;
    }
  }
};
</script>
