<template>
    <div>
        <content-layout>
            <template slot="head-tools">
                <a-row type="flex" justify="end" :gutter="3" style="margin-top: 7px;">
                    <a-select
                        ref="select"
                        v-model="check_status"
                        style="width: 250px"
                        size="large"
                    >
                        <a-select-option :value="null">Pilih status</a-select-option>
                        <a-select-option value="transferred">Transferred</a-select-option>
                        <a-select-option value="waiting">Waiting</a-select-option>
                        <a-select-option value="ongoing">Ongoing</a-select-option>
                    </a-select>
                </a-row>
            </template>
            <template slot="content">
                <a-table
                    class="mt-3"
                    :columns="disbursement"
                    :loading="loading"
                    :data-source="filteredItems"
                    :class="['trawl']"
                    rowKey="id"
                >
                    <span slot="number" slot-scope="number" class="fw-bold">{{ number }}</span>
                    <span slot="name" slot-scope="record" class="fw-bold">
                      <a class="text-black" :href="routeUri('admin.master.account.executive.agent.detail', {user_id: record.user_id, period: record.periode})">
                        <a-space class="cursor-pointer uppercase">
                            {{ record.name }}
                        </a-space>
                      </a>
                    </span>
                    <span slot="saldo" slot-scope="record">
                        Rp. {{ formatPrice(record.monthly_income) }}
                    </span>
                    <span slot="periode" slot-scope="record">
                        {{ record.periode }}
                    </span>
                    <span slot="status" slot-scope="record">
                        <template v-if="record.status == 'transferred'">
                            <span class="text-green fw-medium">
                                <a-icon type="check"/>
                                Transferred
                            </span>
                        </template>
                        <template v-if="record.status == 'ongoing'">
                            <input type="checkbox" disabled>
                            <span class="text-gray">On Going</span>
                        </template>
                        <template v-if="record.status == 'waiting'">
                            <input type="checkbox" v-model="req.checked" :value="record">
                            Waiting
                        </template>
                    </span>
                </a-table>
                <div>
                    <a-button type="primary" @click="store()" :disabled="req.checked.length == 0" class="trawl-button-success">
                        Simpan
                    </a-button>
                </div>
            </template>
        </content-layout>
    </div>
</template>

<script>

import ContentLayout from "../../../layouts/content-layout.vue";
import disbursement from "../../../config/table/disbursement";
import axios from "axios";

export default {
    name: "master-ae-agent",
    components: {
        ContentLayout,
    },

    data() {
        return {
            check_status: null,
            disbursement,
            datas: [],
            loading: false,
            cehckbox_data : [],
            req: {
                checked: []
            },
        };
    },
    created() {
        this.getDatas()
    },
    computed: {
        filteredItems() {
            return this.datas.filter(item => {
                if(this.check_status != null){
                    return item.status == this.check_status
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
        getDatas(){
            console.log(this.req)
            this.loading = true
            axios.get(`https://ae.trawlbens.co.id/agent/disbursement`)
            .then((res)=>{
                this.datas = res.data.data
                this.loading = false

                let numbering = 1;
                this.datas.forEach((o, k) => {
                    o.number = numbering++;
                });
            }).catch(function (error) {
                console.log(error)
                this.loading = false
            });
        },
        store(){
            var items = []
            this.req.checked.forEach(item => {
                // var object = {
                //     user_id: item.user_id,
                //     month: item.periode,
                // }
                // items.push(object)

                axios.patch(`https://ae.trawlbens.co.id/agent/setDisbursementStatus`, null, {
                    params: {
                        user_id: item.user_id,
                        month: item.periode
                    }
                })
                .then((res)=>{
                    this.getDatas()
                    this.$message.success(`Change status success`);
                }).catch(function (error) {
                    console.log(error)
                });
            });
        },
    },
};
</script>

<style lang="scss" scoped>
    .text-green{
        color: #3D8824;
    }
    .fw-medium{
        font-weight: 600;
    }
    .trawl-button-success{
        padding: 0 40px;
        height: 40px;
        border-radius: 50px !important;
    }
    .text-gray{
        color: #bfbfbf;
    }
    .text-black{
        color: #000;
    }
</style>
