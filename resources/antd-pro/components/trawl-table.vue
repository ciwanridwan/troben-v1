<template>
  <div>
    <a-table ref="antTable">
      <slot v-for="(_, name) in $slots" :name="name" :slot="name" />
    </a-table>
  </div>
</template>

<script>
import { Table } from "ant-design-vue";

export default {
  props: {
    ...Table.props,
  },
  methods: {
    assignProp(ref) {
      _.forEach(this.$props, (v, k) => {
        this.$refs[ref].$props[k] = v;
      });
    },
  },
  computed: {
    customProps() {
      return { ...this.$props };
    },
  },
  mounted() {
    this.assignProp();
    _.forEach(this.$props, (v, k) => {
      this.$watch(
        () => {
          return this.$props[k];
        },
        (val) => {
          this.$refs.antTable.$props[k] = val;
        }
      );
    });
  },
};
</script>

<style lang="scss"></style>
