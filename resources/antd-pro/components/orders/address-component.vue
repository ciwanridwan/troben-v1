<template>
  <div>
    <slot name="title" v-if="hasTitleSlot"></slot>
    <h5 v-else>{{ receiver ? "Penerima" : "Pengirim" }}</h5>
    <h5 v-if="hasSlot('name')">
      <b><slot name="name"></slot></b>
    </h5>
    <h5 v-else>
      <b>Faris Ferdian Akbar</b>
    </h5>
    <h5 v-if="hasSlot('phone')">
      <slot name="phone"></slot>
    </h5>
    <h5 class="trawl-text-normal" v-else>085694455935</h5>
    <p v-if="hasSlot('address')">
      <slot name="address"> </slot>
    </p>
    <p v-else>
      Jl. Soekarno No.1, Kel. Indarung, Kec. Lubuk Kilangan, Padang, Sumatera
      Barat.
    </p>
  </div>
</template>
<script>
export default {
  props: ["record", "receiver", "title"],
  computed: {
    hasTitleSlot() {
      return !!this.$slots.title;
    },
  },
  methods: {
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    },
  },
};
</script>
