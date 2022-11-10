<template>
    <div>
        <content-layout>
            <template slot="head-tools">
            </template>
            <template slot="content">
                <a-card>
                    <a-row :gutter="[24]">
                        <a-col :span="12">
                            <a-input-search
                                placeholder="Cari nama agen"
                                v-model="keyword"
                            ></a-input-search>
                        </a-col>
                        <a-col :span="12">
                            <a-select
                                ref="select"
                                v-model="check_status"
                                size="large"
                            >
                                <a-select-option :value="null">Pilih status</a-select-option>
                                <a-select-option value="transferred">Transferred</a-select-option>
                                <a-select-option value="waiting">Waiting</a-select-option>
                                <a-select-option value="on_going">Ongoing</a-select-option>
                            </a-select>
                        </a-col>
                    </a-row>
                </a-card>
                <a-table
                    class="mt-3 mb-table"
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
                        {{ moment(record.periode).format('MMMM') }}
                    </span>
                    <span slot="status" slot-scope="record">
                        <template v-if="record.status == 'transferred'">
                            <span class="text-green fw-medium">
                                <a-icon type="check"/>
                                Transferred
                            </span>
                        </template>
                        <template v-if="record.status == 'on_going'">
                            <input type="checkbox" disabled>
                            <span class="text-gray">On Going</span>
                        </template>
                        <template v-if="record.status == 'waiting'">
                            <input type="checkbox" v-model="req.checked" :value="record">
                            Waiting
                        </template>
                    </span>
                </a-table>
            </template>
            <template slot="footer">
                <a-layout-footer :class="['trawl-content-footer']">
                    <a-row type="flex" :gutter="24">
                        <a-col :span="14">
                        </a-col>
                        <a-col :span="4">
                            <center>
                                <a-button type="primary" @click="store()" :disabled="req.checked.length == 0" class="trawl-button-success">
                                    Simpan
                                </a-button>
                            </center>
                        </a-col>
                    </a-row>
                </a-layout-footer>
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
            keyword: ''
        };
    },
    created() {
        this.getDatas()
    },
    computed: {
        filteredItems() {
            return this.datas.filter(item => {
                if(this.check_status != null){
                    return (
                        item.status == this.check_status && this.keyword.toLowerCase().split(' ').every(v => item.name.toLowerCase().includes(v))
                    )
                }else{
                    return this.keyword.toLowerCase().split(' ').every(v => item.name.toLowerCase().includes(v))
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
            this.loading = true
            axios.get(process.env.MIX_TB_AE_URL + `/agent/disbursement`, {
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
                console.log(error)
                this.loading = false
            });
        },
        store(){
            this.req.checked.forEach(item => {
                axios.patch(process.env.MIX_TB_AE_URL + `/agent/setDisbursementStatus`, null, {
                    params: {
                        user_id: item.user_id,
                        month: item.periode
                    },
                    headers: {
                        Authorization: `Bearer ${this.$laravel.jwt_token}`
                    }
                })
                .then((res)=>{
                    this.getDatas()
                    this.$message.success(`Change status success`);
                    this.req.checked = []
                }).catch(function (error) {
                    console.log(error)
                });
            });
        },
    },
};
</script>

<style lang="scss">
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
    .ml-auto{
        margin-left: auto;
    }
    .ant-input{
        height: 38px;
    }
</style>
