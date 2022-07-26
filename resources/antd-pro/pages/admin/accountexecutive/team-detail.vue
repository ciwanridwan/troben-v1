<template>
    <div>
        <content-layout>
            <template slot="content">
                <div>
                    <h2 class="text-danger">
                        <a :href="routeUri('admin.home.accountexecutive.teamagent')">
                            <a-icon class="mr-1 gray-color" type="arrow-left"/>
                        </a>
                        Tim Agen TrawlBens
                    </h2>
                    <h3 class="fw-medium mt-1 mb-0 capitalize">
                        {{ list_leader.name }}
                    </h3>
                    <h3 class="fw-light gray-color capitalize">
                        {{ list_leader.role }}
                    </h3>
                </div>
                <a-table
                    :loading="loading"
                    :columns="teamDetailColumns"
                    :data-source="lists"
                    :class="['trawl']"
                    rowKey="id"
                >
                    <span slot="number" slot-scope="number" class="fw-bold">
                        {{ number }}
                    </span>
                    <span slot="name" slot-scope="record" class="fw-bold uppercase">
                        {{ record.name }}
                    </span>
                    <span slot="voucher" slot-scope="record">
                        {{ record.voucher_share }} Voucher
                    </span>
                    <span slot="income" slot-scope="record">
                        Rp.{{ formatPrice(record.monthly_income) }}
                    </span>
                </a-table>
            </template>
        </content-layout>
    </div>
</template>

<script>

import teamDetailColumns from "../../../config/table/team-detail";
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
            teamDetailColumns,
            lists: {},
            list_leader: {},
            check_status: null,
            is_coordinator: false,
            code_item: window.location.href.split('/').pop(),
        };
    },
    created() {
        this.getDatas()
    },
    computed: {
    },
    methods: {
        formatPrice(value) {
            let val = (value/1).toFixed(2).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },
        getDatas(){
            this.loading = true
            axios.get(`https://ae.trawlbens.co.id/agent/teamDetail`, {
                params: {
                    code:this.code_item
                },
                headers: {
                    Authorization: `Bearer ${this.$laravel.jwt_token}`
                }
            })
            .then((res)=>{
                this.loading = false
                this.lists = res.data.data.leader.members
                this.list_leader = res.data.data.leader
                let numbering = 1;
                this.lists.forEach((o, k) => {
                    o.number = numbering++;
                });
            }).catch(function (error) {
                console.error(error);
                this.loading = false
            });
        },
    },
};
</script>

<style lang="scss" scoped>
    .mb-0{
        margin-bottom: 0 !important;
    }
    .gray-color{
        color: #8E8E94 !important;
    }
    .fw-medium{
        font-weight: 600;
    }
    .fw-light{
        font-weight: 400;
    }
    .mr-1{
        margin-right: 10px;
    }
    .capitalize{
        text-transform: capitalize;
    }
    .text-danger{
      color: #E60013 !important;
    }
</style>