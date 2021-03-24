<template>
  <div>
    <slot name="title" v-if="hasTitleSlot"></slot>
    <h5 v-else>{{ receiver ? "Penerima" : "Pengirim" }}</h5>
    <slot name="people" v-if="hasSlot('people')"> </slot>
    <h5 v-else>
      <b>Faris Ferdian Akbar</b>
    </h5>
    <slot name="phone" v-if="hasSlot('phone')"> </slot>
    <h5 class="trawl-text-normal" v-else>
      085694455935
    </h5>
    <slot name="address" v-if="hasSlot('address')"> </slot>
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
    }
  },
  methods: {
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    }
  }
};
</script>
