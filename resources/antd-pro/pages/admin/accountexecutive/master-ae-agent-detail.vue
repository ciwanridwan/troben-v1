<template>
    <div>
        <content-layout>
            <template slot="content">
                <div>
                    <h2 class="title-page">
                        <a :href="routeUri('admin.master.account.executive.agent.index')">
                            <a-icon class="mr-1 gray-color" type="arrow-left"/>
                        </a>
                        Pencairan Saldo
                    </h2>
                </div>
                <a-table
                    :columns="teamDetailColumns"
                    :class="['trawl']"
                    rowKey="id"
                    :loading="loading"
                    :data-source="datas"
                >
                    <span slot="number" slot-scope="number" class="fw-bold">
                        {{ number }}
                    </span>
                    <span slot="id" slot-scope="record" class="uppercase">
                        {{ record.package_id }}
                    </span>
                    <span slot="type" slot-scope="record">
                        {{ record.profit_type }}
                    </span>
                    <span slot="payment" slot-scope="record">
                        Rp.{{ formatPrice(record.service_price) }}
                    </span>
                    <span slot="komisi" slot-scope="record">
                        Rp.{{ formatPrice(record.income) }}
                    </span>
                </a-table>
            </template>
        </content-layout>
    </div>
</template>

<script>

import teamDetailColumns from "../../../config/table/master-ae-agent-detail";
import ContentLayout from "../../../layouts/content-layout.vue";
import axios from "axios";

export default {
    name: "master-ae-agent-detail",
    components: {
        ContentLayout,
    },
    data() {
        return {
            loading: false,
            teamDetailColumns,
            userId: window.location.pathname.split('/')[6],
            periode: window.location.pathname.split('/')[7],
            datas: []
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
            axios.get(`https://ae.trawlbens.com/agent/disbursementDetail`, {
                params: {
                    user_id: this.userId,
                    month: this.periode,
                },
                headers: {
                    Authorization: `Bearer ${this.$laravel.jwt_token}`
                }
            })
            .then((res)=>{
                this.datas = res.data.data
                this.loading = false

                let numbering = 1;
                this.datas.forEach((o, k) => {
                    o.number = numbering++;
                });
            }).catch(function (error) {
                console.error(error);
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
    .title-page{
        color: #E60013 !important;
        font-size: 25px;
        font-weight: 600;
    }
</style>
