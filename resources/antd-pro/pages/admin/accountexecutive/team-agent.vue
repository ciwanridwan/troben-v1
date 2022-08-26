<template>
    <div>
        <content-layout>
            <template slot="content">
                <div>
                  <a-row type="flex" :gutter="[24, 24]">
                    <a-col :span="17">
                      <h2 class="head-title">
                        Tim Agen TrawlBens
                      </h2>
                    </a-col>
                    <a-col :span="7">
                      <a-select
                        ref="select"
                        v-model="sort_by"
                        size="large"
                    >
                        <a-select-option :value="null">Sort by</a-select-option>
                        <a-select-option :value="true">Koordinator</a-select-option>
                        <a-select-option :value="false">Agen Trawlbens</a-select-option>
                      </a-select>
                    </a-col>
                  </a-row>
                </div>
                <a-table
                    class="mt-3"
                    :columns="teamAgentColumns"
                    :loading="loading"
                    :data-source="filteredItems"
                    :class="['trawl']"
                    rowKey="id"
                >
                    <span slot="number" slot-scope="number" class="fw-bold">{{ number }}</span>
                    <span slot="name" slot-scope="record" class="fw-bold">
                      <a :href="routeUri('admin.home.accountexecutive.teamdetail', {code: record.code})">
                        <a-space class="cursor-pointer uppercase">
                            {{ record.coordinator_name }}
                        </a-space>
                      </a>
                    </span>
                    <span slot="member" slot-scope="record">
                      {{ record.member_num }} Member
                    </span>
                    <span slot="referral" slot-scope="record">
                      {{ record.refferal_share }} Referral
                    </span>
                    <span slot="income" slot-scope="record">
                      Rp.{{ formatPrice(record.monthly_income) }}
                    </span>
                    <span slot="posisi" slot-scope="record">
                      <template v-if="record.is_coordinator">
                        Koordinator
                      </template>
                      <template v-else>
                        Agen Trawlbens
                      </template>
                    </span>
                    <span slot="action" slot-scope="record">
                      <a-button type="primary" class="trawl-button-circle disabled" v-if="record.is_coordinator">
                          <img src="/assets/status.png" width="20">
                      </a-button>
                      <a-button type="primary" class="trawl-button-circle" @click="changeStatus(record.code)" v-else>
                          <img src="/assets/status.png" width="20">
                      </a-button>
                    </span>
                </a-table>
            </template>
        </content-layout>
    </div>
</template>

<script>

import teamAgentColumns from "../../../config/table/team-agent";
import ContentLayout from "../../../layouts/content-layout.vue";
import axios from "axios";

export default {
    name: "team-agent",
    components: {
        ContentLayout,
    },
    data() {
        return {
            loading: false,
            teamAgentColumns,
            lists: {
                data: [],
            },
            sort_by: null,
            is_coordinator: false
        };
    },
    created() {
        this.getTeamList()
    },
    computed: {
        filteredItems() {
            return this.lists.data.filter(item => {
                if(this.sort_by != null){
                    return item.is_coordinator == this.sort_by
                }else{
                    return true
                }
            })
        }
    },
    methods: {
        formatPrice(value) {
          let val = (value/1).toFixed(2).replace('.', ',')
          return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },
        getTeamList(){
            this.loading = true
            axios.get(`https://ae.trawlbens.com/agent/teamList`, {
                headers: {
                    Authorization: `Bearer ${this.$laravel.jwt_token}`
                }
            })
            .then((res)=>{
                this.lists = res.data
                let numbering = 1;
                this.lists.data.forEach((o, k) => {
                    o.number = numbering++;
                });
                this.loading = false
            }).catch(function (error) {
                console.error(error);
                this.loading = false
            });
        },
        changeStatus(code){
            axios.patch(`https://ae.trawlbens.com/agent/setCoordinator`, null, {
                params: {
                    code: code
                },
                headers: {
                    Authorization: `Bearer ${this.$laravel.jwt_token}`
                }
            })
            .then((res)=>{
              console.log(res)
                this.getTeamList()
                this.$message.success(`Change status success`);
            }).catch(function (error) {
                console.log(error)
            });
        },
    },
};
</script>

<style lang="scss" scoped>
  .trawl-button-circle{
        background-color: #E60013;
        border: 2px solid #E60013;
        color: #E60013;
        border-radius: 50px !important;
        &:hover{
            background-color: #E60013;
            border: 2px solid #E60013;
            color: #E60013;
            border-radius: 50px !important;
        }
    }
    .ant-btn-primary{
      &.disabled{
        background-color: #D2D2D5;
        border: 2px solid #D2D2D5;
        border-radius: 50px !important;
      }
    }
    .mt-3{
      margin-top: 30px;
    }
    .head-title{
      color: #E60013 !important;
      font-size: 25px;
      font-weight: 600;
    }
</style>