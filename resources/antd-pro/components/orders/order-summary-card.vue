<template>
  <a-card>
    <a-row type="flex" :gutter="[12, 12]">
      <a-col :span="8">
        <a-row type="flex">
          <a-col :span="24">
            <a-space>
              <span>
                <package-icon />
              </span>
              <span>
                Berat :
              </span>
            </a-space>
          </a-col>
          <a-col :span="24" class="trawl-text-center">
            <span class="trawl-text-bold">
              {{ getTotalWeightBorne(record.items) }} Kg
            </span>
          </a-col>
        </a-row>
      </a-col>
      <a-col :span="8">
        <a-row type="flex">
          <a-col :span="24">
            <a-space>
              <span>
                <weight-machine-icon />
              </span>
              <span>
                Jml. koli :
              </span>
            </a-space>
          </a-col>
          <a-col :span="24" class="trawl-text-center">
            <span class="trawl-text-bold">
              {{ getNumberOfItems(record.items) }} Koli
            </span>
          </a-col>
        </a-row>
      </a-col>
      <a-col :span="8">
        <a-row type="flex">
          <a-col :span="24">
            <a-space>
              <span>
                <term-condition-icon />
              </span>
              <span>
                Jml. Resi :
              </span>
            </a-space>
          </a-col>
          <a-col :span="24" class="trawl-text-center">
            <span class="trawl-text-bold">
              1 Resi
            </span>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
  </a-card>
</template>
<script>
import { getNumberOfItems, getTotalWeightBorne } from "../../functions/orders";
import { PackageIcon, TermConditionIcon, WeightMachineIcon } from "../icons";
export default {
  props: ["record"],
  methods: {
    getNumberOfItems,
    getTotalWeightBorne
  },
  components: { PackageIcon, TermConditionIcon, WeightMachineIcon }
};
</script>
